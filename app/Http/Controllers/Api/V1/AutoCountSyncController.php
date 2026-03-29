<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\SpecialPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutoCountSyncController extends Controller
{
    /**
     * Sync customers (debtors) from AutoCount into the system.
     *
     * Expected payload shape from plugin:
     * {
     *   "customers": [
     *     {
     *       "AccNo": "3000/002",
     *       "CompanyName": "R02-CASH SALES",
     *       "phone": "0123456789",
     *       "Address": "FULL ADDRESS STRING"
     *     },
     *     ...
     *   ]
     * }
     */
    public function syncCustomers(Request $request): JsonResponse
    {
        $customers = $request->input('customers', []);

        if (!is_array($customers) || empty($customers)) {
            return response()->json([
                'status'  => false,
                'message' => 'No customers provided.',
            ], 400);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($customers as $row) {
                $code    = isset($row['AccNo']) ? trim((string) $row['AccNo']) : '';
                $company = isset($row['CompanyName']) ? trim((string) $row['CompanyName']) : '';
                $phone   = isset($row['phone']) ? trim((string) $row['phone']) : null;
                $address = isset($row['Address']) ? trim((string) $row['Address']) : null;

                // Basic validation – must have code and company
                if ($code === '' || $company === '') {
                    $skipped++;
                    continue;
                }

                // Truncate phone to DB limit (20 chars)
                if (!empty($phone)) {
                    $phone = mb_substr($phone, 0, 20);
                }

                $existing = Customer::where('code', $code)->first();

                $data = [
                    'company'     => $company,
                    'paymentterm' => 'Cash', // default for now
                    'phone'       => $phone,
                    'address'     => $address,
                    'status'      => 1,      // active
                    // tin / sst left null unless provided later
                ];

                if ($existing) {
                    $existing->fill($data);
                    $existing->save();
                    $updated++;
                } else {
                    Customer::create(array_merge($data, ['code' => $code]));
                    $created++;
                }
            }

            DB::commit();

            return response()->json([
                'status'       => true,
                'message'      => 'Customers synced successfully.',
                'created'      => $created,
                'updated'      => $updated,
                'skipped'      => $skipped,
                'total_input'  => count($customers),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Error syncing customers.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync products from AutoCount into the system.
     *
     * Expected payload shape from plugin:
     * {
     *   "products": [
     *     {
     *       "ItemCode": "KTP900ME",
     *       "Description": "KUEH TEOW GORENG 900GM MEEDO",
     *       "ItemType": "...",
     *       "uoms": [
     *         { "name": "CTN", "price": "3.00" },
     *         ...
     *       ]
     *     },
     *     ...
     *   ]
     * }
     *
     * For now we:
     * - Use the first UOM price (if any) as the main product price.
     * - Set category_id = 1 by default (as requested).
     */
    public function syncProducts(Request $request): JsonResponse
    {
        $products = $request->input('products', []);

        if (!is_array($products) || empty($products)) {
            return response()->json([
                'status'  => false,
                'message' => 'No products provided.',
            ], 400);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($products as $row) {
                $code = isset($row['ItemCode']) ? trim((string) $row['ItemCode']) : '';
                $name = isset($row['Description']) ? trim((string) $row['Description']) : '';
                $uom  = isset($row['SalesUOM']) ? trim((string) $row['SalesUOM']) : null;

                if ($code === '' || $name === '') {
                    $skipped++;
                    continue;
                }

                $price = 0.00;

                if (isset($row['uoms']) && is_array($row['uoms']) && count($row['uoms']) > 0) {
                    // Use first UOM price
                    $firstUom = $row['uoms'][0];
                    if (isset($firstUom['price'])) {
                        $price = (float) $firstUom['price'];
                    }
                }

                // Fallback if no UOM price provided
                if ($price < 0) {
                    $price = 0.00;
                }

                $existing = Product::where('code', $code)->first();

                $data = [
                    'name'        => $name,
                    'price'       => $price,
                    'uom'         => $uom,
                    'status'      => 1,   // active
                    'category_id' => 1,   // default category (as per your note)
                ];

                if ($existing) {
                    $existing->fill($data);
                    $existing->save();
                    $updated++;
                } else {
                    Product::create(array_merge($data, ['code' => $code]));
                    $created++;
                }
            }

            DB::commit();

            return response()->json([
                'status'       => true,
                'message'      => 'Products synced successfully.',
                'created'      => $created,
                'updated'      => $updated,
                'skipped'      => $skipped,
                'total_input'  => count($products),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Error syncing products.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending invoices (trip ended, not synced). Returns raw data only; plugin builds IV/IVDTL.
     */
    public function getPendingInvoices(Request $request): JsonResponse
    {
        $invoices = Invoice::query()
            ->where('status', Invoice::STATUS_COMPLETED)
            ->whereNotNull('trip_id')
            ->where(function ($q) {
                $q->whereNull('autocount')->orWhere('autocount', '!=', 'Synced');
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('trips')
                    ->whereColumn('trips.uuid', 'invoices.trip_id')
                    ->where('trips.type', 0);
            })
            ->with(['customer', 'invoiceDetails.product', 'driver', 'createdByDriver'])
            ->orderBy('id')
            ->get();

        $payloads = [];
        foreach ($invoices as $invoice) {
            $customer = $invoice->customer;
            if (!$customer) {
                $payloads[] = ['invoice_id' => $invoice->id, 'invoiceno' => $invoice->invoiceno, 'error' => 'Missing customer'];
                continue;
            }
            $salesAgent = 'ADMIN';
            if ($invoice->driver_id && $invoice->driver) {
                $salesAgent = $invoice->driver->name ?? 'ADMIN';
            } elseif ($invoice->is_driver && $invoice->created_by && $invoice->createdByDriver) {
                $salesAgent = $invoice->createdByDriver->name ?? 'ADMIN';
            }
            $details = [];
            foreach ($invoice->invoiceDetails as $d) {
                if (!$d->product) {
                    continue;
                }
                $details[] = [
                    'item_code'   => $d->product->code,
                    'description' => $d->product->name,
                    'quantity'    => (int) $d->quantity,
                    'price'       => (float) $d->price,
                    'totalprice'  => (float) $d->totalprice,
                ];
            }
            $payloads[] = [
                'invoice_id'   => $invoice->id,
                'invoiceno'    => $invoice->invoiceno,
                'date'         => $invoice->date ? $invoice->date->format('Y-m-d H:i:s') : null,
                'customer'     => ['code' => $customer->code, 'tin' => $customer->tin ?? ''],
                'sales_agent'  => $salesAgent,
                'details'      => $details,
            ];
        }

        return response()->json([
            'status'   => true,
            'count'    => count($payloads),
            'invoices' => $payloads,
        ]);
    }

    /**
     * Mark invoices as Synced or Error after AutoCount has processed them.
     *
     * Expected payload:
     * {
     *   "results": [
     *     { "invoice_id": 1, "status": "Synced" },
     *     { "invoice_id": 2, "status": "Error", "message": "Duplicate DocNo" }
     *   ]
     * }
     */
    public function markSynced(Request $request): JsonResponse
    {
        $results = $request->input('results', []);

        if (!is_array($results) || empty($results)) {
            return response()->json([
                'status'  => false,
                'message' => 'No results provided.',
            ], 400);
        }

        $updated = 0;
        $errors = [];

        foreach ($results as $row) {
            $invoiceId = isset($row['invoice_id']) ? (int) $row['invoice_id'] : 0;
            $status = isset($row['status']) ? trim((string) $row['status']) : '';
            $message = isset($row['message']) ? trim((string) $row['message']) : '';

            if ($invoiceId <= 0) {
                $errors[] = "Invalid invoice_id: {$invoiceId}";
                continue;
            }

            $invoice = Invoice::find($invoiceId);
            if (!$invoice) {
                $errors[] = "Invoice not found: {$invoiceId}";
                continue;
            }

            if ($status === 'Synced') {
                $invoice->autocount = 'Synced';
            } elseif ($status === 'Error') {
                $invoice->autocount = $message !== '' ? 'Error: ' . $message : 'Error';
            } else {
                $errors[] = "Invalid status for invoice {$invoiceId}: {$status}";
                continue;
            }

            $invoice->save();
            $updated++;
        }

        return response()->json([
            'status'   => true,
            'message'  => "Updated {$updated} invoice(s).",
            'updated'  => $updated,
            'errors'   => $errors,
        ]);
    }

    /**
     * Get pending invoice payments (trip ended, not synced). Returns raw data only; plugin builds ARPayment/ARPaymentDTL.
     */
    public function getPendingInvoicePayments(Request $request): JsonResponse
    {
        $payments = InvoicePayment::query()
            ->whereIn('type', ['Cash', '1'])
            ->where('status', InvoicePayment::STATUS_COMPLETED)
            ->where(function ($q) {
                $q->whereNull('autocount')->orWhere('autocount', '!=', 'Synced');
            })
            ->whereHas('invoice', function ($q) {
                $q->whereNotNull('invoices.trip_id')
                    ->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('trips')
                            ->whereColumn('trips.uuid', 'invoices.trip_id')
                            ->where('trips.type', 0);
                    });
            })
            ->with(['invoice', 'customer'])
            ->orderBy('id')
            ->get();

        $payloads = [];
        foreach ($payments as $payment) {
            $invoice = $payment->invoice;
            $customer = $payment->customer;
            if (!$invoice || !$customer) {
                $payloads[] = ['payment_id' => $payment->id, 'error' => $invoice ? 'Missing customer' : 'Missing invoice'];
                continue;
            }
            $payloads[] = [
                'payment_id'    => $payment->id,
                'invoice_id'   => $payment->invoice_id,
                'invoiceno'    => $invoice->invoiceno,
                'date'         => $invoice->date ? $invoice->date->format('Y-m-d H:i:s') : ($payment->created_at?->format('Y-m-d H:i:s') ?? null),
                'customer_code'=> $customer->code,
                'amount'       => (float) $payment->amount,
                'remark'       => $payment->remark ?? 'Payment',
            ];
        }

        return response()->json([
            'status'   => true,
            'count'    => count($payloads),
            'payments' => $payloads,
        ]);
    }

    /**
     * Mark invoice payments as Synced or Error after AutoCount has processed them.
     *
     * Expected payload:
     * {
     *   "results": [
     *     { "payment_id": 1, "status": "Synced" },
     *     { "payment_id": 2, "status": "Error", "message": "..." }
     *   ]
     * }
     */
    public function markPaymentSynced(Request $request): JsonResponse
    {
        $results = $request->input('results', []);

        if (!is_array($results) || empty($results)) {
            return response()->json([
                'status'  => false,
                'message' => 'No results provided.',
            ], 400);
        }

        $updated = 0;
        $errors = [];

        foreach ($results as $row) {
            $paymentId = isset($row['payment_id']) ? (int) $row['payment_id'] : 0;
            $status = isset($row['status']) ? trim((string) $row['status']) : '';
            $message = isset($row['message']) ? trim((string) $row['message']) : '';

            if ($paymentId <= 0) {
                $errors[] = "Invalid payment_id: {$paymentId}";
                continue;
            }

            $payment = InvoicePayment::find($paymentId);
            if (!$payment) {
                $errors[] = "Payment not found: {$paymentId}";
                continue;
            }

            if ($status === 'Synced') {
                $payment->autocount = 'Synced';
            } elseif ($status === 'Error') {
                $payment->autocount = $message !== '' ? 'Error: ' . $message : 'Error';
            } else {
                $errors[] = "Invalid status for payment {$paymentId}: {$status}";
                continue;
            }

            $payment->save();
            $updated++;
        }

        return response()->json([
            'status'   => true,
            'message'  => "Updated {$updated} payment(s).",
            'updated'  => $updated,
            'errors'   => $errors,
        ]);
    }

    /**
     * Receive ARPayment records pushed from AutoCount plugin and create invoice payments in system.
     *
     * Expected payload:
     * {
     *   "payments": [
     *     {
     *       "DocKey": 123,
     *       "DocNo": "RC0001",
     *       "DebtorCode": "3000/C15",
     *       "DocDate": "2026-01-10T10:00:00",
     *       "Description": "Some payment",
     *       "PaymentAmt": 100.00
     *     },
     *     ...
     *   ]
     * }
     */
    public function pullInvoicePayments(Request $request): JsonResponse
    {
        $rows = $request->input('payments', []);

        if (!is_array($rows) || empty($rows)) {
            return response()->json([
                'status'  => false,
                'message' => 'No payments provided.',
            ], 400);
        }

        $created = 0;
        $skipped = 0;
        $errors  = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $debtorCode = isset($row['DebtorCode']) ? trim((string) $row['DebtorCode']) : '';
                $amount     = isset($row['PaymentAmt']) ? (float) $row['PaymentAmt'] : 0.0;
                $description = isset($row['Description']) ? trim((string) $row['Description']) : null;

                if ($debtorCode === '' || $amount <= 0) {
                    $skipped++;
                    continue;
                }

                $customer = Customer::where('code', $debtorCode)->first();
                if (!$customer) {
                    $errors[] = "Customer not found for DebtorCode {$debtorCode}";
                    $skipped++;
                    continue;
                }

                // Create a generic cash payment for this customer; no specific invoice_id.
                InvoicePayment::create([
                    'invoice_id'  => null,
                    'customer_id' => $customer->id,
                    'amount'      => $amount,
                    'type'        => 'Cash',
                    'status'      => InvoicePayment::STATUS_COMPLETED,
                    'remark'      => $description,
                ]);

                $created++;
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Created {$created} payment(s), skipped {$skipped}.",
                'created' => $created,
                'skipped' => $skipped,
                'errors'  => $errors,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Error saving pulled payments.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync special prices from AutoCount ItemPrice into SFNoodle special_prices table.
     *
     * Expected payload:
     * {
     *   "prices": [
     *     { "ItemCode": "KTP900ME", "AccNo": "3000/C15", "FixedPrice": 10.0 },
     *     ...
     *   ]
     * }
     */
    public function syncSpecialPrices(Request $request): JsonResponse
    {
        $rows = $request->input('prices', []);

        if (!is_array($rows) || empty($rows)) {
            return response()->json([
                'status'  => false,
                'message' => 'No prices provided.',
            ], 400);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $itemCode   = isset($row['ItemCode']) ? trim((string) $row['ItemCode']) : '';
                $uom        = isset($row['UOM']) ? trim((string) $row['UOM']) : null;
                $accNo      = isset($row['AccNo']) ? trim((string) $row['AccNo']) : '';
                $fixedPrice = isset($row['FixedPrice']) ? (float) $row['FixedPrice'] : 0.0;

                if ($itemCode === '' || $fixedPrice <= 0) {
                    $skipped++;
                    continue;
                }

                // Find product by ItemCode + UOM (SalesUOM mapped to products.uom)
                $query = Product::where('code', $itemCode);
                if ($uom !== null && $uom !== '') {
                    $query->where('uom', $uom);
                }
                $product = $query->first();
                if (!$product) {
                    $errors[] = "Product not found for ItemCode {$itemCode} and UOM {$uom}";
                    $skipped++;
                    continue;
                }

                // If customer is empty, skip as special_prices requires a customer_id
                if ($accNo === '') {
                    $skipped++;
                    continue;
                }

                $customer = Customer::where('code', $accNo)->first();
                if (!$customer) {
                    $errors[] = "Customer not found for AccNo {$accNo}";
                    $skipped++;
                    continue;
                }

                $attrs = [
                    'product_id'  => $product->id,
                    'customer_id' => $customer->id,
                ];

                $values = [
                    'price'  => $fixedPrice,
                    'uom'    => $uom,
                    'status' => 1,
                ];

                $existing = SpecialPrice::where($attrs)->first();
                if ($existing) {
                    $existing->fill($values);
                    $existing->save();
                    $updated++;
                } else {
                    SpecialPrice::create($attrs + $values);
                    $created++;
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Special prices synced. Created {$created}, updated {$updated}, skipped {$skipped}.",
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors'  => $errors,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Error syncing special prices.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}