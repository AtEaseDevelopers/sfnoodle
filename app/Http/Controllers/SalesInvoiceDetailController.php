<?php

namespace App\Http\Controllers;

use App\DataTables\SalesInvoiceDetailDataTable;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use App\Models\SalesInvoiceDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\SpecialPrice;
use App\Models\SalesInvoice;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\Session;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Repositories\SalesInvoiceDetailRepository;

class SalesInvoiceDetailController extends AppBaseController
{
    /** @var SalesInvoiceDetailRepository $salesInvoiceDetailRepository*/
    private $salesInvoiceDetailRepository;

    public function __construct(SalesInvoiceDetailRepository $salesInvoiceDetailRepo)
    {
        $this->salesInvoiceDetailRepository = $salesInvoiceDetailRepo;
    }

    /**
     * Display a listing of the SalesInvoiceDetails.
     *
     * @param SalesInvoiceDetailDataTable $salesInvoiceDetailDataTable
     *
     * @return Response
     */
    public function index(Request $req, SalesInvoiceDetailDataTable $salesInvoiceDetailDataTable)
    {
        $this->upsertDetail(Session::get('is_store'), $req);

        return $salesInvoiceDetailDataTable->render('sales_invoice_details.index');
    }

    /**
     * Show the form for creating a new SalesInvoiceDetails.
     *
     * @return Response
     */
    public function create()
    {
        return view('sales_invoice_details.create');
    }

    /**
     * Store a newly created SalesInvoiceDetails in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // Validate using model rules
        $validator = Validator::make($request->all(), SalesInvoiceDetails::$rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();
        Session::put('is_store', true);
        Session::put('sales_invoice_detail_data', $input);

        $res = $this->upsertDetail(true, $request);
        if ($res != null) {
            return $res;
        }
        
        return redirect(route('salesInvoiceDetails.index'));
    }
    
    private function upsertDetail($is_store, Request $req) {
        $input = Session::get('sales_invoice_detail_data');
        if ($input == null) {
            return null;
        }

        $salesInvoice = SalesInvoice::where('id', $input['sales_invoice_id'])->first();

        DB::beginTransaction();

        if ($is_store == true) {
            $input['totalprice'] = $input['quantity'] * $input['price'];
            $salesInvoiceDetail = $this->salesInvoiceDetailRepository->create($input);
        } else {
            $input['totalprice'] = $input['quantity'] * $input['price'];
            $salesInvoiceDetail = $this->salesInvoiceDetailRepository->update($input, $input['edit_id']);
        }

        // Create credit note in Xero if payment term is credit
        $xero_has_err = false;
        if ($salesInvoice != null && $salesInvoice->paymentterm == 2) {
            try {
                $redirect_uri = config('app.url') . '/salesInvoiceDetails';
                $xero = new XeroController($redirect_uri);
    
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
                // Get contact
                $customer_name = Customer::where('id', $salesInvoice->customer_id)->value('company');
                
                $res = $xero->getContact($customer_name); // Get contact
                $payload = $res->object();

                if (!$res->ok()) {
                    throw new Exception('Failed to get xero contact.');
                } elseif ($res->ok() && isset($payload->Contacts) && count($payload->Contacts) <= 0) { // Create contact in Xero
                    $res = $xero->createContact($customer_name);
                    if (!$res->ok()) {
                        throw new Exception('Failed to create contact for ' . $customer_name);
                    }
                    $payload = $res->object();
                }
                // Create credit note
                $items = [
                    'Quantity' => $input['quantity'],
                    'UnitAmount' => $input['price'], 
                    'Description' => $input['remark'] ?? $salesInvoice->invoiceno
                ];
                $res = $xero->createCreditNote(true, $payload->Contacts[0], $items, 'SID' . ($is_store == true ? $salesInvoiceDetail->id : $input['edit_id']));
                if (!$res->ok()) {
                    throw new Exception('Failed to create credit note.');
                }
            } catch (\Throwable $th) {
                DB::rollback();
                report($th);
                
                $xero_has_err = true;
                Flash::error('Something went wrong.');
            }
        }
        
        if (!$xero_has_err) {
            if ($is_store == true) {
                Flash::success('Sales invoice detail saved successfully.');
            } else {
                Flash::success('Sales invoice detail updated successfully.');
            }
            
            DB::commit();
        }
        
        Session::forget('sales_invoice_detail_data');
    }

    /**
     * Display the specified SalesInvoiceDetails.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $salesInvoiceDetail = $this->salesInvoiceDetailRepository->find($id);

        if (empty($salesInvoiceDetail)) {
            Flash::error('Sales invoice detail not found.');

            return redirect(route('salesInvoiceDetails.index'));
        }

        return view('sales_invoice_details.show')->with('salesInvoiceDetail', $salesInvoiceDetail);
    }

    /**
     * Show the form for editing the specified SalesInvoiceDetails.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $salesInvoiceDetail = $this->salesInvoiceDetailRepository->find($id);

        if (empty($salesInvoiceDetail)) {
            Flash::error('Sales invoice detail not found.');

            return redirect(route('salesInvoiceDetails.index'));
        }

        return view('sales_invoice_details.edit')->with('salesInvoiceDetail', $salesInvoiceDetail);
    }

    /**
     * Update the specified SalesInvoiceDetails in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $salesInvoiceDetail = $this->salesInvoiceDetailRepository->find($id);

        if (empty($salesInvoiceDetail)) {
            Flash::error('Sales invoice detail not found.');

            return redirect(route('salesInvoiceDetails.index'));
        }

        // Validate using model rules
        $validator = Validator::make($request->all(), SalesInvoiceDetails::$rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();
        $input['edit_id'] = $id;
        
        Session::put('is_store', false);
        Session::put('sales_invoice_detail_data', $input);

        $res = $this->upsertDetail(false, $request);
        if ($res != null) {
            return $res;
        }

        return redirect(route('salesInvoiceDetails.index'));
    }

    /**
     * Remove the specified SalesInvoiceDetails from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $salesInvoiceDetail = $this->salesInvoiceDetailRepository->find($id);

        if (empty($salesInvoiceDetail)) {
            Flash::error('Sales invoice detail not found.');

            return redirect(route('salesInvoiceDetails.index'));
        }

        $this->salesInvoiceDetailRepository->delete($id);
        Flash::success('Sales invoice detail deleted successfully.');

        return redirect(route('salesInvoiceDetails.index'));
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        
        $count = 0;
    
        foreach ($ids as $id) {
            $salesInvoiceDetail = $this->salesInvoiceDetailRepository->find($id);
            $count = $count + SalesInvoiceDetails::destroy($id);
        }
    
        return $count;
    }
    
    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];
        
        $count = SalesInvoiceDetails::whereIn('id',$ids)->update(['status'=>$status]);
    
        return $count;
    }
    
    public function getprice($sales_invoice_id,$product_id)
    {
        $salesInvoice = SalesInvoice::where('id',$sales_invoice_id)->first();

        if (empty($salesInvoice)) {
            return response()->json(['status' => false, 'message' => 'Sales invoice not found!']);
        }

        $product = Product::where('id',$product_id)->first();

        if (empty($product)) {
            return response()->json(['status' => false, 'message' => 'Product not found!']);
        }

        $specialprice = SpecialPrice::where('customer_id',$salesInvoice->customer_id)->where('product_id',$product_id)->first();

        if (empty($specialprice)) {
            return response()->json(['status' => true, 'message' => 'Special Price not found!', 'data' => $product->price]);
        }else{
            return response()->json(['status' => true, 'message' => 'Special Price found!', 'data' => $specialprice->price]);
        }
    }
}