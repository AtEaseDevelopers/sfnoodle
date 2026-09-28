<?php
/**
 * Find inventory (stock) requests with NULL trip_id that were created while
 * the driver was on an active trip - these are missing from that trip's
 * summary report StockIn (they show up as Variance instead).
 *
 * DRY-RUN by default; --apply backfills trip_id on the affected rows.
 *
 * Usage:
 *   php check_request_trip_id.php            (report only)
 *   php check_request_trip_id.php --apply    (backfill trip_id)
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Driver;
use App\Models\InventoryRequest;
use App\Models\Trip;

$apply = in_array('--apply', array_slice($argv, 1));

$requests = InventoryRequest::whereNull('trip_id')->orderBy('id')->get();

echo ($apply ? "APPLYING backfill" : "DRY-RUN (pass --apply to backfill)")
    . " - checking " . $requests->count() . " request(s) with no trip_id\n";
echo str_repeat('=', 120) . "\n";
printf("%-8s %-28s %-10s %-20s %-20s %s\n", 'Req ID', 'Driver', 'Status', 'Requested At', 'Trip Started', 'Trip UUID');
echo str_repeat('-', 120) . "\n";

$affected = 0;

foreach ($requests as $req) {
    // Latest trip start at or before the request time for this driver
    $start = Trip::where('driver_id', $req->driver_id)
        ->where('type', Trip::START_TRIP)
        ->where('date', '<=', $req->created_at)
        ->orderByDesc('date')
        ->first();
    if (!$start) continue;

    // Trip is ongoing (no end row yet) or ended after the request was made
    $end = Trip::where('uuid', $start->uuid)->where('type', Trip::END_TRIP)->first();
    $inTrip = $end ? ($end->date >= $req->created_at) : true;
    if (!$inTrip) continue;

    $affected++;
    $driver = Driver::find($req->driver_id);
    printf("%-8s %-28s %-10s %-20s %-20s %s\n",
        $req->id,
        mb_substr($driver->name ?? ('driver#' . $req->driver_id), 0, 28),
        $req->status,
        $req->created_at,
        $start->date,
        $start->uuid);

    if ($apply) {
        $req->trip_id = $start->uuid;
        $req->save();
    }
}

echo str_repeat('=', 120) . "\n";
printf("%d affected request(s) %s.\n", $affected, $apply ? 'backfilled' : 'found');
if (!$apply && $affected > 0) echo "Nothing was changed. Re-run with --apply, then re-print the affected trips' summary reports.\n";
if ($affected === 0) echo "No other stock requests are affected by this issue.\n";
