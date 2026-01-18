<?php

namespace App\Http\Controllers;

use App\DataTables\AssignDataTable;
use App\Repositories\AssignRepository;
use App\Repositories\CustomerGroupRepository;
use App\Repositories\DriverRepository;
use App\Repositories\CustomerRepository; // Add this
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use App\Models\Assign;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AssignController extends AppBaseController
{
    /** @var AssignRepository $assignRepository*/
    private $assignRepository;
    
    /** @var CustomerGroupRepository $customerGroupRepository*/
    private $customerGroupRepository;
    
    /** @var DriverRepository $driverRepository*/
    private $driverRepository;
    
    /** @var CustomerRepository $customerRepository*/ // Add this
    private $customerRepository;

    public function __construct(
        AssignRepository $assignRepo,
        CustomerGroupRepository $customerGroupRepo,
        DriverRepository $driverRepo,
        CustomerRepository $customerRepo // Add this
    ) {
        $this->assignRepository = $assignRepo;
        $this->customerGroupRepository = $customerGroupRepo;
        $this->driverRepository = $driverRepo;
        $this->customerRepository = $customerRepo; // Add this
    }

    /**
     * Display a listing of the Assign.
     *
     * @param AssignDataTable $assignDataTable
     *
     * @return Response
     */
    public function index(AssignDataTable $assignDataTable)
    {
        return $assignDataTable->render('assigns.index');
    }

    /**
     * Show the form for creating a new Assign.
     *
     * @return Response
     */
    public function create()
    {
        $drivers = $this->driverRepository->all()->pluck('name', 'id');
        $customerGroups = $this->customerGroupRepository->all()->pluck('name', 'id');
        
        return view('assigns.create', compact('drivers', 'customerGroups'));
    }

    /**
     * Store a newly created Assign in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // Simple validation
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'customer_group_id' => 'required|exists:customer_groups,id',
            'sequence' => 'nullable|integer|min:1'
        ]);

        $input = $request->all();

        // Check if assignment already exists
        $existingAssignment = $this->assignRepository->makeModel()
            ->where('driver_id', $input['driver_id'])
            ->where('customer_group_id', $input['customer_group_id'])
            ->first();
            
        if ($existingAssignment) {
            Flash::error(__('assign.assign_already_exists'));
            return redirect(route('assigns.create'));
        }

        $assign = $this->assignRepository->create($input);

        Flash::success(__('assign.assign_saved_successfully'));

        return redirect(route('assigns.index'));
    }

    /**
     * Display the specified Assign.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $assign = $this->assignRepository->find($id);

        if (empty($assign)) {
            Flash::error(__('assign.assign_not_found'));

            return redirect(route('assigns.index'));
        }

        return view('assigns.show')->with('assign', $assign);
    }

    /**
     * Show the form for editing the specified Assign.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $assign = $this->assignRepository->find($id);

        if (empty($assign)) {
            Flash::error(__('assign.assign_not_found'));

            return redirect(route('assigns.index'));
        }
        
        $drivers = $this->driverRepository->all()->pluck('name', 'id');
        $customerGroups = $this->customerGroupRepository->all()->pluck('name', 'id');

        return view('assigns.edit', compact('assign', 'drivers', 'customerGroups'));
    }

    /**
     * Update the specified Assign in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $assign = $this->assignRepository->find($id);

        if (empty($assign)) {
            Flash::error(__('assign.assign_not_found'));

            return redirect(route('assigns.index'));
        }

        // Simple validation
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'customer_group_id' => 'required|exists:customer_groups,id',
            'sequence' => 'nullable|integer|min:1'
        ]);

        $input = $request->all();
        
        // Check if another assignment already exists with the same driver and customer group
        $existingAssignment = $this->assignRepository->makeModel()
            ->where('driver_id', $input['driver_id'])
            ->where('customer_group_id', $input['customer_group_id'])
            ->where('id', '!=', $id)
            ->first();
            
        if ($existingAssignment) {
            Flash::error(__('assign.assign_already_exists'));
            return redirect(route('assigns.edit', Crypt::encrypt($id)));
        }

        $assign = $this->assignRepository->update($input, $id);

        Flash::success(__('assign.assign_updated_successfully'));

        return redirect(route('assigns.index'));
    }

    /**
     * Remove the specified Assign from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $assign = $this->assignRepository->find($id);

        if (empty($assign)) {
            Flash::error(__('assign.assign_not_found'));

            return redirect(route('assigns.index'));
        }

        $this->assignRepository->delete($id);

        Flash::success(__('assign.assign_deleted_successfully'));

        return redirect(route('assigns.index'));
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];

        $count = 0;

        foreach ($ids as $id) {
            $assign = $this->assignRepository->find($id);
            $count = $count + Assign::destroy($id);
        }

        return $count;
    }

    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];

        $count = Assign::whereIn('id', $ids)->update(['status' => $status]);

        return $count;
    }

    /**
     * Get customers in a customer group (for driver invoice creation)
     */
    public function getGroupCustomers($groupId)
    {
        try {
            $customerGroup = $this->customerGroupRepository->find($groupId);
            
            if (empty($customerGroup)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer group not found'
                ], 200);
            }
            
            // Get customer details using Customer model directly
            $customerIds = $customerGroup->customer_ids;
            $customers = [];
            
            if (!empty($customerIds) && is_array($customerIds)) {
                // Use the Customer model directly
                $customers =Customer::whereIn('id', $customerIds)
                    ->select('id', 'company')
                    ->get()
                    ->toArray();
            }
            
            return response()->json([
                'status' => true,
                'message' => 'OK',
                'data' => [
                    'id' => $customerGroup->id,
                    'name' => $customerGroup->name,
                    'description' => $customerGroup->description,
                    'created_at' => $customerGroup->created_at,
                    'customers' => $customers
                ]
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Error in getGroupCustomers: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Something went wrong: " . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all customer groups assigned to a driver
     */
    public function getDriverCustomerGroups($driverId)
    {
        try {
            $assignments = $this->assignRepository->findWhere(['driver_id' => $driverId], ['customer_group_id']);
            
            if ($assignments->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'OK',
                    'data' => []
                ], 200);
            }
            
            $groupIds = $assignments->pluck('customer_group_id')->toArray();
            $customerGroups = $this->customerGroupRepository->findWhereIn('id', $groupIds, ['id', 'name']);
            
            return response()->json([
                'status' => true,
                'message' => 'OK',
                'data' => $customerGroups
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Something went wrong"
            ], 400);
        }
    }

    /**
     * Get all customers a driver can invoice (from all assigned customer groups)
     */
    public function getDriverInvoiceCustomers($driverId)
    {
        try {
            $assignments = $this->assignRepository
                ->with(['customerGroup'])
                ->findWhere(['driver_id' => $driverId]);
            
            $allCustomerIds = [];
            
            foreach ($assignments as $assignment) {
                if ($assignment->customerGroup && !empty($assignment->customerGroup->customer_ids)) {
                    $allCustomerIds = array_merge($allCustomerIds, $assignment->customerGroup->customer_ids);
                }
            }
            
            // Remove duplicates
            $allCustomerIds = array_values(array_unique($allCustomerIds));
            
            if (empty($allCustomerIds)) {
                return response()->json([
                    'status' => true,
                    'message' => 'OK',
                    'data' => []
                ], 200);
            }
            
            $customers = $this->customerRepository->findWhereIn('id', $allCustomerIds, ['id', 'company']);
            
            return response()->json([
                'status' => true,
                'message' => 'OK',
                'data' => $customers
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Something went wrong"
            ], 400);
        }
    }
}