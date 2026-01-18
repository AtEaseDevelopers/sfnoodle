<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Repositories\CustomerRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\SalesInvoice;
use App\Models\SpecialPrice;
use App\Models\foc;
use App\Models\Assign;
use Illuminate\Support\Facades\Session;
use Exception;
use Illuminate\Support\Facades\Validator;

class CustomerController extends AppBaseController
{
    /** @var CustomerRepository $customerRepository*/
    private $customerRepository;

    public function __construct(CustomerRepository $customerRepo)
    {
        $this->customerRepository = $customerRepo;
    }

    /**
     * Display a listing of the Customer.
     *
     * @param CustomerDataTable $customerDataTable
     *
     * @return Response
     */
    public function index(CustomerDataTable $customerDataTable)
    {
        return $customerDataTable->render('customers.index');
    }

    /**
     * Show the form for creating a new Customer.
     *
     * @return Response
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created Customer in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // Validate using model rules
        $validator = Validator::make($request->all(), Customer::$rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();
        $customer = $this->customerRepository->create($input);

        Flash::success('Customer saved successfully.');

        return redirect(route('customers.index'));
    }

    /**
     * Display the specified Customer.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $customer = $this->customerRepository->find($id);

        if (empty($customer)) {
            Flash::error('Customer not found.');

            return redirect(route('customers.index'));
        }
        
        return view('customers.show')->with('customer', $customer);
    }

    /**
     * Show the form for editing the specified Customer.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $customer = $this->customerRepository->find($id);

        if (empty($customer)) {
            Flash::error('Customer not found.');

            return redirect(route('customers.index'));
        }
        
        return view('customers.edit')->with('customer', $customer);
    }

    /**
     * Update the specified Customer in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $customer = $this->customerRepository->find($id);

        if (empty($customer)) {
            Flash::error('Customer not found.');

            return redirect(route('customers.index'));
        }
        $rules = [
            'code' => 'required|string|max:255|unique:customers,code,'.$id,
            'company' => 'required|string|max:255|string|max:255',
            'paymentterm' => 'required',
            'phone' => 'nullable|string|max:20|nullable|string|max:20',
            'address' => 'nullable|string|max:65535|nullable|string|max:65535',
            'status' => 'required',
            'created_at' => 'nullable|nullable',
            'updated_at' => 'nullable|nullable'
        ];
        // Validate using model rules
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();
        $customer = $this->customerRepository->update($input, $id);

        Flash::success('Customer updated successfully.');

        return redirect(route('customers.index'));
    }

    /**
     * Remove the specified Customer from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $customer = $this->customerRepository->find($id);

        if (empty($customer)) {
            Flash::error('Customer not found.');

            return redirect(route('customers.index'));
        }

        $salesInvoice = SalesInvoice::where('customer_id',$id)->get()->toArray();
        if(count($salesInvoice) > 0){
            Flash::error('Unable to delete '.$customer->company.', '.$customer->company.' is being used in Sales Invoice');

            return redirect(route('customers.index'));
        }

        $specialPrice = SpecialPrice::where('customer_id',$id)->get()->toArray();
        if(count($specialPrice) > 0){
            Flash::error('Unable to delete '.$customer->company.', '.$customer->company.' is being used in Special Price');

            return redirect(route('customers.index'));
        }

        $foc = foc::where('customer_id',$id)->get()->toArray();
        if(count($foc) > 0){
            Flash::error('Unable to delete '.$customer->company.', '.$customer->company.' is being used in FOC');

            return redirect(route('customers.index'));
        }

        $assign = Assign::where('customer_id',$id)->get()->toArray();
        if(count($assign) > 0){
            Flash::error('Unable to delete '.$customer->company.', '.$customer->company.' is being used in Assign');

            return redirect(route('customers.index'));
        }

        $this->customerRepository->delete($id);

        Flash::success($customer->company . ' deleted successfully.');

        return redirect(route('customers.index'));
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];

        $count = 0;

        foreach ($ids as $id) {

            $salesInvoice = SalesInvoice::where('customer_id',$id)->get()->toArray();
            if(count($salesInvoice) > 0){
                continue;
            }

            $specialPrice = SpecialPrice::where('customer_id',$id)->get()->toArray();
            if(count($specialPrice) > 0){
                continue;
            }

            $foc = foc::where('customer_id',$id)->get()->toArray();
            if(count($foc) > 0){
                continue;
            }

            $assign = Assign::where('customer_id',$id)->get()->toArray();
            if(count($assign) > 0){
                continue;
            }

            $customer = $this->customerRepository->find($id);

            $count = $count + Customer::destroy($id);
        }

        return $count;
    }

    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];

        $count = Customer::whereIn('id',$ids)->update(['status'=>$status]);

        return $count;
    }
    
    public function syncXero(Request $req)
    {
        try {
            $redirect_uri = config('app.url') . '/customers/sync-xero';
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
            $customers = Customer::whereIn('id', $ids)->get();
            for ($i = 0; $i < count($customers) ;$i++) {
                $res = $xero->createContact($customers[$i]->company);

                if (!$res->ok()) {  
                    throw new Exception('Failed to sync customer.');
                }
            }
            
            Flash::success('Customers synced to Xero.');
            return redirect(route('customers.index'));
        } catch (\Throwable $th) {
            report($th);

            Flash::error('Something went wrong. Please contact administrator.');
            return redirect(route('customers.index'));
        }
    }
}