<?php
/**
 * Re-queue invoices for AutoCount sync by resetting their autocount status
 * to 'Pending' (getPendingInvoices picks up anything != 'Synced').
 *
 * DRY-RUN by default.
 *
 * Usage:
 *   php reset_autocount_sync.php 58908 58910 58923 ...            (preview)
 *   php reset_autocount_sync.php 58908 58910 58923 ... --apply    (write)
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;

$args  = array_slice($argv, 1);
$apply = in_array('--apply', $args);
$ids   = array_values(array_filter($args, fn($a) => ctype_digit($a)));

if (empty($ids)) {
    echo "No invoice IDs given.\nUsage: php reset_autocount_sync.php <id> <id> ... [--apply]\n";
    exit(1);
}

$invoices = Invoice::whereIn('id', $ids)->orderBy('id')->get();

echo ($apply ? "APPLYING" : "DRY-RUN (pass --apply to write)") . "\n";
printf("%-8s %-18s %-10s %-40s -> %s\n", 'ID', 'Invoice No', 'Status', 'Current autocount', 'New');
echo str_repeat('-', 100) . "\n";

$found = [];
foreach ($invoices as $invoice) {
    $found[] = (string) $invoice->id;
    printf("%-8s %-18s %-10s %-40s -> Pending\n",
        $invoice->id, $invoice->invoiceno, $invoice->status,
        $invoice->autocount === null ? '(null)' : mb_substr($invoice->autocount, 0, 40));
    if ($apply) {
        $invoice->autocount = 'Pending';
        $invoice->save();
    }
}

foreach (array_diff($ids, $found) as $missing) {
    echo "WARNING: invoice id {$missing} not found\n";
}

echo str_repeat('-', 100) . "\n";
printf("%d invoice(s) %s.\n", count($found), $apply ? 'reset to Pending' : 'would be reset to Pending');
if (!$apply) echo "Nothing was changed.\n";
