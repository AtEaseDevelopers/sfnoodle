<?php
/**
 * Correct invoice line prices stored during the bad special-price-logic window.
 * Uses the SAME logic as check_invoice_prices.php (customer special price ->
 * price category -> default, tiers/discounts for totals) and rewrites only
 * the mismatched lines, adjusting each invoice's total by the same delta.
 *
 * DRY-RUN by default - shows what would change, writes nothing.
 *
 * Usage:
 *   php fix_invoice_prices.php [YYYY-MM-DD]            (preview, default 2026-09-03)
 *   php fix_invoice_prices.php [YYYY-MM-DD] --apply    (actually update the DB)
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SpecialPrice;
use Illuminate\Support\Facades\DB;

$args       = array_slice($argv, 1);
$apply      = in_array('--apply', $args);
$dateArgs   = array_values(array_filter($args, fn($a) => $a !== '--apply'));
$targetDate = $dateArgs[0] ?? '2026-09-03';

// Same logic as check_invoice_prices.php / DriverController::calculateProductPriceForInvoice
function expectedPrice($product, $quantity, $customerId)
{
    $customer = Customer::find($customerId);
    $specialPrices = SpecialPrice::where('product_id', $product->id)
        ->where('status', 1)
        ->get();

    $specialPrice = null;
    foreach ($specialPrices as $sp) {
        if ($sp->customer_id == $customerId) { $specialPrice = $sp; break; }
    }
    if (!$specialPrice && $customer && $customer->price_category) {
        foreach ($specialPrices as $sp) {
            if ($sp->price_category && $sp->price_category == $customer->price_category) { $specialPrice = $sp; break; }
        }
    }
    $basePrice = $specialPrice ? $specialPrice->price : $product->price;

    $totalPrice = 0;
    $remaining = $quantity;
    $tiers = $product->tiered_pricing;
    if (!empty($tiers) && is_array($tiers)) {
        usort($tiers, fn($a, $b) => $a['quantity'] - $b['quantity']);
        foreach ($tiers as $tier) {
            if ($remaining <= 0) break;
            $packages = floor($remaining / $tier['quantity']);
            if ($packages > 0) {
                $totalPrice += $packages * $tier['price'];
                $remaining  -= $packages * $tier['quantity'];
            }
        }
    }
    if ($remaining > 0) $totalPrice += $remaining * $basePrice;

    return ['unit_price' => round($basePrice, 2), 'total' => round($totalPrice, 2)];
}

$invoices = Invoice::whereDate('created_at', $targetDate)
    ->with(['customer', 'invoiceDetails.product'])
    ->orderBy('id')
    ->get();

echo ($apply ? "APPLYING fixes" : "DRY-RUN (pass --apply to write)") . " for invoices created on {$targetDate}\n";
echo str_repeat('=', 120) . "\n";

$fixedInvoices = 0; $fixedLines = 0; $grandDelta = 0;

DB::beginTransaction();
try {
    foreach ($invoices as $invoice) {
        $invoiceDelta = 0;
        $changed = [];

        foreach ($invoice->invoiceDetails as $detail) {
            if ($detail->price == 0 && $detail->totalprice == 0) continue; // FOC rows
            $product = $detail->product;
            if (!$product) continue;

            $exp = expectedPrice($product, $detail->quantity, $invoice->customer_id);
            $discount = (float) ($detail->discount_amount ?? 0);
            $expTotal = round(max(0, $exp['total'] - $discount * $detail->quantity), 2);

            $priceWrong = abs($detail->price - $exp['unit_price']) > 0.001;
            $totalWrong = abs($detail->totalprice - $expTotal) > 0.001;
            if (!$priceWrong && !$totalWrong) continue;

            $invoiceDelta += $expTotal - $detail->totalprice;
            $changed[] = sprintf("  line %-8s %-12s qty %-4s price %.2f -> %.2f  total %.2f -> %.2f",
                $detail->id, $product->code, $detail->quantity,
                $detail->price, $exp['unit_price'], $detail->totalprice, $expTotal);

            $detail->price = $exp['unit_price'];
            $detail->totalprice = $expTotal;
            if ($apply) $detail->save();
            $fixedLines++;
        }

        if ($changed) {
            $fixedInvoices++;
            $grandDelta += $invoiceDelta;
            $oldTotal = $invoice->total;
            $newTotal = round($oldTotal + $invoiceDelta, 2);
            printf("%s  %s (%s)  invoice total RM %.2f -> RM %.2f (%+.2f)\n",
                $invoice->id, $invoice->invoiceno, $invoice->customer->company ?? '?', $oldTotal, $newTotal, $invoiceDelta);
            echo implode("\n", $changed) . "\n";

            if ($apply) {
                $invoice->total = $newTotal;
                $invoice->save();
            }
        }
    }

    if ($apply) { DB::commit(); } else { DB::rollBack(); }
} catch (Exception $e) {
    DB::rollBack();
    echo "ERROR - rolled back, nothing changed: " . $e->getMessage() . "\n";
    exit(1);
}

echo str_repeat('=', 120) . "\n";
printf("%d invoice(s), %d line(s) %s. Net total adjustment: RM %+.2f\n",
    $fixedInvoices, $fixedLines, $apply ? 'UPDATED' : 'would be updated', $grandDelta);
if (!$apply) echo "Nothing was changed. Re-run with --apply to write these fixes.\n";
