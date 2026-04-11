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
use App\Models\Foc;
use App\Models\Assign;
use App\Models\CustomerGroup;
use Illuminate\Support\Facades\Session;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Models\Driver;

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
        
        // Handle driver - store name instead of ID
        if(!empty($input['driver'])) {
            // Find driver by name to get ID for assignment logic
            $driver = Driver::where('name', $input['driver'])->first();
            
            if ($driver) {
                // Check if the driver already has a customer group
                $existingAssign = Assign::where('driver_id', $driver->id)->first();
                
                if ($existingAssign) {
                    // Driver has an existing group, we'll add the customer after creation
                    $customer = $this->customerRepository->create($input);
                    
                    // Add customer to the existing group
                    $customerGroup = CustomerGroup::find($existingAssign->customer_group_id);
                    $customerGroup->addCustomerWithSequence($customer->id);
                    $customerGroup->save();
                } else {
                    // Create customer first
                    $customer = $this->customerRepository->create($input);
                    
                    // Driver doesn't have a group, create new group and assignment
                    $customerGroup = CustomerGroup::create([
                        'name' => $driver->name, // Changed from $input['company'] to $driver->name
                        'customer_ids' => [
                            [
                                'id' => $customer->id,
                                'sequence' => 1
                            ]
                        ]
                    ]);
                    
                    // Create assignment
                    Assign::create([
                        'driver_id' => $driver->id,
                        'customer_group_id' => $customerGroup->id
                    ]);
                }
            } else {
                // Driver name doesn't exist in drivers table, just create customer without group
                $customer = $this->customerRepository->create($input);
            }
        } else {
            // No driver provided, just create customer
            $customer = $this->customerRepository->create($input);
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
            'updated_at' => 'nullable',
            'driver' => 'nullable|string|max:255'
        ];
        
        // Validate using model rules
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();
        // Store old driver name before updating customer
        $oldDriverName = $customer->driver ?? null;

        // Update customer
        $customer->code = $input['code'];
        $customer->company = $input['company'];
        $customer->paymentterm = $input['paymentterm'];
        $customer->phone = $input['phone'] ?? null;
        $customer->address = $input['address'] ?? null;
        $customer->status = $input['status'];
        $customer->driver = $input['driver'] ?? null;
        $customer->sst = $input['sst'] ?? null;
        $customer->tin = $input['tin'] ?? null;

        $customer->save();

        // Handle driver assignment
        if(!empty($input['driver'])) {
            $newDriverName = $input['driver'];
            
            // Find driver by name to get ID
            $newDriver = Driver::where('name', $newDriverName)->first();
            
            if (!$newDriver) {
                // If driver doesn't exist in drivers table, just update customer and skip group logic
                Flash::success('Customer updated successfully.');
                return redirect(route('customers.index'));
            }
            
            // Find old driver by name to get ID
            $oldDriver = null;
            if ($oldDriverName) {
                $oldDriver = Driver::where('name', $oldDriverName)->first();
            }
            
            // Check if the driver has changed
            if ($oldDriverName != $newDriverName) {
                // Remove customer from old driver's group if they had one
                if ($oldDriver) {
                    $oldAssign = Assign::where('driver_id', $oldDriver->id)->first();
                    if ($oldAssign) {
                        $oldGroup = CustomerGroup::find($oldAssign->customer_group_id);
                        if ($oldGroup) {
                            // Remove customer from old group
                            $oldGroup->removeCustomer($customer->id);
                            $oldGroup->save();
                        }
                    }
                }
                
                // Add customer to new driver's group
                $newAssign = Assign::where('driver_id', $newDriver->id)->first();
                
                if ($newAssign) {
                    // New driver has an existing group, add customer to it
                    $newGroup = CustomerGroup::find($newAssign->customer_group_id);
                    $newGroup->addCustomerWithSequence($customer->id);
                    $newGroup->save();
                } else {
                    // New driver doesn't have a group, create new group and assignment
                    $newGroup = CustomerGroup::create([
                        'name' => $newDriver->name, 
                        'customer_ids' => [
                            [
                                'id' => $customer->id,
                                'sequence' => 1
                            ]
                        ]
                    ]);
                    
                    // Create assignment
                    Assign::create([
                        'driver_id' => $newDriver->id,
                        'customer_group_id' => $newGroup->id
                    ]);
                }
            } else {
                // Same driver, just update group name if needed and ensure customer is in the group
                $assign = Assign::where('driver_id', $newDriver->id)->first();
                
                if ($assign) {
                    $group = CustomerGroup::find($assign->customer_group_id);
                    
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
            if ($oldDriverName) {
                $oldDriver = Driver::where('name', $oldDriverName)->first();
                if ($oldDriver) {
                    $oldAssign = Assign::where('driver_id', $oldDriver->id)->first();
                    if ($oldAssign) {
                        $oldGroup = CustomerGroup::find($oldAssign->customer_group_id);
                        if ($oldGroup) {
                            // Remove customer from old group
                            $oldGroup->removeCustomer($customer->id);
                            $oldGroup->save();
                        }
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
        $foc = Foc::where('customer_id', $id)->exists();
        if ($foc) {
            Flash::error('Unable to delete ' . $customer->company . ', ' . $customer->company . ' is being used in Foc');
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
            $Foc = Foc::where('customer_id', $id)->exists();
            
            if ($salesInvoice || $specialPrice || $Foc) {
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