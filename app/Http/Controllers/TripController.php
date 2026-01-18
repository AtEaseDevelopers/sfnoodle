<?php

namespace App\Http\Controllers;

use App\DataTables\TripDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Repositories\TripRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Trip;
use App\Models\InventoryRequest;
use App\Models\InventoryReturn;
use App\Models\InventoryCount;
use App\Models\InvoiceDetail;
use App\Models\SalesInvoice;
use App\Models\Invoice;
use App\Models\Driver;
use App\Models\Product;
use App\Models\Notification;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;

class TripController extends AppBaseController
{
    /** @var TripRepository $tripRepository*/
    private $tripRepository;

    public function __construct(TripRepository $tripRepo)
    {
        $this->tripRepository = $tripRepo;
    }

    /**
     * Display a listing of the Trip.
     *
     * @param TripDataTable $tripDataTable
     *
     * @return Response
     */
    public function index(TripDataTable $tripDataTable)
    {
        return $tripDataTable->render('trips.index');
    }

    /**
     * Show the form for creating a new Trip.
     *
     * @return Response
     */
    public function create()
    {
        return view('trips.create');
    }

    /**
     * Store a newly created Trip in storage.
     *
     * @param CreateTripRequest $request
     *
     * @return Response
     */
    public function store(CreateTripRequest $request)
    {
        $input = $request->all();

        $trip = $this->tripRepository->create($input);

        Flash::success('Trip saved successfully.');

        return redirect(route('trips.index'));
    }

    /**
     * Display the specified Trip.
     *
     * @param int $id
     *
     * @return Response
     */
    // public function show($id)
    // {
    //     $id = Crypt::decrypt($id);
    //     $trip = $this->tripRepository->find($id);

    //     if (empty($trip)) {
    //         Flash::error(__('trips.trip_not_found'));

    //         return redirect(route('trip.index'));
    //     }

    //     $data = [
    //         'date' => Carbon::parse($trip->date)->toDateString()
    //     ];
        
    //     $sales = DB::Select('select sum(a.totalprice) as sales from(select i.id,sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$trip->driver_id  .' group by i.id) a')[0]->sales;
    //         $cash = DB::Select('select coalesce(sum(coalesce(amount,0)),0) as cash from invoice_payments where type = 1 and status = 1 and driver_id = '.$trip->driver_id  .' and approve_at >= "'.$data['date'].'" and approve_at < "'.date('Y-m-d', strtotime("+1 day", strtotime($data['date']))).'";')[0]->cash;
    //         $bank_in = DB::Select('select coalesce(sum(coalesce(bank_in,0)),0) as bank_in from trips where type = 2 and driver_id = '.$trip->driver_id  .' and created_at >= "'.$data['date'].'" and created_at < "'.date('Y-m-d', strtotime("+1 day", strtotime($data['date']))).'";')[0]->bank_in;
    //         $cash_left = DB::Select('select coalesce(sum(coalesce(cash,0)),0) as cash from trips where type = 2 and driver_id = '.$trip->driver_id  .' and created_at >= "'.$data['date'].'" and created_at < "'.date('Y-m-d', strtotime("+1 day", strtotime($data['date']))).'";')[0]->cash;
    //         // $credit = DB::select('select sum(a.totalprice) as credit from ( select i.id,sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id left join invoice_payments ip on ip.invoice_id = i.id where i.status = 1 and i.date = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and ip.id is null group by i.id ) a')[0]->credit;
    //         $credit = DB::select('select sum(a.totalprice) as credit from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$trip->driver_id  .' and i.paymentterm = 2 group by i.id ) a')[0]->credit;
    //         $bank = DB::select('select sum(a.totalprice) as bank from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$trip->driver_id  .' and i.paymentterm = 3 group by i.id ) a')[0]->bank;
    //         $tng = DB::select('select sum(a.totalprice) as tng from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$trip->driver_id  .' and i.paymentterm = 4 group by i.id ) a')[0]->tng;
    //         $cheque = DB::select('select sum(a.totalprice) as cheque from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$trip->driver_id  .' and i.paymentterm = 5 group by i.id ) a')[0]->cheque;
    //         $productsold = DB::Select('select sum(id.quantity) as productsold from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and id.totalprice > 0 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$trip->driver_id  )[0]->productsold;
    //         $solddetail = DB::select('select p.name, sum(id.quantity) as quantity, sum(id.totalprice) as price from invoices i left join invoice_details id on id.invoice_id = i.id  left join products p on p.id = id.product_id where i.status = 1 and id.totalprice > 0 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$trip->driver_id  .' group by id.product_id, p.id, p.name');
    //         $productfoc = DB::Select('select sum(id.quantity) as productsold from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and id.totalprice = 0 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$trip->driver_id  )[0]->productsold;
    //         $focdetail = DB::select('select p.name, sum(id.quantity) as quantity, sum(id.totalprice) as price from invoices i left join invoice_details id on id.invoice_id = i.id left join products p on p.id = id.product_id where i.status = 1 and id.totalprice = 0  and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$trip->driver_id  .' group by id.product_id, p.id, p.name');
    //         $tripList = DB::select('select t.id, d.name as driver_name, k.name as kelindan_name, l.lorryno from trips t left join drivers d on d.id = t.driver_id left join kelindans k on k.id = t.kelindan_id left join lorrys l on l.id = t.lorry_id where t.driver_id = '.$trip->driver_id  .' and t.type = 1 and t.date >= "'.$data['date'].'" and t.date < "'.$data['date'].' 23:59:59"');
            
