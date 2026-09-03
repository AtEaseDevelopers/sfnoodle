<?php
/**
 * Audit invoices created on a given date against the CORRECT pricing logic
 * (special price: customer-specific -> price category -> default product price,
 *  plus tiered pricing for totals). READ-ONLY - changes nothing.
 *
 * Usage:  php check_invoice_prices.php [YYYY-MM-DD]   (default 2026-09-03)
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SpecialPrice;

$targetDate = $argv[1] ?? '2026-09-03';

// Mirrors DriverController::calculateProductPriceForInvoice (the restored/correct logic)
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

    // tiered pricing affects the line total
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

    return [
        'unit_price' => round($basePrice, 2),
        'total'      => round($totalPrice, 2),
        'source'     => $specialPrice ? 'special_price' : 'default_price',
    ];
}

$invoices = Invoice::whereDate('created_at', $targetDate)
    ->with(['customer', 'invoiceDetails.product'])
    ->orderBy('id')
    ->get();

echo "Checking " . $invoices->count() . " invoice(s) created on {$targetDate}\n";
echo str_repeat('=', 130) . "\n";
printf("%-10s %-15s %-30s %-12s %5s | %8s %8s | %10s %10s | %s\n",
    'ID', 'Invoice No', 'Customer', 'SKU', 'Qty', 'DB Price', 'Correct', 'DB Total', 'Correct', 'Source');
echo str_repeat('-', 130) . "\n";

$badInvoices = 0; $badLines = 0; $dbSum = 0; $correctSum = 0;

foreach ($invoices as $invoice) {
    $invoiceHasIssue = false;

    foreach ($invoice->invoiceDetails as $detail) {
        if ($detail->price == 0 && $detail->totalprice == 0) continue; // FOC rows
        $product = $detail->product;
        if (!$product) continue;

        $exp = expectedPrice($product, $detail->quantity, $invoice->customer_id);
        $discount = (float) ($detail->discount_amount ?? 0);
        $expTotal = round(max(0, $exp['total'] - $discount * $detail->quantity), 2);

        $priceWrong = abs($detail->price - $exp['unit_price']) > 0.001;
        $totalWrong = abs($detail->totalprice - $expTotal) > 0.001;

        if ($priceWrong || $totalWrong) {
            $invoiceHasIssue = true;
            $badLines++;
            $dbSum      += $detail->totalprice;
            $correctSum += $expTotal;
            printf("%-10s %-15s %-30s %-12s %5s | %8.2f %8.2f | %10.2f %10.2f | %s\n",
                $invoice->id,
                $invoice->invoiceno,
                mb_substr($invoice->customer->company ?? '?', 0, 30),
                $product->code,
                $detail->quantity,
                $detail->price, $exp['unit_price'],
                $detail->totalprice, $expTotal,
                $exp['source']);
        }
    }
    if ($invoiceHasIssue) $badInvoices++;
}

echo str_repeat('=', 130) . "\n";
if ($badLines === 0) {
    echo "All invoice line prices match the correct pricing logic. Nothing to fix.\n";
} else {
    printf("%d invoice(s) with %d wrong line(s).\n", $badInvoices, $badLines);
    printf("Stored total of wrong lines: RM %.2f | Correct total: RM %.2f | Difference: RM %.2f\n",
        $dbSum, $correctSum, $correctSum - $dbSum);
    echo "NOTE: read-only report - no data was changed.\n";
}
