<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerGroupDataTable;
use App\Repositories\CustomerGroupRepository;
use App\Repositories\CustomerRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;

class CustomerGroupController extends AppBaseController
{
    /** @var CustomerGroupRepository $customerGroupRepository*/
    private $customerGroupRepository;
    
    /** @var CustomerRepository $customerRepository*/
    private $customerRepository;

    public function __construct(CustomerGroupRepository $customerGroupRepo, CustomerRepository $customerRepo)
    {
        $this->customerGroupRepository = $customerGroupRepo;
        $this->customerRepository = $customerRepo;
    }

    /**
     * Display a listing of the CustomerGroup.
     *
     * @param CustomerGroupDataTable $customerGroupDataTable
     *
     * @return Response
     */
    public function index(CustomerGroupDataTable $customerGroupDataTable)
    {
        return $customerGroupDataTable->render('customer_group.index');
    }

    /**
     * Show the form for creating a new CustomerGroup.
     *
     * @return Response
     */
    public function create()
    {
        $customers = $this->customerRepository->all()->pluck('company', 'id');
        return view('customer_group.create', compact('customers'));
    }

    /**
     * Store a newly created CustomerGroup in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // Fix validation - use dot notation for array validation
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_ids' => 'nullable|array',
            'customer_ids.*.id' => 'required|integer|exists:customers,id',
            'customer_ids.*.sequence' => 'nullable|integer|min:1'
        ]);
        
        $input = $request->all();
        
        // Format customer_ids with sequence
        if (isset($input['customer_ids']) && is_array($input['customer_ids'])) {
            $formattedCustomers = [];
            $sequence = 1;
            
            foreach ($input['customer_ids'] as $customerData) {
                if (is_array($customerData) && isset($customerData['id'])) {
                    $formattedCustomers[] = [
                        'id' => (int) $customerData['id'],
                        'sequence' => isset($customerData['sequence']) ? (int) $customerData['sequence'] : $sequence
                    ];
                    $sequence++;
                } elseif (is_numeric($customerData)) {
                    // Backward compatibility - just ID provided
                    $formattedCustomers[] = [
                        'id' => (int) $customerData,
                        'sequence' => $sequence
                    ];
                    $sequence++;
                }
            }
            
            // Sort by sequence
            usort($formattedCustomers, function($a, $b) {
                return $a['sequence'] <=> $b['sequence'];
            });
            
            $input['customer_ids'] = $formattedCustomers;
        } else {
            $input['customer_ids'] = [];
        }
        
        // Create the customer group
        $customerGroup = $this->customerGroupRepository->create($input);

        Flash::success(__('customer_group.customer_group_saved_successfully'));

        return redirect(route('customer_group.index'));
    }

    /**
     * Display the specified CustomerGroup.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $customerGroup = $this->customerGroupRepository->find($id);

        if (empty($customerGroup)) {
            Flash::error(__('customer_group.customer_group_not_found'));

            return redirect(route('customer_groups.index'));
        }

        return view('customer_group.show')->with('customerGroup', $customerGroup);
    }

    /**
     * Show the form for editing the specified CustomerGroup.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $customerGroup = $this->customerGroupRepository->find($id);

        if (empty($customerGroup)) {
            Flash::error(__('customer_group.customer_group_not_found'));

            return redirect(route('customer_group.index'));
        }
        
        $customers = $this->customerRepository->all()->pluck('company', 'id');
        $selectedCustomers = $customerGroup->customer_ids ?? [];

        return view('customer_group.edit', compact('customerGroup', 'customers', 'selectedCustomers'));
    }

    /**
     * Update the specified CustomerGroup in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $customerGroup = $this->customerGroupRepository->find($id);

        if (empty($customerGroup)) {
            Flash::error(__('customer_group.customer_group_not_found'));

            return redirect(route('customer_group.index'));
        }

        // Simple validation
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_ids' => 'nullable|array',
            'customer_ids.*.id' => 'required|integer|exists:customers,id',
            'customer_ids.*.sequence' => 'nullable|integer|min:1'
        ]);

        $input = $request->all();
        
        // Format customer_ids with sequence
        if (isset($input['customer_ids']) && is_array($input['customer_ids'])) {
            $formattedCustomers = [];
            $sequence = 1;
            
            foreach ($input['customer_ids'] as $customerData) {
                if (is_array($customerData) && isset($customerData['id'])) {
                    $formattedCustomers[] = [
                        'id' => (int) $customerData['id'],
                        'sequence' => isset($customerData['sequence']) ? (int) $customerData['sequence'] : $sequence
                    ];
                    $sequence++;
                } elseif (is_numeric($customerData)) {
                    // Backward compatibility - just ID provided
                    $formattedCustomers[] = [
                        'id' => (int) $customerData,
                        'sequence' => $sequence
                    ];
                    $sequence++;
                }
            }
            
            // Sort by sequence
            usort($formattedCustomers, function($a, $b) {
                return $a['sequence'] <=> $b['sequence'];
            });
            
            $input['customer_ids'] = $formattedCustomers;
        } else {
            $input['customer_ids'] = [];
        }
        
        // Update the customer group
        $customerGroup = $this->customerGroupRepository->update($input, $id);

        Flash::success(__('customer_group.customer_group_updated_successfully'));

        return redirect(route('customer_group.index'));
    }

    /**
     * Remove the specified CustomerGroup from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $customerGroup = $this->customerGroupRepository->find($id);

        if (empty($customerGroup)) {
            Flash::error(__('customer_group.customer_group_not_found'));

            return redirect(route('customer_group.index'));
        }
        
        $this->customerGroupRepository->delete($id);

        Flash::success(__('customer_group.customer_group_deleted_successfully'));

        return redirect(route('customer_group.index'));
    }

    /**
     * Get customers for a specific group (sorted by sequence)
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
            
            // Get customer details sorted by sequence
            $customerData = $customerGroup->customer_ids ?? [];
            if (empty($customerData)) {
                return response()->json([
                    'status' => true,
                    'message' => 'OK',
                    'data' => []
                ], 200);
            }
            
            // Sort by sequence
            usort($customerData, function($a, $b) {
                return $a['sequence'] <=> $b['sequence'];
            });
            
            // Get customer details
            $customerIds = array_column($customerData, 'id');
            $customers = $this->customerRepository->findWhereIn('id', $customerIds, ['id', 'company']);
            
            // Add sequence to customers
            $customerMap = [];
            foreach ($customerData as $data) {
                $customerMap[$data['id']] = $data['sequence'];
            }
            
            $customersWithSequence = $customers->map(function($customer) use ($customerMap) {
                $customer->sequence = $customerMap[$customer->id] ?? 0;
                return $customer;
            })->sortBy('sequence');
            
            return response()->json([
                'status' => true,
                'message' => 'OK',
                'data' => $customersWithSequence->values()
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Something went wrong"
            ], 400);
        }
    }
}