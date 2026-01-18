<?php

namespace App\Http\Controllers;

use App\DataTables\ReportDataTable;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Requests\CreateReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Repositories\ReportRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use App\Models\Report;
use Response;
use App\Models\Reportdetail;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;
use \Exception;
use Mockery\Expectation;
use App\Models\paymentdetail;
use App\Models\Driver;
use App\Models\Lorry;
use App\Models\Code;
use App\Models\DailyInventoryBalance;
use App\Models\Vendor;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use File;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoicePayment;
use App\Models\Customer;
use App\Models\Agent;
use App\Models\Product;
use App\Models\User;
use App\Models\InventoryCount;
use App\Exports\SellerInformationExport;
use App\Exports\MonthlySaleReport;
use App\Exports\DailySaleReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends AppBaseController
{
    /** @var ReportRepository $reportRepository*/
    private $reportRepository;

    public function __construct(ReportRepository $reportRepo)
    {
        $this->reportRepository = $reportRepo;
    }

    /**
     * Display a listing of the Report.
     *
     * @param ReportDataTable $reportDataTable
     *
     * @return Response
     */
    public function index(ReportDataTable $reportDataTable)
    {
        return $reportDataTable->render('reports.index');
    }

    /**
     * Show the form for creating a new Report.
     *
     * @return Response
     */
    public function create()
    {
        return view('reports.create');
    }

    /**
     * Store a newly created Report in storage.
     *
     * @param CreateReportRequest $request
     *
     * @return Response
     */
    public function store(CreateReportRequest $request)
    {
        $input = $request->all();

        $report = $this->reportRepository->create($input);

        Flash::success(__('report.report_saved_successfully'));

        return redirect(route('reports.index'));
    }

    /**
     * Display the specified Report.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $report = $this->reportRepository->find($id);

        if (empty($report)) {
            Flash::error(__('report.report_not_found'));
            return redirect(route('reports.index'));
        }

        // Instead of using reportdetails, we'll handle the form fields based on report name/type
        $reportType = $report->sqlvalue;
        
        // Initialize variables based on report type
        $formFields = [];
        
        if ($reportType == 'DAILY_SALES_REPORT') {
            // Daily Sales Report - only needs date
            $formFields = [
                'report_date' => [
                    'label' => 'Report Date',
                    'type' => 'date',
                    'required' => true
                ]
            ];
        } elseif ($reportType == 'STOCK_COUNT_REPORT') {
            // Inventory Count Report - needs date, driver, and trip
            $formFields = [
                'report_date' => [
                    'label' => 'Report Date',
                    'type' => 'date',
                    'required' => true
                ],
                'driver_id' => [
                    'label' => 'Driver',
                    'type' => 'dropdown',
                    'required' => false,
                    'depends_on' => 'report_date' // This field depends on date selection
                ],
                'trip_id' => [
                    'label' => 'Trip Number',
                    'type' => 'dropdown',
                    'required' => true,
                    'depends_on' => 'driver_id' // This field depends on driver selection
                ]
            ];
        }

        return view('reports.show')
            ->with('report', $report)
            ->with('formFields', $formFields);
    }

    public function getDriversByDate(Request $request)
    {
        $date = $request->get('date');
        
        // Validate date
        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => 'Date parameter is required'
            ], 400);
        }
        try {
            $driverIds = Trip::whereDate('date', $date)
                ->distinct() // Get unique driver IDs
                ->pluck('driver_id');
            // Get driver details for the unique IDs
            $drivers = Driver::whereIn('id', $driverIds)
                ->where('status', 1) // Active drivers only
                ->orderBy('name')
                ->get(['id', 'name']);
        
            return response()->json([
                'success' => true,
                'drivers' => $drivers
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching drivers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading drivers'
            ], 500);
        }
    }

    /**
     * Get trips for a specific driver and date
     */
    public function getTripsByDriverDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'driver_id' => 'required'
        ]);
        
        $date = $request->get('date');
        $driverId = $request->get('driver_id');
        
        try {
            $trips = Trip::whereDate('date', $date)
                ->where('driver_id', $driverId)
                ->where('type', Trip::END_TRIP)
                ->get(['id', 'uuid','driver_id']);
            
            return response()->json([
                'success' => true,
                'trips' => $trips
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching trips: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading trips'
            ], 500);
        }
    }

    /**
     * Run the report
     */
    public function run(Request $request)
    {        
        $request->validate([
            'report_id' => 'required|exists:reports,id',
            'report_date' => 'required|date_format:Y-m-d'
        ]);
        
        $report = $this->reportRepository->find($request->get('report_id'));
        
        if (!$report) {
            Flash::error(__('report.report_not_found'));
            return redirect(route('reports.index'));
        }
        
        try {
            if ($report->sqlvalue == 'DAILY_SALES_REPORT') {
                return $this->generateDailySalesReport($request);
            } elseif ($report->sqlvalue == 'STOCK_COUNT_REPORT') {
                return $this->generateStockCountReport($request);
            } else {
                Flash::error('Unsupported report type');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            \Log::error('Report generation error: ' . $e->getMessage());
            Flash::error('Error generating report: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Generate Daily Sales Report
     */
    private function generateDailySalesReport(Request $request)
    {
        $date = $request->get('report_date');
        
        // Your report generation logic here
        // Example: $data = Sales::whereDate('sale_date', $date)->get();
        
        // For now, just show a message
        Flash::success("Daily Sales Report for {$date} generated successfully");
        return redirect()->back();
    }

    /**
     * Generate Stock Count Report
     */
    public function showStockCountReport($driverId, $tripId)
    {
        // You might want to validate the trip belongs to the driver
        $trip = Trip::where('driver_id', $driverId)
                    ->where('uuid', $tripId)
                    ->firstOrFail();
        
        // Create a request object manually to pass to your existing function
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'driver_id' => $driverId,
            'trip_id' => $tripId,
            'report_date' => $trip->date, // If you need date
        ]);
        
        return $this->generateStockCountReport($request);
    }

    private function generateStockCountReport(Request $request)
    {
        $driverId = $request->get('driver_id');
        $tripId = $request->get('trip_id');

        $driver = Driver::find($driverId);

        $starttrip = Trip::where('driver_id', $driverId)->where('type',Trip::START_TRIP)->where('uuid', $tripId)->first();
        $endtrip = Trip::where('driver_id', $driverId)->where('type',Trip::END_TRIP)->where('uuid', $tripId)->first();

        // Get all approved inventory counts for this trip
        $inventoryCounts = InventoryCount::where('driver_id', $driverId)
            ->where('status', InventoryCount::STATUS_APPROVED)
            ->where('trip_id', $tripId)
            ->get();

        // Process inventory counts to get summary data
        $stockCountSummary = [];
        $productData = [];
        
        foreach ($inventoryCounts as $count) {
            $items = $count->items ?? [];
            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $currentQty = $item['current_quantity'] ?? 0;
                $countedQty = $item['counted_quantity'] ?? 0;
                
                if ($productId && $countedQty !== '' && $countedQty !== null) {
                    if (!isset($stockCountSummary[$productId])) {
                        $stockCountSummary[$productId] = [
                            'product_id' => $productId,
                            'product_name' => $item['product_name'] ?? 'Product ' . $productId,
                            'current_quantity' => 0,
                            'counted_quantity' => 0,
                            'difference' => 0
                        ];
                    }
                    
                    // Sum up quantities from all counts
                    $stockCountSummary[$productId]['current_quantity'] += (float)$currentQty;
                    $stockCountSummary[$productId]['counted_quantity'] += (float)$countedQty;
                    $stockCountSummary[$productId]['difference'] = 
                        $stockCountSummary[$productId]['counted_quantity'] - 
                        $stockCountSummary[$productId]['current_quantity'];
                }
            }
        }
        
        // Convert to collection for easier handling
        $stockCounts = collect($stockCountSummary)->values();
        
        // Get product details for all products in the count
        $productIds = $stockCounts->pluck('product_id')->toArray();
        $products = \App\Models\Product::whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
        
        // Update product names from database (more accurate)
        foreach ($stockCounts as &$count) {
            $product = $products[$count['product_id']] ?? null;
            if ($product) {
                $count['product_name'] = $product->name;
                $count['product_code'] = $product->code ?? '';
            } else {
                $count['product_code'] = '';
            }
        }
        
        // Get latest approved count for approved_by info
        $latestCount = $inventoryCounts->sortByDesc('created_at')->first();
        
        // Calculate totals
        $totalCurrent = $stockCounts->sum('current_quantity');
        $totalCounted = $stockCounts->sum('counted_quantity');
        $totalDifference = $stockCounts->sum('difference');
        
        $data =[
            'company_name' => 'SF Noodles Sdn. Bhd.',
            'roc_no' => '(FKA Soon Fatt Foods Sdn Bhd) ROC No. 201001017887',
            'address' => '48, Jin TPP 1/18, Taman Industri Puchong, 47100 Puchong, Selangor',
            'phone' => 't: 03-80611490 / 012-3111531',
            'email' => 'email: account@sfnoodles.com',
            
            // Trip Information
            'salesman' => $driver->name ?? 'N/A',
            'printed_time' => Carbon::now()->format('d M Y h:i A'),
            'approved_by' => $latestCount ? (User::find($latestCount->approved_by)->name ?? '-') : '-',
            'trip_id' => 'T-' . ($starttrip->uuid ?? $tripId),
            'start_time' => $starttrip ? Carbon::parse($starttrip->date)->format('d M Y h:i A') : 'N/A',
            'end_time' => $endtrip ? Carbon::parse($endtrip->date)->format('d M Y h:i A') : 'N/A',
            
            // Stock Count Data
            'stock_counts' => $stockCounts,
            'products' => $products,
            'total_current' => $totalCurrent,
            'total_counted' => $totalCounted,
            'total_difference' => $totalDifference,
            
            // Additional data for display
            'inventory_counts' => $inventoryCounts,
            'has_data' => $stockCounts->isNotEmpty(),
        ];
        
        try {
            $pdf = Pdf::loadView('reports.stockCountreport', $data);

            return $pdf->setPaper('a4', 'portrait')
                    ->setOptions([
                        'isPhpEnabled' => true, 
                        'isRemoteEnabled' => true,
                        'defaultFont' => 'sans-serif',
                    ])
                    ->stream('stock_count_' . $driver->name . '_' . $tripId . '_' . date('Ymd') . '.pdf');

        } catch(Exception $e) {
            dd($e->getMessage());
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified Report.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $report = $this->reportRepository->find($id);

        if (empty($report)) {
            Flash::error(__('report.report_not_found'));

            return redirect(route('reports.index'));
        }

        return view('reports.edit')->with('report', $report);
    }

    /**
     * Update the specified Report in storage.
     *
     * @param int $id
     * @param UpdateReportRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateReportRequest $request)
    {
        $report = $this->reportRepository->find($id);

        if (empty($report)) {
            Flash::error(__('report.report_not_found'));

            return redirect(route('reports.index'));
        }

        $report = $this->reportRepository->update($request->all(), $id);

        Flash::error(__('report.report_updated_successfully'));

        return redirect(route('reports.index'));
    }



    public function monthlysalereport(Request $request)
    {
        // return $request->all();
        $monthYear = $request->month_year;
        $startDate = Carbon::parse($monthYear)->startOfMonth();
        $endDate = Carbon::parse($monthYear)->endOfMonth();
        
        if($request->products == '%'){
            $products = Product::pluck('id')->toArray();
        } else {
            $products = $request->products;
        }
        
        if($request->agent == '%'){
            $agents = Agent::pluck('id')->toArray();
        } else {
            $agents = [$request->agent];
        }
        
        if($request->customergroup == '%'){
            $p_customer = Customer::pluck('id')->toArray();
        } else {
            $p_customer = Customer::whereRaw("FIND_IN_SET(?, `group`)", [$request->customergroup])->pluck('id')->toArray();
        }
            
        $invoices = Invoice::whereBetween('date', [$startDate, $endDate])
                ->whereHas('invoicedetail', function ($query) use ($products) {
                    $query->whereIn('product_id', $products);
                })
                ->where(function($q) use ($agents, $p_customer) {
                    $q->whereIn('agent_id', $agents)->orWhereIn('customer_id', $p_customer); // Filter by customer IDs
                })
                ->with('customer', 'agent', 'invoicedetail.product')
                ->get();

        $reportData = [];
    
        foreach ($invoices as $invoice) {
            $customerName = $invoice->customer->company ?? '';
            $agentName = $invoice->agent->name ?? '';
            $day = Carbon::parse($invoice->date)->day; // Day of the month
    
            if (!isset($reportData[$customerName][$agentName])) {
                // Initialize empty array for each customer-agent combination
                $reportData[$customerName][$agentName] = array_fill(1, 31, ['quantity' => 0, 'price' => 0]);
                $reportData[$customerName][$agentName]['Total Quantity'] = 0;
                $reportData[$customerName][$agentName]['Total Price'] = 0;
                $reportData[$customerName][$agentName]['Average Price'] = 0;
            }
    
            foreach ($invoice->invoicedetail as $detail) {
                $reportData[$customerName][$agentName][$day]['quantity'] += $detail->quantity;
                $reportData[$customerName][$agentName][$day]['price'] += $detail->totalprice;
    
                // Add to totals
                $reportData[$customerName][$agentName]['Total Quantity'] += $detail->quantity;
                $reportData[$customerName][$agentName]['Total Price'] += $detail->totalprice;
            }
    
            // Calculate average price for this invoice
            if ($reportData[$customerName][$agentName]['Total Quantity'] > 0) {
                $reportData[$customerName][$agentName]['Average Price'] =
                    $reportData[$customerName][$agentName]['Total Price'] /
                    $reportData[$customerName][$agentName]['Total Quantity'];
            }
        }
    
        // Export the data to Excel
        return Excel::download(new MonthlySaleReport($reportData, $monthYear), 'monthly_sale_report.xlsx');
    }

    public function daily_sales_report_excel(Request $request){
        // return $request->all();
        
        $day = $request->date;
        
        
        $date = Carbon::parse($day)->format('Y-m-d');

        // Get product IDs based on the request
        //$products = $request->products != '%' ? Product::pluck('id')->toArray() : $request->products;

        // Get driver IDs based on the request
        $p_driver = explode(',',$request['driver']) ?? [];
        
        if($p_driver[0] == '%'){
            $p_drivers = 'All';
            $p_driver = Driver::pluck('id')->toArray();
        }else{
            $p_drivers = implode(', ',Driver::whereIn('id',$p_driver)->pluck('name')->toArray());
        }
        
        //$lorrys = $request->lorrys != '%' ? Lorry::pluck('id')->toArray() : $request->lorrys;

        // Get customer IDs based on the customer group
       /* $p_customer = $request->customergroup == '%'
            ? Customer::pluck('id')->toArray() 
            : Customer::whereRaw("FIND_IN_SET(?, `group`)", [$request->customergroup])->pluck('id')->toArray();
            
        $payment_term = $request->payment_term == null ? null : ($request->payment_term == '%' ? [1, 2] : [$request->payment_term]);*/

        // Filter invoices based on the selected criteria
        $invoices = Invoice::whereIn('driver_id', $p_driver)
                ->whereDate('date', $date)
                /*->whereHas('invoicedetail')
                //->whereIn('customer_id', $p_customer)*/
                ->with('customer', 'driver', 'invoicedetail.product');
                
        $invoices = $invoices->pluck('id');
        $invoiceDetails = InvoiceDetail::whereIn('invoice_id', $invoices)->orderBy('invoice_id', 'desc')->get();
        
        return Excel::download(new DailySaleReportExport($invoiceDetails,$p_driver,$date), 'DailySaleReport.xlsx');    
    }

    public function report($id)
    {
        if($id == 0){
            abort(404);
        }
        try{
            $result = DB::select('select * from reportlists where id='.$id)[0];
            $report = Report::where('id',$result->report_id)->select('name','id')->first();
            return view('reports.report')->with('result', $result)->with('report', $report);
        }
        catch(Exception $e){
            abort(404);
        }
    }

    public function newreport()
    {
        return view('reports.newreport');
    }

    public function customer_statement_of_account(Request $request)
    {
        $p_customer = $request['p_customer'] ?? '';
        $p_datefrom = ($request['p_datefrom'] . ' 00:00:00') ?? '';
        $p_dateto = ($request['p_dateto'] . ' 23:59:59') ?? '';

        $p_customers = Customer::where('id',$p_customer)->first();

        $params = array(
            'p_customer' => $request['p_customer'],
            'p_customers' => $p_customers,
            'p_datefrom' => $request['p_datefrom'],
            'p_dateto' => $request['p_dateto']
        );

        $invoices = Invoice::leftJoin('invoice_details', function($join) {
            $join->on('invoices.id', '=', 'invoice_details.invoice_id');
          })
        ->where('invoices.customer_id',$p_customer)
        ->where('invoices.date', '>=', $p_datefrom)
        ->where('invoices.date', '<=', $p_dateto)
        ->select('invoices.date',DB::raw('invoices.invoiceno as reference'),DB::raw('"Sales" as descr'),DB::raw('sum(invoice_details.totalprice) as debit'),DB::raw('null as credit'))
        ->groupBy('invoices.id','invoices.date','invoices.invoiceno');
        $payments = InvoicePayment::where('customer_id',$p_customer)
        ->where('approve_at', '>=', $p_datefrom)
        ->where('approve_at', '<=', $p_dateto)
        ->select(DB::raw('approve_at as date'),DB::raw('DATE_FORMAT(approve_at,"%Y%m%d") as reference'),DB::raw('"Payment For Account" as descr'),DB::raw('null as debit'),DB::raw('amount as credit'));
        $data = $invoices->union($payments)->orderBy('date','asc')->get()->toArray();
        $params['total_debit'] = $invoices->count();
        $params['sum_debit'] = $invoices->sum('debit');
        $params['total_credit'] = $payments->count();
        $params['sum_credit'] = $payments->sum('amount');
        $pdf = Pdf::loadView('reports.customer_statement_of_account', array('data'=>$data,'params'=>$params));
        return $pdf->setPaper('a4', 'portrait')->setOptions(['isPhpEnabled' => true])->set_option('isRemoteEnabled', true)->stream('SELLER_INFORMATION_RECORD.pdf');
        return view('reports.customer_statement_of_account')->with('invoices', $invoices)->with('params',$params);
    }

    public function seller_information_record(Request $request)
    {
        $p_driver = explode(',',$request['p_driver']) ?? [];
        $p_agent = explode(',',$request['p_agent']) ?? [];
        $p_customer_group = explode(',',$request['p_customer_group']) ?? [];
        $p_customer = explode(',',$request['p_customer']) ?? [];
        $p_datefrom = ($request['p_datefrom'] . ' 00:00:00') ?? '';
        $p_dateto = ($request['p_dateto'] . ' 23:59:59') ?? '';

        if($p_driver[0] == '%'){
            $p_drivers = 'All';
            $p_driver = Driver::pluck('id')->toArray();
        }else{
            $p_drivers = implode(', ',Driver::whereIn('id',$p_driver)->pluck('name')->toArray());
        }
        
        if($p_agent[0] == '%'){
            $p_agents = 'All';
            $p_agent = Agent::pluck('id')->toArray();
        }else{
            $p_agents = implode(', ',Agent::whereIn('id',$p_agent)->pluck('name')->toArray());
        }
        
        if($p_customer_group[0] == '%'){
            $p_customer_groups = 'All';
        }else{
            $p_customer_groups = implode(', ',Code::whereIn('value',$p_customer_group)->where('code', 'customer_group')->pluck('description')->toArray());
        }

        if($p_customer[0] == '%'){
            $p_customers = 'All';
            if($p_customer_group[0] == '%'){
                $p_customer = Customer::pluck('id')->toArray();
            }
            else{
                $p_customer = Customer::whereRaw("FIND_IN_SET(?, `group`)", [$p_customer_group[0]])->pluck('id')->toArray();
            }
        }else{
            $p_customers = implode(', ',Customer::whereIn('id',$p_customer)->pluck('company')->toArray());
        }

        $params = array(
            'p_driver' => $request['p_driver'],
            'p_drivers' => $p_drivers,
            'p_agent' => $request['p_agent'],
            'p_agents' => $p_agents,
            'p_customer_group' => $request['p_customer_group'],
            'p_customer_groups' => $p_customer_groups,
            'p_customer' => $request['p_customer'],
            'p_customers' => $p_customers,
            'p_datefrom' => $request['p_datefrom'],
            'p_dateto' => $request['p_dateto']
        );

        $invoices = Invoice::whereIn('driver_id',$p_driver)
        ->whereIn('agent_id',$p_agent)
        ->when(!empty($p_customer), function ($query) use ($p_customer) {
            return $query->whereIn('customer_id', $p_customer);
        }) 
        ->where('date', '>=', $p_datefrom)
        ->where('date', '<=', $p_dateto)
        ->with('customer')
        ->withSum('invoicedetail','totalprice')
        ->with('invoicedetail.product')
        ->orderby('date','asc')
        ->get()->toArray();
        $pdf = Pdf::loadView('reports.seller_information_record', array('invoices'=>$invoices,'params'=>$params));
        return $pdf->setPaper('a4', 'portrait')->setOptions(['isPhpEnabled' => true])->stream('SELLER_INFORMATION_RECORD.pdf');
    }
    
    public function seller_information_record_excel(Request $request)
    {
        $p_driver = explode(',',$request['p_driver']) ?? [];
        $p_agent = explode(',',$request['p_agent']) ?? [];
        $p_customer_group = explode(',',$request['p_customer_group']) ?? [];
        $p_customer = explode(',',$request['p_customer']) ?? [];
        $p_datefrom = ($request['p_datefrom'] . ' 00:00:00') ?? '';
        $p_dateto = ($request['p_dateto'] . ' 23:59:59') ?? '';

        if($p_driver[0] == '%'){
            $p_drivers = 'All';
            $p_driver = Driver::pluck('id')->toArray();
        }else{
            $p_drivers = implode(', ',Driver::whereIn('id',$p_driver)->pluck('name')->toArray());
        }
        
        if($p_agent[0] == '%'){
            $p_agents = 'All';
            $p_agent = Agent::pluck('id')->toArray();
        }else{
            $p_agents = implode(', ',Agent::whereIn('id',$p_agent)->pluck('name')->toArray());
        }
        
        if($p_customer_group[0] == '%'){
            $p_customer_groups = 'All';
        }else{
            $p_customer_groups = implode(', ',Code::whereIn('value',$p_customer_group)->where('code', 'customer_group')->pluck('description')->toArray());
        }

        if($p_customer[0] == '%'){
            $p_customers = 'All';
            if($p_customer_group[0] == '%'){
                $p_customer = Customer::pluck('id')->toArray();
            }
            else{
                $p_customer = Customer::whereRaw("FIND_IN_SET(?, `group`)", [$p_customer_group[0]])->pluck('id')->toArray();
            }
        }else{
            $p_customers = implode(', ',Customer::whereIn('id',$p_customer)->pluck('company')->toArray());
        }

        $params = array(
            'p_driver' => $request['p_driver'],
            'p_drivers' => $p_drivers,
            'p_agent' => $request['p_agent'],
            'p_agents' => $p_agents,
            'p_customer_group' => $request['p_customer_group'],
            'p_customer_groups' => $p_customer_groups,
            'p_customer' => $request['p_customer'],
            'p_customers' => $p_customers,
            'p_datefrom' => $request['p_datefrom'],
            'p_dateto' => $request['p_dateto']
        );

        $invoices = Invoice::whereIn('driver_id',$p_driver)
        ->whereIn('agent_id',$p_agent)
        ->when(!empty($p_customer), function ($query) use ($p_customer) {
            return $query->whereIn('customer_id', $p_customer);
        }) 
        ->where('date', '>=', $p_datefrom)
        ->where('date', '<=', $p_dateto)
        ->with('customer')
        ->withSum('invoicedetail','totalprice')
        ->with('invoicedetail.product')
        ->orderby('date','asc')
        ->get()
        ->toArray();
        
        return Excel::download(new SellerInformationExport($invoices, $params), 'SELLER_INFORMATION_RECORD.xlsx');
    }

    public function dailysalereport(Request $request){
        // return $request->all();
        
        $day = $request->day;
        $date = Carbon::parse($day)->format('Y-m-d');

        // Get product IDs based on the request
        //$products = $request->products != '%' ? Product::pluck('id')->toArray() : $request->products;

        // Get driver IDs based on the request
        $p_driver = explode(',',$request['p_driver']) ?? [];
        if($p_driver[0] == '%'){
            $p_drivers = 'All';
            $p_driver = Driver::pluck('id')->toArray();
        }else{
            $p_drivers = implode(', ',Driver::whereIn('id',$p_driver)->pluck('name')->toArray());
        }
        
        //$lorrys = $request->lorrys != '%' ? Lorry::pluck('id')->toArray() : $request->lorrys;

        // Get customer IDs based on the customer group
       /* $p_customer = $request->customergroup == '%'
            ? Customer::pluck('id')->toArray() 
            : Customer::whereRaw("FIND_IN_SET(?, `group`)", [$request->customergroup])->pluck('id')->toArray();
            
        $payment_term = $request->payment_term == null ? null : ($request->payment_term == '%' ? [1, 2] : [$request->payment_term]);*/

        // Filter invoices based on the selected criteria
        $invoices = Invoice::whereIn('driver_id', $p_driver)
                ->whereDate('date', $date)
                ->whereHas('invoicedetail')
                ->whereIn('customer_id', $p_customer)
                ->with('customer', 'driver', 'invoicedetail.product');
            
        
        $invoices = $invoices->pluck('id');
        
        $invoiceDetails = InvoiceDetail::whereIn('invoice_id', $invoices)->orderBy('invoice_id', 'desc')->get();
        
        return Excel::download(new DailySaleReportExport($invoiceDetails,$p_driver,$date), 'DailySaleReport.xlsx');    
    }
    public function paymentoneview($id)
    {
        $id = Crypt::decrypt($id);
        if($id == 0){
            abort(404);
        }

        $paymentdetail = paymentdetail::where('id',$id)->with('driver:id,name,employeeid,grouping,ic,phone,bankdetails1,bankdetails2')->get();
        // dd($paymentdetail[0]->id);
        if (empty($paymentdetail)) {
            abort(404);
        }

        try{
            $result_do = DB::select('select * from reportlists where id='.$paymentdetail[0]->do_report)[0];
            $result_claim = DB::select('select * from reportlists where id='.$paymentdetail[0]->claim_report)[0];
            $result_compound = DB::select('select * from reportlists where id='.$paymentdetail[0]->comp_report)[0];
            $result_advance = DB::select('select * from reportlists where id='.$paymentdetail[0]->adv_report)[0];
            $result_loan = DB::select('select * from reportlists where id='.$paymentdetail[0]->loanpay_report)[0];
            $result_bonus = DB::select('select * from reportlists where id='.$paymentdetail[0]->bonus_report)[0];
            $paymentdetail_datefrom = $paymentdetail[0]->datefrom;
            $pdo_id = DB::select('select * from paymentdetails where datefrom <= \''.date_format(date_create($paymentdetail_datefrom),'Y-m-d').'\' and driver_id='.$paymentdetail[0]->driver_id.' and month=\''.$paymentdetail[0]->month.'\' and id<>'.$paymentdetail[0]->id.' order by dateto desc');
            // $result_pdo = DB::select('select * from reportlists where id='.$paymentdetail->do_report)[0];
            if (!empty($pdo_id)) {
                $result_pdo = DB::select('select * from reportlists where id='.$pdo_id[0]->do_report)[0];
            }else{
                $result_pdo = DB::select('select * from reportlists where id='.$paymentdetail[0]->do_report)[0];
                $result_pdo->data = '[{"COLUMNS": [{"title": "Date"}, {"title": "D/O No"}, {"title": "Destination"}, {"title": "Source"}, {"title": "Vendor"}, {"title": "Product"}, {"title": "Tonnage Delivered"}, {"title": "Billing Rate"}, {"title": "Delivered Product Price"}, {"title": "Loading/Unloading"}, {"title": "Tol"}, {"title": "Earn Before Comm."}, {"title": "Employee ID"}, {"title": "Driver Name"}, {"title": "Lorry Number"}, {"title": "Commission Weightage"}, {"title": "Commission Rate"}, {"title": "Amt for Commission"}, {"title": "Commission Percentage"}, {"title": "Driver\"s Comm."}, {"title": "Earn After Comm."}], "DATA": []}]';
            }
            return view('reports.paymentoneview')
            ->with('paymentdetail', $paymentdetail[0])
            ->with('result_do', $result_do)
            ->with('result_claim', $result_claim)
            ->with('result_compound', $result_compound)
            ->with('result_advance', $result_advance)
            ->with('result_loan', $result_loan)
            ->with('result_bonus', $result_bonus)
            ->with('result_pdo', $result_pdo);
        }
        catch(Exception $e){
            abort(404);
        }
    }

    // public function massDownloadPaymentoneviewPDF(Request $request)
    public function massDownloadPaymentoneviewPDF($ids)
    {
        $key = $_SERVER['REQUEST_TIME'] . $_SERVER['REMOTE_PORT'];
        $zip = new ZipArchive;
        $fileName = $key.'.zip';
        if(file_exists($fileName)){
            unlink($fileName);
        }

        if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
        {

            // $data = $request->all();
            // $ids = explode(",",$data['ids']);
            // $ids = $data['ids'];
            // $ids = [894];
            $ids = explode(",",$ids);
            foreach($ids as $id){
                $value = $this->getPaymentoneviewPDF(Crypt::encrypt($id),$key);
                // dd($value);
                $relativeNameInZipFile = basename($value);
                $zip->addFile($value, $relativeNameInZipFile);
            }

            $zip->close();
        }
        if(Storage::disk('public')->has($key)){
            Storage::disk('public')->deleteDirectory($key);
        }
    	// Download the generated zip
        return response()->download(public_path($fileName));
    }

    public function getPaymentoneviewPDF($id,$function){
        $id = Crypt::decrypt($id);
        if($id == 0){
            dd('0');
        }

        $paymentdetail = paymentdetail::where('id',$id)->with('driver:id,name,employeeid,grouping,ic,phone,bankdetails1,bankdetails2')->get();
        if (empty($paymentdetail)) {
            dd('Empty');
        }

        $result_do = DB::select('select * from reportlists where id='.$paymentdetail[0]->do_report)[0];
        $result_claim = DB::select('select * from reportlists where id='.$paymentdetail[0]->claim_report)[0];
        $result_compound = DB::select('select * from reportlists where id='.$paymentdetail[0]->comp_report)[0];
        $result_advance = DB::select('select * from reportlists where id='.$paymentdetail[0]->adv_report)[0];
        $result_loan = DB::select('select * from reportlists where id='.$paymentdetail[0]->loanpay_report)[0];
        $result_bonus = DB::select('select * from reportlists where id='.$paymentdetail[0]->bonus_report)[0];
        $paymentdetail_datefrom = $paymentdetail[0]->datefrom;
        $pdo_id = DB::select('select * from paymentdetails where datefrom <= \''.date_format(date_create($paymentdetail_datefrom),'Y-m-d').'\' and driver_id='.$paymentdetail[0]->driver_id.' and month=\''.$paymentdetail[0]->month.'\' and id<>'.$paymentdetail[0]->id.' order by dateto desc');
        if (!empty($pdo_id)) {
            $result_pdo = DB::select('select * from reportlists where id='.$pdo_id[0]->do_report)[0];
        }else{
            $result_pdo = DB::select('select * from reportlists where id='.$paymentdetail[0]->do_report)[0];
            $result_pdo->data = '[{"COLUMNS": [{"title": "Date"}, {"title": "D/O No"}, {"title": "Destination"}, {"title": "Source"}, {"title": "Vendor"}, {"title": "Product"}, {"title": "Tonnage Delivered"}, {"title": "Billing Rate"}, {"title": "Delivered Product Price"}, {"title": "Loading/Unloading"}, {"title": "Tol"}, {"title": "Earn Before Comm."}, {"title": "Employee ID"}, {"title": "Driver Name"}, {"title": "Lorry Number"}, {"title": "Commission Weightage"}, {"title": "Commission Rate"}, {"title": "Amt for Commission"}, {"title": "Commission Percentage"}, {"title": "Driver\"s Comm."}, {"title": "Earn After Comm."}], "DATA": []}]';
        }
        $pdf = Pdf::loadView('reports.paymentoneviewtemplate', array(
            'paymentdetail'=> $paymentdetail[0],
            'result_do'=> $result_do,
            'result_claim'=> $result_claim,
            'result_compound'=> $result_compound,
            'result_advance'=> $result_advance,
            'result_loan'=> $result_loan,
            'result_bonus'=> $result_bonus,
            'result_pdo'=> $result_pdo
        ));

        if($function == 'download'){
            return $pdf->setPaper('a4', 'landscape')->setOptions(['isPhpEnabled' => true])->download($paymentdetail[0]->driver->name.'_'.$paymentdetail[0]->datefrom.'_'.$paymentdetail[0]->dateto.'.pdf');
        }elseif($function == 'view'){
            return $pdf->setPaper('a4', 'landscape')->setOptions(['isPhpEnabled' => true])->stream($paymentdetail[0]->driver->name.'_'.$paymentdetail[0]->datefrom.'_'.$paymentdetail[0]->dateto.'.pdf');
        }else{
            if(!Storage::disk('public')->has($function)){
                Storage::disk('public')->makeDirectory($function);
            }
            $path = storage_path('app/public').'/'.$function.'/'.$paymentdetail[0]->driver->name.'_'.$paymentdetail[0]->datefrom.'_'.$paymentdetail[0]->dateto.'.pdf';
            $filename = $paymentdetail[0]->driver->name.'_'.$paymentdetail[0]->datefrom.'_'.$paymentdetail[0]->dateto.'.pdf';
            Storage::disk('public')->put($function.'/'.$filename,$pdf->setPaper('a4', 'landscape')->output());
            return $path;
        }

    }

    public function vendoroneview($id,$datefrom,$dateto)
    {
        $id = Crypt::decrypt($id);
        if($id == 0){
            abort(404);
        }

        $vendor = Vendor::find($id);
        $vendorbillings = DB::select('call spGetVendorBilling(\''.$id.'\',\''.$datefrom.'\',\''.$dateto.'\');');
        // dd($vendorbillings);

        try{
            return view('reports.vendoroneview')
            ->with('id', $id)
            ->with('datefrom', $datefrom)
            ->with('dateto', $dateto)
            ->with('vendor', $vendor)
            ->with('vendorbillings', $vendorbillings);
        }
        catch(Exception $e){
            abort(404);
        }
    }

    public function getVendoroneviewPDF($id,$datefrom,$dateto,$function)
    {
        $id = Crypt::decrypt($id);
        if($id == 0){
            abort(404);
        }

        try{
            $vendor = Vendor::find($id);
            $vendorbillings = DB::select('call spGetVendorBilling(\''.$id.'\',\''.$datefrom.'\',\''.$dateto.'\');');

            $pdf = Pdf::loadView('reports.vendoroneviewtemplate', array(
                'datefrom'=> $datefrom,
                'dateto'=> $dateto,
                'vendor'=> $vendor,
                'vendorbillings'=> $vendorbillings
            ));

            if($function == 'download'){
                return $pdf->setPaper('a4', 'landscape')->setOptions(['isPhpEnabled' => true])->download($vendor->name.'_'.$datefrom.'_'.$dateto.'.pdf');
            }elseif($function == 'view'){
                return $pdf->setPaper('a4', 'landscape')->setOptions(['isPhpEnabled' => true])->stream($vendor->name.'_'.$datefrom.'_'.$dateto.'.pdf');
            }
        }
        catch(Exception $e){
            abort(404);
        }

    }


}
