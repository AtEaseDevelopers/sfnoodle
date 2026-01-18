<?php

namespace App\Http\Controllers;

use App\DataTables\InvoiceDataTable;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\InvoiceDetail;
use App\Models\InvoicePayment;
use App\Models\Trip;
use App\Models\Product;
use App\Models\Task;
use App\Models\Code;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Repositories\InvoiceRepository;
use App\Models\Driver;

class InvoiceController extends AppBaseController
{
    /** @var InvoiceRepository $invoiceRepository*/
    private $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepo)
    {
        $this->invoiceRepository = $invoiceRepo;
    }

    /**
     * Display a listing of the Invoice.
     *
     * @param InvoiceDataTable $invoiceDataTable
     *
     * @return Response
     */
    public function index(Request $request, InvoiceDataTable $invoiceDataTable)
    {
        $this->syncDetailToXero($request);

        return $invoiceDataTable->render('invoices.index');
    }

    /**
     * Show the form for creating a new Invoice.
     *
     * @return Response
     */
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

        $productPrices = Product::pluck('price', 'id')->toArray();

        // Generate next invoice number
        $nextInvoiceNumber = Invoice::getNextInvoiceNumber();

        return view('invoices.create', compact(
            'customerItems', 
            'driverItems', 
            'customerPaymentTerms',
            'productItems',
            'nextInvoiceNumber',
            'productPrices'

        ));
    }

    /**
     * Store a newly created Invoice in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all(); 

        $additionalRules = [
            'payment_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'payment_remark' => 'nullable|string|max:255',
        ];
        
        $validator = Validator::make($request->all(), array_merge(Invoice::$rules, $additionalRules));
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction(); // Start transaction

        try {
            $input = $request->all();

            $input['date'] = date_create($input['date']);
            
            // Handle invoice number generation using the new method
            if(empty($input['invoiceno']) || $input['invoiceno'] == 'SYSTEM GENERATED IF BLANK') {
                // Generate new invoice number using the new method
                $input['invoiceno'] = Invoice::generateInvoiceNumber();
            } else {
                // Check if the provided invoice number already exists
                if(Invoice::invoiceNumberExists($input['invoiceno'])) {
                    // If exists, generate a new one with incremented number
                    $input['invoiceno'] = Invoice::generateInvoiceNumber();
                }
            }
            // Set creator information (handled by model boot method)
            // Model boot will automatically set created_by and is_driver
            
            // Set default status if not provided
            if (!isset($input['status'])) {
                $input['status'] = Invoice::STATUS_NEW;
            }

            // Create invoice
            $invoice = $this->invoiceRepository->create($input);

            // Create invoice details
            $totalAmount = 0;
            if (isset($input['details']) && is_array($input['details'])) {
                foreach ($input['details'] as $detail) {
                    $itemTotal = $detail['quantity'] * $detail['price'];
                    $totalAmount += $itemTotal;
                    
                    $invoiceDetail = new InvoiceDetail();
                    $invoiceDetail->invoice_id = $invoice->id;
                    $invoiceDetail->product_id = $detail['product_id'];
                    $invoiceDetail->quantity = $detail['quantity'];
                    $invoiceDetail->price = $detail['price'];
                    $invoiceDetail->totalprice = $itemTotal;
                    $invoiceDetail->remark = $detail['remark'] ?? null;
                    $invoiceDetail->save();
                }
            }

            // Create invoice payment ONLY if status is COMPLETED and payment term is CASH
            if ($input['status'] == Invoice::STATUS_COMPLETED && $input['paymentterm'] == 'Cash') {
                $this->createInvoicePayment($invoice, $input, $totalAmount, $request);
            }
            
            DB::commit(); // Commit transaction if everything is successful

            Flash::success('Invoice created successfully.');
            return redirect(route('invoices.index'));

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            
            Flash::error('Error saving invoice: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    private function createInvoicePayment($invoice, $input, $totalAmount, $request)
    {
        $invoicePayment = new InvoicePayment();
        $invoicePayment->invoice_id = $invoice->id;
        $invoicePayment->customer_id = $invoice->customer_id;
        $invoicePayment->amount = $totalAmount;
        $invoicePayment->status = 1; // Completed status for payment
        $invoicePayment->type = Invoice::PAYMENT_TYPE_CASH ; // always cash for now

        // Handle attachment upload
        if ($request->hasFile('payment_attachment')) {
            $file = $request->file('payment_attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('payment_attachments', $fileName, 'public');
            $invoicePayment->attachment = $filePath;
        }
        
        $invoicePayment->remark = $input['payment_remark'] ?? null;
        $invoicePayment->user_id = Auth::id();
        $invoicePayment->approve_by = Auth::user()->name;
        $invoicePayment->approve_at = date('Y-m-d H:i:s');
        $invoicePayment->save();

        return $invoicePayment;
    }

    /**
     * Display the specified Invoice.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            Flash::error('Invoice not found');

            return redirect(route('invoices.index'));
        }

        $invoicedetails = InvoiceDetail::with('product')->where('invoice_id',$id)->get()->toArray();

        return view('invoices.show')->with('invoice', $invoice)->with('invoicedetails', $invoicedetails)->with('id',$id);
    }

    /**
     * Show the form for editing the specified Invoice.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            Flash::error('Invoice not found');
            return redirect(route('invoices.index'));
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
        
        // Get invoice details
        $invoiceDetails = InvoiceDetail::with('product')
            ->where('invoice_id', $id)
            ->get()
            ->toArray();
            
        $invoicePayment = InvoicePayment::where('invoice_id', $id)->first();

            // Format date for display
        $invoice->date = $invoice->date ? date('d-m-Y', strtotime($invoice->date)) : null;

        return view('invoices.edit', compact(
            'invoice', 
            'customerItems', 
            'driverItems', 
            'customerPaymentTerms', 
            'productItems',
            'invoiceDetails',
            'invoicePayment'
        ))->with('isEdit', true);
    }

    /**
     * Update the specified Invoice in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            Flash::error('Invoice not found');

            return redirect(route('invoices.index'));
        }

        // Validate using model rules
        $validator = Validator::make($request->all(), Invoice::$Updaterules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        $old_payment = $invoice['paymentterm'];
        
        $input = $request->all();

        $input['date'] = date_create($input['date']);
        if($input['invoiceno'] == null){
            Code::where('code','invoicerunningnumber')->first()->increment('value');
            $input['invoiceno'] = 'INV'.sprintf('%07d',Code::where('code','invoicerunningnumber')->first()->value);
        }

        $invoice = $this->invoiceRepository->update($input, $id);

        InvoiceDetail::where('invoice_id', $id)->delete();
        
        if (isset($input['details']) && is_array($input['details'])) {
            foreach ($input['details'] as $detail) {
                $invoiceDetail = new InvoiceDetail();
                $invoiceDetail->invoice_id = $invoice->id;
                $invoiceDetail->product_id = $detail['product_id'];
                $invoiceDetail->quantity = $detail['quantity'];
                $invoiceDetail->price = $detail['price'];
                $invoiceDetail->totalprice = $detail['quantity'] * $detail['price'];
                $invoiceDetail->remark = $detail['remark'] ?? null;
                $invoiceDetail->save();
            }
        }

        Flash::success('Invoice updated successfully.');
        return redirect(route('invoices.index'));
    }

    /**
     * Remove the specified Invoice from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            Flash::error('Invoice not found');

            return redirect(route('invoices.index'));
        }

        $task = Task::where('invoice_id',$invoice->id)->first();
        if(!empty($task)){
            if ($task->status != 0) {
                Flash::error('Invoice had been processed by driver');

                return redirect(route('invoices.index'));
            }

            $task->delete();
        }

        $this->invoiceRepository->delete($id);
        $invoicedetail = Invoicedetail::where('invoice_id',$invoice->id)->delete();

        Flash::success('Invoice deleted successfully.');

        return redirect(route('invoices.index'));
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];

        $count = 0;

        foreach ($ids as $id) {

            $invoice = $this->invoiceRepository->find($id);

        $task = Task::where('invoice_id',$invoice->id)->first();
            if(!empty($task)){
                if ($task->status != 0) {
                    continue;
                }

                $task->delete();
            }
            $invoicedetail = Invoicedetail::where('invoice_id',$invoice->id)->delete();

            $count = $count + Invoice::destroy($id);
        }

        return $count;
    }

    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];

        $count = Invoice::whereIn('id',$ids)->update(['status'=>$status]);

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
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            Flash::error('Invoice not found');

            return redirect(route('invoices.index'));
        }

        return view('invoices.detail')->with('id', $id);
    }

    public function adddetail($id , Request $request)
    {
        $id = Crypt::decrypt($id);

        $input = $request->all();

        $driver = $this->invoiceRepository->find($id);

        if (empty($driver)) {
            Flash::error('Invoice not found');

            return redirect(route('invoices.index'));
        }
        
        $input['invoice_id'] = $id;
        Session::put('invoice_detail_data', $input);

        $res = $this->syncDetailToXero($request);
        if ($res != null) {
            return $res;
        }
        
        return redirect(route('invoices.show',encrypt($id)));
    }
    
    private function syncDetailToXero(Request $request) {
        $input = Session::get('invoice_detail_data');
        if ($input == null) {
            return null;
        }
        
        $invoice = Invoice::where('id', $input['invoice_id'])->first();

        // DB::beginTransaction();
        
        $invoicedetail = new InvoiceDetail();
        $invoicedetail->invoice_id  = $input['invoice_id'];
        $invoicedetail->product_id = $input['product_id'];
        $invoicedetail->quantity = $input['quantity'];
        $invoicedetail->price = $input['price'];
        $invoicedetail->totalprice = $input['quantity'] * $input['price'];
        $invoicedetail->remark = $input['remark'];
        $invoicedetail->save();
        

        if($invoice->paymentterm == "Cash") // Changed from "1" to "Cash"
        {
            // Check invoice payment if cash 
            $id = $input['invoice_id'];
            $invoicePayment = InvoicePayment::where('invoice_id', $id)->first();
            
            $totalAmount = InvoiceDetail::where('invoice_id', $id)->sum('totalprice');

            if(!$invoicePayment)
            {
                $invoicepayment_new = New InvoicePayment();
                $invoicepayment_new->invoice_id = $id;
                $invoicepayment_new->type = 1;
                $invoicepayment_new->customer_id = $invoice->customer_id;
                $invoicepayment_new->amount = $totalAmount;
                $invoicepayment_new->status = $invoice->status;
                $invoicepayment_new->driver_id = $invoice->driver_id;
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

        Flash::success('Invoice Detail saved successfully.');
        
        Session::forget('invoice_detail_data');
    }

    public function deletedetail($id)
    {
        $id = Crypt::decrypt($id);

        $invoicedetail = InvoiceDetail::where('id',$id)->first();

        if (empty($invoicedetail)) {
            Flash::error('Invoice Detail not found');

            return redirect()->back();
        }

        $invoicedetail->delete($id);

        Flash::success('Invoice Detail deleted successfully.');

        return redirect(route('invoices.show',encrypt($invoicedetail->invoice_id)));
    }

    public function print()
    {
        return view('invoices.print');
    }

    public function getInvoiceViewPDF($id,$function)
    {
        $id = Crypt::decrypt($id);
        $invoice = Invoice::where('id',$id)
        ->with(['customer', 'InvoiceDetails.product', 'createdByUser', 'createdByDriver'])
        ->first();
    

        $min = 450;
        $each = 23;
        $height = (count($invoice['invoiceDetails']) * $each) + $min;

        $creator = $invoice->creator; // Returns User or Driver model

        try{
            $pdf = Pdf::loadView('invoices.print', array(
                'invoices' => $invoice,
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

    public function cancelInvoice($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            Flash::error('Invoice not found');
            return redirect(route('invoices.index'));
        }

        // Check if invoice can be cancelled (only completed invoices can be cancelled)
        if ($invoice->status != Invoice::STATUS_COMPLETED) {
            Flash::error('Only completed invoices can be cancelled.');
            return redirect(route('invoices.show', encrypt($id)));
        }
        try {
            // Cancel the invoice
            $invoice->cancel();

            // Store cancellation reason if provided
            $cancellationReason = $request->input('cancellation_reason');
            if ($cancellationReason) {
                $invoice->remark = "\n[Cancelled: " . $cancellationReason . " - " . date('Y-m-d H:i:s') . "] by". Auth::user()->name;
                $invoice->save();
            }
            
            Flash::success('Invoice cancelled successfully.');
            
        } catch (\Exception $e) {
            Flash::error('Error cancelling invoice: ' . $e->getMessage());
        }

        return redirect(route('invoices.index'));
    }

    public function syncXero(Request $req)
    {
        try {
            $redirect_uri = config('app.url') . '/invoices/sync-xero';
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
            $records = Invoice::whereIn('id', $ids)->get();
            
            $invoice_data = [];
            for ($i = 0; $i < count($records); $i++) {
                if (!isset($invoice_data[$records[$i]['invoiceno']])) {
                    $invoice_data[$records[$i]['invoiceno']] = [
                        'invoice_date' => $records[$i]['date'],
                        'contact_name' => Customer::where('id', $records[$i]->customer_id)->value('company'),
                        'line_items' => [],
                        'currency_code' => strtoupper('myr'),
                    ];
                }
                // Prepare line items
                $invoice_details = InvoiceDetail::where('invoice_id', $records[$i]->id)->get();
                
                for ($j = 0; $j < count($invoice_details); $j++) {
                    $invoice_data[$records[$i]['invoiceno']]['line_items'][] = [
                        'ItemCode' => Product::where('id', $invoice_details[$j]->product_id)->value('code'),
                        'Description' => $invoice_details[$j]->remark,
                        'Quantity' => $invoice_details[$j]->quantity,
                        'UnitAmount' => $invoice_details[$j]->price,
                    ];   
                }
            }
            // Insert into Xero
            foreach ($invoice_data as $sku => $data) {
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
                    if ($res->object()->ErrorNumber == 10) { // Invoice alrdy exists
                        Invoice::where('invoiceno', $sku)->update([
                            'xero_status' => Invoice::STATUS_VOIDED
                        ]);
                        throw new Exception('The invoice is voided. No sync is allowed.', 1);
                    }
                    throw new Exception('Failed to create xero invoice.');
                }
                // Update status
                Invoice::where('invoiceno', $sku)->update([
                    'xero_status' => Invoice::STATUS_SYNCED_TO_XERO
                ]);
            }
            
            Flash::success('Invoices synced to Xero.');
            return redirect(route('invoices.index'));
        } catch (\Throwable $th) {
            report($th);

            Flash::error('Something went wrong. Please contact administator.');
            return redirect(route('invoices.index'));
        }
    }
}