    //         $transaction = DB::table('inventory_transactions as i_t')
    //         ->join('products as p', 'p.id', '=', 'i_t.product_id')
    //         ->join('drivers as d', function($join) use ($trip) {
    //             $join->where('d.id', '=', $trip->id)
    //                 ->where(DB::raw("SUBSTRING_INDEX(i_t.user, ' ', 1)"), '=', DB::raw('d.employeeid'))
    //                 ->where(DB::raw("REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(i_t.user, '(', -1), ')', 1), ')', '')"), '=', DB::raw('d.name'));
    //         })
    //         ->where('i_t.type', 5)
    //         ->where('i_t.created_at', '>=', $data['date'] . ' 00:00:00')
    //         ->where('i_t.created_at', '<', $data['date'] . ' 23:59:59')
    //         ->select('p.name', 'i_t.quantity')
    //         ->get();

    //         // $trip = Trip::where('driver_id', $driver->id)
    //         // ->where('date','>=',$data['date'].' 00:00:00')
    //         // ->where('date','<',$data['date'].' 23:59:59')
    //         // ->where('type',1) 
    //         // ->with('driver')
    //         // ->with('kelindan')
    //         // ->with('lorry')
    //         // ->get()
    //         // ->toArray();
    //         $result = [
    //             'sales' => round($sales,2),
    //             'cash' => round($cash,2),
    //             'cash_left' =>  ceil($cash_left),
    //             'bank_in' => round($bank_in,2),
    //             'wastage' => $transaction,
    //             'credit' => round($credit,2),
    //             'onlinebank' =>round($bank,2),
    //             'tng' =>round($tng,2),
    //             'cheque' =>round($cheque,2),
    //             'productsold' => [
    //                 'total_quantity' =>round($productsold,2),
    //                 'details' =>$solddetail
    //             ],
    //             'productfoc' => [
    //                 'total_quantity' =>round($productfoc,2),
    //                 'details' =>$focdetail
    //             ],
    //             'trip' => $tripList
    //         ];
    //     return view('trips.show')->with('trip', (object)$result);
    // }

