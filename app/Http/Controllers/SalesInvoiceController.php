<?php

namespace App\Http\Controllers;

use App\DataTables\SalesInvoiceDataTable;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\SalesInvoiceDetails;
use App\Models\InvoicePayment;
use App\Models\Trip;
use App\Models\Product;
use App\Models\User;
use App\Models\Driver;
use App\Models\Task;
use App\Models\Code;
use App\Models\SpecialPrice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Repositories\SalesInvoiceRepository;

class SalesInvoiceController extends AppBaseController
{
    /** @var SalesInvoiceRepository $salesInvoiceRepository*/
    private $salesInvoiceRepository;

    public function __construct(SalesInvoiceRepository $salesInvoiceRepo)
    {
        $this->salesInvoiceRepository = $salesInvoiceRepo;
    }

    /**
     * Display a listing of the SalesInvoice.
     *
     * @param SalesInvoiceDataTable $salesInvoiceDataTable
     *
     * @return Response
     */
    public function index(Request $request, SalesInvoiceDataTable $salesInvoiceDataTable)
    {
        $this->syncDetailToXero($request);

        return $salesInvoiceDataTable->render('sales_invoices.index');
    }

    /**
     * Show the form for creating a new SalesInvoice.
     *
     * @return Response
     */
    public function getCustomerProductPrices($customerId)
    {
        try {
            $customer = Customer::find($customerId);
            
            if (empty($customer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            
            // Get all products with their default prices
            $products = Product::select('id', 'name', 'code', 'price')->get();
            
            // Get special prices for this customer
            $specialPrices = SpecialPrice::where('customer_id', $customerId)
                ->where('status', 1)
                ->pluck('price', 'product_id')
                ->toArray();
            
            // Combine: use special price if available, otherwise default price
            $productPrices = [];
            foreach ($products as $product) {
                $productPrices[$product->id] = $specialPrices[$product->id] ?? $product->price;
            }
            
            return response()->json([
                'success' => true,
                'product_prices' => $productPrices
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        $customers = Customer::select('id', 'company', 'paymentterm')->orderBy('company')->get();
        $customerItems = $customers->pluck('company', 'id');
        
        // Pass customer payment terms as JSON for JavaScript
        $customerPaymentTerms = $customers->mapWithKeys(function($customer) {
            return [$customer->id => $customer->paymentterm];
        })->toJson();
        
        $drivers = Driver::select('id', 'name')->orderBy('name')->get();
        $driverItems = $drivers->pluck('name', 'id');
        
        // Get products for the details section
        $products = Product::select('id', 'name', 'code')->orderBy('name')->get();
        $productItems = $products->mapWithKeys(function($product) {
            return [$product->id => $product->name . ' (' . $product->code . ')'];
        });

        $specialPrice = SpecialPrice::all();
        
        $productPrices = Product::pluck('price', 'id')->toArray();

        // Generate next invoice number
        $nextInvoiceNumber = SalesInvoice::getNextInvoiceNumber();
        return view('sales_invoices.create', compact(
            'customerItems', 
            'driverItems', 
            'customerPaymentTerms', 
            'productItems',
            'nextInvoiceNumber',
            'productPrices',
        ));
    }
    /**
     * Store a newly created SalesInvoice in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // Validate using model rules with details
        $validator = Validator::make($request->all(), SalesInvoice::$rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction(); // Start transaction

        try {
            $input = $request->all();

            $input['date'] = date_create($input['date']);
            
            // Handle invoice number generation
            if(empty($input['invoiceno']) || $input['invoiceno'] == 'SYSTEM GENERATED IF BLANK') {
                // Generate new invoice number
                $input['invoiceno'] = SalesInvoice::generateInvoiceNumber();
            } else {
                // Check if the provided invoice number already exists
                if(SalesInvoice::invoiceNumberExists($input['invoiceno'])) {
                    // If exists, generate a new one with incremented number
                    $input['invoiceno'] = SalesInvoice::generateInvoiceNumber();
                }
            }
            
            // Set creator information (handled by model boot method)
            // Model boot will automatically set created_by and is_driver
            
            // Set default status if not provided
            if (!isset($input['status'])) {
                $input['status'] = SalesInvoice::STATUS_PENDING;
            }

            // Create sales invoice
            $salesInvoice = $this->salesInvoiceRepository->create($input);

            // Create sales invoice details
            if (isset($input['details']) && is_array($input['details'])) {
                foreach ($input['details'] as $detail) {
                    $salesInvoiceDetail = new SalesInvoiceDetails();
                    $salesInvoiceDetail->sales_invoice_id = $salesInvoice->id;
                    $salesInvoiceDetail->product_id = $detail['product_id'];
                    $salesInvoiceDetail->quantity = $detail['quantity'];
                    $salesInvoiceDetail->price = $detail['price'];
                    $salesInvoiceDetail->totalprice = $detail['quantity'] * $detail['price'];
                    $salesInvoiceDetail->save();
                }
            }            

            DB::commit(); // Commit transaction if everything is successful

            Flash::success('Sales invoice saved successfully.');

            if($input['method'] == 1){
                return redirect(route('salesInvoices.index'));
            }else{
                return redirect(route('salesInvoices.show',encrypt($salesInvoice->id)));
            }

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            
            Flash::error('Error saving sales invoice: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified SalesInvoice.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $salesInvoice = $this->salesInvoiceRepository->find($id);

        if (empty($salesInvoice)) {
            Flash::error('Sales invoice not found');

            return redirect(route('salesInvoices.index'));
        }

        $salesInvoiceDetails = SalesInvoiceDetails::with('product')->where('sales_invoice_id',$id)->get()->toArray();

        return view('sales_invoices.show')->with('salesInvoice', $salesInvoice)->with('salesInvoiceDetails', $salesInvoiceDetails)->with('id',$id);
    }

    /**
     * Show the form for editing the specified SalesInvoice.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $salesInvoice = $this->salesInvoiceRepository->find($id);

        if (empty($salesInvoice)) {
            Flash::error('Sales invoice not found');
            return redirect(route('salesInvoices.index'));
        }

        $customers = Customer::select('id', 'company', 'paymentterm')->orderBy('company')->get();
        $customerItems = $customers->pluck('company', 'id');
        
        // Pass customer payment terms as JSON for JavaScript
        $customerPaymentTerms = $customers->mapWithKeys(function($customer) {
            return [$customer->id => $customer->paymentterm];
        })->toJson();
        
        $drivers = Driver::select('id', 'name')->orderBy('name')->get();
        $driverItems = $drivers->pluck('name', 'id');
        
        // Get products for the details section
        $products = Product::select('id', 'name', 'code')->orderBy('name')->get();
        $productItems = $products->mapWithKeys(function($product) {
            return [$product->id => $product->name . ' (' . $product->code . ')'];
        });
        
        // Get sales invoice details
        $salesInvoiceDetails = SalesInvoiceDetails::with('product')
            ->where('sales_invoice_id', $id)
            ->get()
            ->toArray();

        $productPrices = Product::pluck('price', 'id')->toArray();

        return view('sales_invoices.edit', compact(
            'salesInvoice', 
            'customerItems', 
            'driverItems', 
            'customerPaymentTerms', 
            'productItems',
            'salesInvoiceDetails',
            'productPrices'
        ))->with('isEdit', true);

    }

    /**
     * Update the specified SalesInvoice in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $salesInvoice = $this->salesInvoiceRepository->find($id);

        if (empty($salesInvoice)) {
            Flash::error('Sales invoice not found');
            return redirect(route('salesInvoices.index'));
        }

        // Validate using model rules with details
        $validator = Validator::make($request->all(), SalesInvoice::$Updaterules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction(); // Start transaction

        try {
            $old_payment = $salesInvoice['paymentterm'];
            
            $input = $request->all();

            $input['date'] = date_create($input['date']);
            if($input['invoiceno'] == null){
                Code::where('code','invoicerunningnumber')->first()->increment('value');
                $input['invoiceno'] = 'SINV'.sprintf('%07d',Code::where('code','invoicerunningnumber')->first()->value);
            }

            $salesInvoice = $this->salesInvoiceRepository->update($input, $id);

            // Delete existing details
            SalesInvoiceDetails::where('sales_invoice_id', $id)->delete();

            // Create new details
            if (isset($input['details']) && is_array($input['details'])) {
                foreach ($input['details'] as $detail) {
                    $salesInvoiceDetail = new SalesInvoiceDetails();
                    $salesInvoiceDetail->sales_invoice_id = $id;
                    $salesInvoiceDetail->product_id = $detail['product_id'];
                    $salesInvoiceDetail->quantity = $detail['quantity'];
                    $salesInvoiceDetail->price = $detail['price'];
                    $salesInvoiceDetail->totalprice = $detail['quantity'] * $detail['price'];
                    $salesInvoiceDetail->save();
                }
            }

            // ... rest of your existing update logic ...

            DB::commit(); // Commit transaction

            Flash::success('Sales invoice updated successfully.');

            if($input['method'] == 1){
                return redirect(route('salesInvoices.index'));
            }else{
                return redirect(route('salesInvoices.show',encrypt($salesInvoice->id)));
            }

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            
            Flash::error('Error updating sales invoice: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified SalesInvoice from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $salesInvoice = $this->salesInvoiceRepository->find($id);

        if (empty($salesInvoice)) {
            Flash::error('Sales invoice not found');

            return redirect(route('salesInvoices.index'));
        }

        $task = Task::where('sales_invoice_id',$salesInvoice->id)->first();
        if(!empty($task)){
            if ($task->status != 0) {
                Flash::error('Sales invoice had been processed by driver');

                return redirect(route('salesInvoices.index'));
            }

            $task->delete();
        }

        $this->salesInvoiceRepository->delete($id);
        $salesInvoiceDetails = SalesInvoiceDetails::where('sales_invoice_id',$salesInvoice->id)->delete();

        Flash::success('Sales invoice deleted successfully.');

        return redirect(route('salesInvoices.index'));
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];

        $count = 0;

        foreach ($ids as $id) {

            $salesInvoice = $this->salesInvoiceRepository->find($id);

            $task = Task::where('sales_invoice_id',$salesInvoice->id)->first();
            if(!empty($task)){
                if ($task->status != 0) {
                    continue;
                }

                $task->delete();
            }
            $salesInvoiceDetails = SalesInvoiceDetails::where('sales_invoice_id',$salesInvoice->id)->delete();

            $count = $count + SalesInvoice::destroy($id);
        }

        return $count;
    }

    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];

        $count = SalesInvoice::whereIn('id',$ids)->update(['status'=>$status]);

        return $count;
    }

    public function getcustomer($id)
    {
        $customer = Customer::where('id',$id)->first();

        if (empty($customer)) {
            return response()->json(['status' => false, 'message' => 'Customer not found!']);
        }

        return response()->json(['status' => true, 'message' => 'Customer found!', 'data' => $customer]);
    }

    public function detail(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $salesInvoice = $this->salesInvoiceRepository->find($id);

        if (empty($salesInvoice)) {
            Flash::error('Sales invoice not found');

            return redirect(route('salesInvoices.index'));
        }

        return view('sales_invoices.detail')->with('id', $id);
    }

    public function adddetail($id , Request $request)
    {
        $id = Crypt::decrypt($id);

        $input = $request->all();

        $driver = $this->salesInvoiceRepository->find($id);

        if (empty($driver)) {
            Flash::error('Sales invoice not found');

            return redirect(route('salesInvoices.index'));
        }
        
        $input['sales_invoice_id'] = $id;
        Session::put('sales_invoice_detail_data', $input);

        $res = $this->syncDetailToXero($request);
        if ($res != null) {
            return $res;
        }
        
        return redirect(route('salesInvoices.show',encrypt($id)));
    }
    
    private function syncDetailToXero(Request $request) {
        $input = Session::get('sales_invoice_detail_data');
        if ($input == null) {
            return null;
        }
        
        $salesInvoice = SalesInvoice::where('id', $input['sales_invoice_id'])->first();

        // DB::beginTransaction();
        
        $salesInvoiceDetail = new SalesInvoiceDetails();
        $salesInvoiceDetail->sales_invoice_id  = $input['sales_invoice_id'];
        $salesInvoiceDetail->product_id = $input['product_id'];
        $salesInvoiceDetail->quantity = $input['quantity'];
        $salesInvoiceDetail->price = $input['price'];
        $salesInvoiceDetail->totalprice = $input['quantity'] * $input['price'];
        $salesInvoiceDetail->save();
        

        if($salesInvoice->paymentterm == "Cash") // Changed from "1" to "Cash"
        {
            // Check sales invoice payment if cash 
            $id = $input['sales_invoice_id'];
            $invoicePayment = InvoicePayment::where('sales_invoice_id', $id)->first();
            
            $totalAmount = SalesInvoiceDetails::where('sales_invoice_id', $id)->sum('totalprice');

            if(!$invoicePayment)
            {
                $invoicepayment_new = New InvoicePayment();
                $invoicepayment_new->sales_invoice_id = $id;
                $invoicepayment_new->type = 1;
                $invoicepayment_new->customer_id = $salesInvoice->customer_id;
                $invoicepayment_new->amount = $totalAmount;
                $invoicepayment_new->status = $salesInvoice->status;
                $invoicepayment_new->approve_by = Auth::user()->email;
                $invoicepayment_new->approve_at = date('Y-m-d H:i:s');
                $invoicepayment_new->save();
            }
            else
            {
                $invoicePayment->status = 1;
                $invoicePayment->amount = $totalAmount;
                $invoicePayment->save();
            }
        }

        Flash::success('Sales Invoice Detail saved successfully.');
        
        Session::forget('sales_invoice_detail_data');
    }

    public function deletedetail($id)
    {
        $id = Crypt::decrypt($id);

        $salesInvoiceDetail = SalesInvoiceDetails::where('id',$id)->first();

        if (empty($salesInvoiceDetail)) {
            Flash::error('Sales Invoice Detail not found');

            return redirect()->back();
        }

        $salesInvoiceDetail->delete($id);

        Flash::success('Sales Invoice Detail deleted successfully.');

        return redirect(route('salesInvoices.show',encrypt($salesInvoiceDetail->sales_invoice_id)));
    }

    public function print()
    {
        return view('sales_invoices.print');
    }

    public function syncXero(Request $req)
    {
        try {
            $redirect_uri = config('app.url') . '/salesInvoices/sync-xero';
            $xero = new XeroController($redirect_uri);

            if ($req->has('ids')) {
                $ids = explode(',', $req->ids);
                Session::put('ids_to_sync_xero', $ids);
            }
            // Get Xero's access token
            if ($req->has('code')) {
                $res = $xero->getToken($req->code);
                if (!$res->ok()) {
                    throw new Exception('Failed to get xero access token.');
                }
            }
            // Xero auth
            $res = $xero->auth();
            if ($res !== true) {
                return $res;
            }
            // Sync customers
            $ids = Session::get('ids_to_sync_xero');
            $records = SalesInvoice::whereIn('id', $ids)->get();
            
            $sales_invoice_data = [];
            for ($i = 0; $i < count($records); $i++) {
                if (!isset($sales_invoice_data[$records[$i]['invoiceno']])) {
                    $sales_invoice_data[$records[$i]['invoiceno']] = [
                        'invoice_date' => $records[$i]['date'],
                        'contact_name' => Customer::where('id', $records[$i]->customer_id)->value('company'),
                        'line_items' => [],
                        'currency_code' => strtoupper('myr'),
                    ];
                }
                // Prepare line items
                $sales_invoice_details = SalesInvoiceDetails::where('sales_invoice_id', $records[$i]->id)->get();
                
                for ($j = 0; $j < count($sales_invoice_details); $j++) {
                    $sales_invoice_data[$records[$i]['invoiceno']]['line_items'][] = [
                        'ItemCode' => Product::where('id', $sales_invoice_details[$j]->product_id)->value('code'),
                        'Quantity' => $sales_invoice_details[$j]->quantity,
                        'UnitAmount' => $sales_invoice_details[$j]->price,
                    ];   
                }
            }
            // Insert into Xero
            foreach ($sales_invoice_data as $sku => $data) {
                $res = $xero->getContact($data['contact_name']); // Get contact
                $payload = $res->object();

                if (!$res->ok()) {
                    throw new Exception('Failed to get xero contact.');
                } elseif ($res->ok() && isset($payload->Contacts) && count($payload->Contacts) <= 0) { // Create contact in Xero
                    $res = $xero->createContact($data['contact_name']);
                    if (!$res->ok()) {
                        throw new Exception('Failed to create contact for ' . $data['contact_name']);
                    }
                    $payload = $res->object();
                }

                // Create invoice
                $res = $xero->createInvoice($payload->Contacts[0], $data);
                if (!$res->ok()) {
                    if ($res->object()->ErrorNumber == 10) { // Sales invoice alrdy exists
                        SalesInvoice::where('invoiceno', $sku)->update([
                            'xero_status' => SalesInvoice::STATUS_VOIDED
                        ]);
                        throw new Exception('The sales invoice is voided. No sync is allowed.', 1);
                    }
                    throw new Exception('Failed to create xero sales invoice.');
                }
                // Update status
                SalesInvoice::where('invoiceno', $sku)->update([
                    'xero_status' => SalesInvoice::STATUS_SYNCED_TO_XERO
                ]);
            }
            
            Flash::success('Sales invoices synced to Xero.');
            return redirect(route('salesInvoices.index'));
        } catch (\Throwable $th) {
            report($th);

            Flash::error('Something went wrong. Please contact administator.');
            return redirect(route('salesInvoices.index'));
        }
    }

    public function getSalesInvoiceViewPDF($id,$function)
    {
        $id = Crypt::decrypt($id);
        $salesInvoice = SalesInvoice::where('id',$id)
        ->with(['customer', 'salesInvoiceDetails.product', 'createdByUser', 'createdByDriver'])
        ->first();
        
        $min = 450;
        $each = 23;
        $height = (count($salesInvoice['salesInvoiceDetails']) * $each) + $min;

        $creator = $salesInvoice->creator; // Returns User or Driver model
                        
        try{
            $pdf = Pdf::loadView('sales_invoices.print', array(
                'salesInvoice' => $salesInvoice,
                'creatorName' => $creator->name
            ));

            if($function == 'download'){
                return $pdf->setPaper(array(0, 0, 300, $height), 'portrait')->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true])->download('download.pdf');
            }elseif($function == 'view'){
                return $pdf->setPaper(array(0, 0, 300, $height), 'portrait')->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true])->stream('view.pdf');
            }
        }
        catch(Exception $e){
            dd($e->getMessage());
            abort(404);
        }

    }
    
    /** 
     * Cancel a sales invoice
     *
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function cancel($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $salesInvoice = $this->salesInvoiceRepository->find($id);

        if (empty($salesInvoice)) {
            Flash::error('Sales invoice not found');
            return redirect(route('salesInvoices.index'));
        }

        $reason = $request->get('reason', 'Cancelled by admin');
        
        if ($salesInvoice->cancel($reason)) {
            Flash::success('Sales invoice cancelled successfully.');
        } else {
            Flash::error('Cannot cancel this sales invoice. It may already be converted or cancelled.');
        }

        return redirect(route('salesInvoices.show', encrypt($id)));
    }

    /**
     * Convert sales invoice to invoice
     *
     * @param int $id
     * @return Response
     */
    public function convertToInvoice(Request $request)
    {
        try {
            $id = Crypt::decrypt($request->input('id'));
            $salesInvoice = $this->salesInvoiceRepository->find($id);
            
            if (empty($salesInvoice)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales invoice not found'
                ], 404);
            }
            
            // Check if can be converted
            if (!$salesInvoice->canBeConvertedToInvoice()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot convert this sales invoice'
                ], 400);
            }
            
            // Check payment term - if Cash, use convertWithPayment instead
            if ($salesInvoice->paymentterm == 'Cash') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cash payments require payment proof. Please use "Convert with Payment" button.'
                ], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // Convert to invoice (this only creates the invoice, not payment)
                $invoice = $salesInvoice->convertToInvoice();
                
                if (!$invoice) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to convert sales invoice'
                    ], 400);
                }
                
                // // For non-cash payments, create a PENDING invoice payment record
                // // Only create for Credit (status 0), others don't need payment record
                // if ($salesInvoice->paymentterm == 'Credit') {
                //     $invoicePayment = new InvoicePayment();
                //     $invoicePayment->invoice_id = $invoice->id;
                //     $invoicePayment->customer_id = $salesInvoice->customer_id;
                //     $invoicePayment->amount = $salesInvoice->total ?? 0;
                //     $invoicePayment->status = 0; // Pending for credit
                //     $invoicePayment->user_id = Auth::id();
                //     $invoicePayment->approve_by = Auth::user()->name ?? 'System';
                //     $invoicePayment->approve_at = null; // Not approved yet for credit
                //     $invoicePayment->remark = 'Auto-created on conversion from sales invoice';
                //     $invoicePayment->save();
                // }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sales invoice converted successfully. Invoice No: ' . $invoice->invoiceno,
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoiceno
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert sales invoice to invoice WITH PAYMENT PROOF (for CASH payments only)
     */
    public function convertWithPayment(Request $request)
    {
        try {
            $request->validate([
                'sales_invoice_id' => 'required',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,gif|max:5120',
                'amount' => 'required|numeric|min:0',
                'remark' => 'nullable|string'
            ]);
            
            $id = Crypt::decrypt($request->sales_invoice_id);
            $salesInvoice = $this->salesInvoiceRepository->find($id);
            
            if (empty($salesInvoice)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales invoice not found'
                ], 404);
            }
            
            // Check if can be converted
            if (!$salesInvoice->canBeConvertedToInvoice()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot convert this sales invoice'
                ], 400);
            }
            
            // Verify it's CASH payment
            if ($salesInvoice->paymentterm != 'Cash') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment proof is only required for CASH payments'
                ], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // Convert to invoice (creates the invoice only)
                $invoice = $salesInvoice->convertToInvoice();
                
                if (!$invoice) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to convert sales invoice'
                    ], 400);
                }
                
                // Handle attachment upload
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $attachmentPath = $file->storeAs('invoice_payments', $fileName, 'public');
                }
                
                // Create APPROVED invoice payment record for cash
                $invoicePayment = new InvoicePayment();
                $invoicePayment->invoice_id = $invoice->id;
                $invoicePayment->type = $invoice->paymentterm ;
                $invoicePayment->customer_id = $salesInvoice->customer_id;
                $invoicePayment->amount = $request->amount;
                $invoicePayment->status = 1; // Approved for cash payment
                $invoicePayment->attachment = $attachmentPath;
                
                // Set user/driver information
                if (Auth::guard('web')->check()) {
                    $invoicePayment->user_id = Auth::id();
                    $invoicePayment->driver_id = null;
                } elseif (Auth::guard('driver')->check()) {
                    $invoicePayment->driver_id = Auth::id();
                    $invoicePayment->user_id = null;
                }
                
                $invoicePayment->approve_by = Auth::user()->name ?? 'System';
                $invoicePayment->approve_at = now(); // Approved immediately for cash
                $invoicePayment->remark = $request->remark ?? 'Cash payment with proof';
                $invoicePayment->save();
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sales invoice converted successfully with payment proof',
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoiceno
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}