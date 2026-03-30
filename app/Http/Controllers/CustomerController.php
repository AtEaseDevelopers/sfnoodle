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
use App\Models\CustomerGroup;
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

        if(!empty($input['driver_id'])) {
            // Check if the driver already has a customer group
            $existingAssign = Assign::where('driver_id', $input['driver_id'])->first();
            
            if ($existingAssign) {
                // Driver has an existing group, add this customer to that group
                $customerGroup = CustomerGroup::find($existingAssign->customer_group_id);
                
                // Add customer to the group
                $customerGroup->addCustomerWithSequence($customer->id);
                $customerGroup->save();
            } else {
                // Driver doesn't have a group, create new group and assignment
                $customerGroup = CustomerGroup::create([
                    'name' => $input['company'], 
                    'customer_ids' => [
                        [
                            'id' => $customer->id,
                            'sequence' => 1
                        ]
                    ]
                ]);
                
                // Create assignment
                Assign::create([
                    'driver_id' => $input['driver_id'],
                    'customer_group_id' => $customerGroup->id
                ]);
            }
        }

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
            'company' => 'required|string|max:255',
            'paymentterm' => 'required',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:65535',
            'status' => 'required',
            'created_at' => 'nullable',
            'updated_at' => 'nullable'
        ];
        
        // Validate using model rules
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();
        
        // Store old driver_id before updating customer
        $oldDriverId = $customer->driver_id ?? null;
        
        // Update customer
        $customer = $this->customerRepository->update($input, $id);
        
        // Handle driver assignment
        if(!empty($input['driver_id'])) {
            $newDriverId = $input['driver_id'];
            
            // Check if the driver has changed
            if ($oldDriverId != $newDriverId) {
                // Remove customer from old driver's group if they had one
                if ($oldDriverId) {
                    $oldAssign = Assign::where('driver_id', $oldDriverId)->first();
                    if ($oldAssign) {
                        $oldGroup = CustomerGroup::find($oldAssign->customer_group_id);
                        if ($oldGroup) {
                            // Remove customer from old group
                            $oldGroup->removeCustomer($customer->id);
                            $oldGroup->save();
                            // Note: We DO NOT delete the old group even if empty
                            // Keep it for future use when new customers are assigned to this driver
                        }
                    }
                }
                
                // Add customer to new driver's group
                $newAssign = Assign::where('driver_id', $newDriverId)->first();
                
                if ($newAssign) {
                    // New driver has an existing group, add customer to it
                    $newGroup = CustomerGroup::find($newAssign->customer_group_id);
                    $newGroup->addCustomerWithSequence($customer->id);
                    $newGroup->save();
                } else {
                    // New driver doesn't have a group, create new group and assignment
                    $newGroup = CustomerGroup::create([
                        'name' => $input['company'],
                        'customer_ids' => [
                            [
                                'id' => $customer->id,
                                'sequence' => 1
                            ]
                        ]
                    ]);
                    
                    // Create assignment
                    Assign::create([
                        'driver_id' => $newDriverId,
                        'customer_group_id' => $newGroup->id
                    ]);
                }
            } else {
                // Same driver, just update group name if needed and ensure customer is in the group
                $assign = Assign::where('driver_id', $newDriverId)->first();
                
                if ($assign) {
                    $group = CustomerGroup::find($assign->customer_group_id);
                    
                    // Update group name if needed
                    if ($group->name != $input['company']) {
                        $group->name = $input['company'] ;
                        $group->save();
                    }
                    
                    // Ensure customer is in the group (just in case)
                    $customerIds = $group->customer_ids ?? [];
                    $customerExists = false;
                    foreach ($customerIds as $item) {
                        if ($item['id'] == $customer->id) {
                            $customerExists = true;
                            break;
                        }
                    }
                    
                    if (!$customerExists) {
                        $group->addCustomerWithSequence($customer->id);
                        $group->save();
                    }
                }
            }
        } else {
            // No driver selected - remove customer from any group they're in
            if ($oldDriverId) {
                $oldAssign = Assign::where('driver_id', $oldDriverId)->first();
                if ($oldAssign) {
                    $oldGroup = CustomerGroup::find($oldAssign->customer_group_id);
                    if ($oldGroup) {
                        // Remove customer from old group
                        $oldGroup->removeCustomer($customer->id);
                        $oldGroup->save();
                        // Note: We DO NOT delete the old group even if empty
                        // Keep it for future use
                    }
                }
            }
        }

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

        // Check if customer is used in Sales Invoice
        $salesInvoice = SalesInvoice::where('customer_id', $id)->exists();
        if ($salesInvoice) {
            Flash::error('Unable to delete ' . $customer->company . ', ' . $customer->company . ' is being used in Sales Invoice');
            return redirect(route('customers.index'));
        }

        // Check if customer is used in Special Price
        $specialPrice = SpecialPrice::where('customer_id', $id)->exists();
        if ($specialPrice) {
            Flash::error('Unable to delete ' . $customer->company . ', ' . $customer->company . ' is being used in Special Price');
            return redirect(route('customers.index'));
        }

        // Check if customer is used in FOC
        $foc = foc::where('customer_id', $id)->exists();
        if ($foc) {
            Flash::error('Unable to delete ' . $customer->company . ', ' . $customer->company . ' is being used in FOC');
            return redirect(route('customers.index'));
        }

        $allGroups = CustomerGroup::all();
        $groupsContainingCustomer = $allGroups->filter(function ($group) use ($id) {
            $customerIds = $group->customer_ids ?? [];
            foreach ($customerIds as $item) {
                if (isset($item['id']) && $item['id'] == $id) {
                    return true;
                }
            }
            return false;
        });
        
        foreach ($groupsContainingCustomer as $group) {
            // Remove customer from the group
            $group->removeCustomer($id);
            $group->save();
            // Note: We DO NOT delete the group even if empty
            // Keep it for future use when new customers are assigned to this driver
        }

        // Delete the customer
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
            // Check if customer is used in any dependent records
            $salesInvoice = SalesInvoice::where('customer_id', $id)->exists();
            $specialPrice = SpecialPrice::where('customer_id', $id)->exists();
            $foc = foc::where('customer_id', $id)->exists();
            
            if ($salesInvoice || $specialPrice || $foc) {
                continue;
            }

            // Find and process customer groups containing this customer
            $customerGroups = CustomerGroup::where('customer_ids', 'like', '%"id":' . $id . '%')->get();
            
            foreach ($customerGroups as $customerGroup) {
                // Remove customer from the group
                $customerGroup->removeCustomer($id);
                $customerGroup->save();
                
                // If the group has no customers left, delete the group and its assignments
                $remainingCustomers = $customerGroup->customer_ids ?? [];
                if (empty($remainingCustomers)) {
                    Assign::where('customer_group_id', $customerGroup->id)->delete();
                    $customerGroup->delete();
                }
            }

            $customer = $this->customerRepository->find($id);
            if ($customer) {
                $count = $count + Customer::destroy($id);
            }
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