    /**
     * Show the form for editing the specified Trip.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $trip = $this->tripRepository->find($id);

        if (empty($trip)) {
            Flash::error(__('trips.trip_not_found'));

            return redirect(route('trips.index'));
        }

        return view('trips.edit')->with('trip', $trip);
    }

    /**
     * Update the specified Trip in storage.
     *
     * @param int $id
     * @param UpdateTripRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateTripRequest $request)
    {
        $trip = $this->tripRepository->find($id);

        if (empty($trip)) {
            Flash::error(__('trips.trip_not_found'));

            return redirect(route('trips.index'));
        }

        $trip = $this->tripRepository->update($request->all(), $id);

        Flash::success('Trip updated successfully.');

        return redirect(route('trips.index'));
    }

    /**
     * Remove the specified Trip from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $trip = $this->tripRepository->find($id);

        if (empty($trip)) {
            Flash::error(__('trips.trip_not_found'));

            return redirect(route('trips.index'));
        }

        $this->tripRepository->delete($id);

        Flash::success('Trip deleted successfully.');

        return redirect(route('trips.index'));
    }


    public static function generateTripReport($trip_id) 
    {
        $trip = Trip::where('uuid', $trip_id)->first();
        
        if (!$trip) {
            throw new \Exception('Trip not found');
        }

        // Opening stock (from JSON snapshot) - products the driver had at trip start
        $opening = collect(json_decode($trip->stock_data, true));
        
        $endtrip = Trip::where('driver_id', $trip->driver_id)->where('type',Trip::END_TRIP)->where('uuid', $trip->uuid)->first();
        $driver = Driver::where('id', $trip->driver_id)->first();

        // Fix: Use proper date filtering
        $startTime = $trip->date;
        $endTime = $endtrip->date; 
        
        // StockIn: Approved stock requests during trip
        $stockIn = InventoryRequest::where('driver_id', $trip->driver_id)
            ->where('status', InventoryRequest::STATUS_APPROVED)
            ->where('trip_id', $trip_id) // Use trip_id directly
            ->get();
        
        // Sales: From invoices during trip
        $sales = InvoiceDetail::whereHas('invoice', function($q) use ($trip_id) {
                $q->where('trip_id', $trip_id);
            })
            ->groupBy('product_id')
            ->select('product_id', DB::raw('SUM(quantity) as total_sales'))
            ->get();
        
        // Return: Approved stock returns during trip
        $returns = InventoryReturn::where('driver_id', $trip->driver_id)
            ->where('status', InventoryReturn::STATUS_APPROVED)
            ->where('trip_id', $trip_id) // Use trip_id directly
            ->get();
        
        // Stock Count: Get counted_quantity from approved counts
        $stockCountsData = InventoryCount::where('driver_id', $trip->driver_id)
            ->where('status', InventoryCount::STATUS_APPROVED)
            ->where('trip_id', $trip_id)
            ->get();

        // Process stock counts to get total counted quantity per product
        $stockCounts = [];
        foreach ($stockCountsData as $count) {
            $items = $count->items ?? [];
            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $countedQty = $item['counted_quantity'] ?? 0;
                
                if ($productId && $countedQty !== '' && $countedQty !== null) {
                    if (!isset($stockCounts[$productId])) {
                        $stockCounts[$productId] = 0;
                    }
                    $stockCounts[$productId] += (float)$countedQty;
                }
            }
        }
        
        // ================================================
        // FIXED: Get ALL products involved in the trip
        // ================================================
        
        // Get all product IDs from:
        // 1. Opening stock
        $allProductIds = $opening->pluck('product_id')->toArray();
        
        // 2. Stock in during trip
        $stockInProductIds = $stockIn->pluck('product_id')->toArray();
        
        // 3. Sales during trip  
        $salesProductIds = $sales->pluck('product_id')->toArray();
        
        // 4. Returns during trip
        $returnProductIds = $returns->pluck('product_id')->toArray();
        
        // 5. Stock counts
        $stockCountProductIds = array_keys($stockCounts);
        
        // Merge all product IDs
        $allProductIds = array_unique(array_merge(
            $allProductIds,
            $stockInProductIds,
            $salesProductIds,
            $returnProductIds,
            $stockCountProductIds
        ));
        
        // Fetch product details for all products
        $products = Product::whereIn('id', $allProductIds)
            ->select('id', 'name', 'code')
            ->get()
            ->keyBy('id');
        
        // Get sales orders and invoices for this trip
        $salesOrder = SalesInvoice::where('trip_id', $trip_id)->get();
        $invoice = Invoice::where('trip_id', $trip_id)->where('status',0)->get();
        
        // Prepare stock summary array
        $stockSummary = [];
        
        $credit_invoice = Invoice::where('trip_id', $trip_id)->where('status',0)->where('paymentterm','=', 'Credit')->get();
        $cash_invoice = Invoice::where('trip_id', $trip_id)->where('status',0)->where('paymentterm','=', 'Cash')->get();

        // Process ALL products involved in the trip
        foreach($allProductIds as $productId) {
            // Get product details
            $product = $products[$productId] ?? null;
            
            // Find opening quantity for this product (0 if not in opening stock)
            $openingProduct = $opening->firstWhere('product_id', $productId);
            $openingQty = $openingProduct['quantity'] ?? 0;
            $productCode = $openingProduct['product_code'] ?? ($product ? $product->code : '');
            $productName = $openingProduct['product_name'] ?? ($product ? $product->name : 'Product ' . $productId);
            
            // Get stockIn for this product
            $stockInQty = $stockIn->where('product_id', $productId)->sum('quantity');
            
            // Get sales for this product  
            $salesQty = $sales->where('product_id', $productId)->first()->total_sales ?? 0;
            
            // Get returns for this product
            $returnQty = $returns->where('product_id', $productId)->sum('quantity');
            
            // Calculate closing (expected)
            $closing = $openingQty + $stockInQty - $salesQty - $returnQty;
            
            // Get actual from stock count - use counted_quantity
            $actual = $stockCounts[$productId] ?? 0;
            
            // Calculate variance
            $variance = $actual - $closing;
            
            $stockSummary[] = [
                'product_id' => $productId,
                'product_code' => $productCode,
                'product_name' => $productName,
                'open' => $openingQty,
                'stock_in' => $stockInQty,
                'sales' => $salesQty,
                'return' => $returnQty,
                'closing' => $closing,
                'stock_count' => $actual,
                'variance' => $variance
            ];
        }
        
        // Sort stock summary by product name for better readability
        usort($stockSummary, function($a, $b) {
            return strcmp($a['product_name'], $b['product_name']);
        });
        
        // Prepare full report
        $report = [
            'trip_info' => [
                'trip_id' => 'T-' . (string)$trip->uuid,
                'driver' => $driver,
                'start_time' => $startTime, 
                'end_time' => $endTime,
            ],
            'stock_summary' => $stockSummary,
            'sales_summary' => [
                'total_invoices' => $invoice->count(),
                'total_sales_orders' => $salesOrder->count(),
                'total_amount' => $invoice->sum('total') ?? 0,
                'total_credit' => $credit_invoice->sum('total') ?? 0,
                'total_cash' => $cash_invoice->sum('total') ?? 0,
                'invoices' => $invoice,
                'sales_orders' => $salesOrder
            ],
            'sales_by_product' => $sales->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'total_sales' => $item->total_sales
                ];
            })
        ];

        return $report;
    }

    public function generateTripReportPDF($trip_id)
    {
        $trip = Trip::where('uuid', $trip_id)->first();
        if (request()->has('notification_id')) {
            $notificationId = request()->input('notification_id');
            $notification = Notification::find($notificationId);
            
            if ($notification && !$notification->is_read) {
                // Mark the notification as read
                $notification->update(['is_read' => true]);
            }
        }
        
        if (!$trip) {
            throw new \Exception('Trip not found');
        }

        // Get report data (using your existing function)
        $report = $this->generateTripReport($trip_id);
        
        // Get driver info
        $driver = Driver::find($trip->driver_id);
        
        // Get product names for sales summary
        $productDetails = [];

        foreach ($report['sales_by_product'] as $salesItem) {
            $product = Product::find($salesItem['product_id']);
            if ($product) {
                // Get total amount for this product from invoices
                $totalAmount = 0;
                foreach ($report['sales_summary']['invoices'] as $invoice) {
                    foreach ($invoice->invoiceDetails as $detail) {
                        if ($detail->product_id == $salesItem['product_id']) {
                            $totalAmount += $detail->totalprice;
                        }
                    }
                }
                
                $productDetails[] = [
                    'code' => $product->code,
                    'name' => $product->name,
                    'quantity' => $salesItem['total_sales'],
                    'uom' => $product->category->name?? '', // Use product unit or default to PACK
                    'amount' => number_format($totalAmount, 2)
                ];
            }
        }
        
        // Format stock summary
        $stockSummaryData = [];
        foreach ($report['stock_summary'] as $stock) {
            $product = Product::find($stock['product_id']);
            $stockSummaryData[] = [
                'brand' => $product->code ?? '',
                'open' => $stock['open'],
                'stock_in' => $stock['stock_in'],
                'sales' => -$stock['sales'], // Negative as in sample
                'return' => $stock['return'],
                'variance' => $stock['variance'],
                'stock_out' => 0, // As per your requirement
                'close' => $stock['stock_count'] // Use actual stock count
            ];
        }
        // Format invoices list
        $invoicesList = [];
        foreach ($report['sales_summary']['invoices'] as $invoice) {
            // Get customer name
            $customer = Customer::find($invoice->customer_id);
            $customerName = $customer ? $customer->company : 'N/A';
            
            // Get invoice total
            $invoiceTotal = 0;
            foreach ($invoice->invoiceDetails as $detail) {
                $invoiceTotal += $detail->totalprice;
            }
            
            $invoicesList[] = [
                'doc_no' => $invoice->invoiceno,
                'status' => 'Invoice', // You can customize based on your status
                'company_name' => $customerName,
                'paymentterm' => $invoice->paymentterm ?? '-',
                'amount' => 'RM ' . number_format($invoiceTotal, 2)
            ];
        }
        
        // Format sales orders list
        $salesOrdersList = [];
        foreach ($report['sales_summary']['sales_orders'] as $salesOrder) {
            // Get customer name
            $customer = Customer::find($salesOrder->customer_id);
            $customerName = $customer ? $customer->company : 'N/A';
            
            // Calculate sales order total (you might need to load sales order details)
            $salesOrderTotal = 0; // You'll need to calculate this from sales_order_details
            
            $salesOrdersList[] = [
                'doc_no' => $salesOrder->invoiceno,
                'status' => 'Sales Order',
                'company_name' => $customerName,
                'outstanding' => '-',
                'amount' => 'RM ' . number_format($salesOrderTotal, 2)
            ];
        }
        
        // Combine invoices and sales orders
        $documentsList = array_merge($invoicesList);

        // Prepare data for PDF
        $data = [
            // Company Information (fixed as per sample)
            'company_name' => 'SF Noodles Sdn. Bhd.',
            'roc_no' => '(FKA Soon Fatt Foods Sdn Bhd) ROC No. 201001017887',
            'address' => '48, Jin TPP 1/18, Taman Industri Puchong, 47100 Puchong, Selangor',
            'phone' => 't: 03-80611490 / 012-3111531',
            'email' => 'email: account@sfnoodles.com',
            
            // Trip Information
            'salesman' => $driver->name ?? 'N/A',
            'printed_time' => Carbon::now()->format('d M Y h:i A'),
            'trip_id' => 'T-' . $trip->uuid,
            'start_time' => Carbon::parse($trip->date)->format('d M Y h:i A'),
            'end_time' => $report['trip_info']['end_time'] ? 
                        Carbon::parse($report['trip_info']['end_time'])->format('d M Y h:i A') : 
                        Carbon::now()->format('d M Y h:i A'),
            
            // Sales Summary
            'sales_summary' => $productDetails,
            'total_quantity' => array_sum(array_column($productDetails, 'quantity')),
            'total_amount' => 'RM ' . number_format(array_sum(array_map(function($item) {
                return floatval(str_replace('RM ', '', $item['amount']));
            }, $productDetails)), 2),
            
            // Stock Summary
            'stock_summary' => $stockSummaryData,
            'total_open' => array_sum(array_column($stockSummaryData, 'open')),
            'total_stock_in' => array_sum(array_column($stockSummaryData, 'stock_in')),
            'total_sales' => array_sum(array_column($stockSummaryData, 'sales')),
            'total_return' => array_sum(array_column($stockSummaryData, 'return')),
            'total_variance' => array_sum(array_column($stockSummaryData, 'variance')),
            'total_stock_out' => array_sum(array_column($stockSummaryData, 'stock_out')),
            'total_close' => array_sum(array_column($stockSummaryData, 'close')),
            
            // Documents List
            'documents_list' => $documentsList,
            'total_documents' => count($documentsList),
            'total_documents_amount' => 'RM ' . number_format(array_sum(array_map(function($item) {
                return floatval(str_replace('RM ', '', $item['amount']));
            }, $documentsList)), 2),
            // Report totals
            'total_cash' => 'RM ' . number_format($report['sales_summary']['total_cash'], 2),
            'total_credit' => 'RM ' . number_format($report['sales_summary']['total_credit'], 2),

            'grand_total' => 'RM ' . number_format($report['sales_summary']['total_amount'], 2)
        ];
        

        try{
            $pdf = Pdf::loadView('reports.print', $data);
            return $pdf->setPaper('a4', 'portrait')
                    ->setOptions([
                        'isPhpEnabled' => true, 
                        'isRemoteEnabled' => true,
                        'defaultFont' => 'sans-serif'
                    ])
                    ->stream('trip_summary_' .$driver->name.'_'. $trip->date . '.pdf');

            return view('reports.print', $data);
        }
        catch(Exception $e){
            dd($e->getMessage());

            abort(404);
        }

    }
    
}   
