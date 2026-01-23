<?php

namespace App\Http\Controllers;

use App\DataTables\InventoryRequestDataTable;
use App\Models\InventoryRequest;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Driver;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Flash;

class InventoryRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(InventoryRequestDataTable $dataTable, Request $request)
    {
        // Get data for filters
        $drivers = Driver::all();
        $products = Product::all();
        $statuses = InventoryRequest::getStatusOptions();

        // Pass filter parameters to DataTable
        $dataTable = $dataTable
            ->with([
                'status' => $request->get('status', 'all'),
                'driver_id' => $request->get('driver_id'),
                'product_id' => $request->get('product_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ]);

        // Return DataTable for AJAX requests
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        // Return view with DataTable for regular requests
        return $dataTable->render('inventory_requests.index', compact('drivers', 'products', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), InventoryRequest::$rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Validate items array
            $items = $request->items;
            if (!is_array($items) || empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please add at least one item'
                ], 422);
            }

            // Check for duplicate products
            $productIds = array_column($items, 'product_id');
            if (count($productIds) !== count(array_unique($productIds))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate products are not allowed in the same request'
                ], 422);
            }

            // Create inventory request with items
            $inventoryRequest = InventoryRequest::create([
                'driver_id' => $request->driver_id,
                'items' => $items, // Store as JSON array
                'status' => InventoryRequest::STATUS_PENDING,
                'remarks' => $request->remarks,
            ]);

            // Return success response (will be handled by AJAX)
            return response()->json([
                'success' => true,
                'message' => 'Inventory request created successfully.',
                'data' => $inventoryRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $request = InventoryRequest::with(['driver', 'approver', 'rejector'])->findOrFail($id);

        // Load product information for each item
        $items = collect($request->items)->map(function($item) {
            $product = Product::find($item['product_id']);
            return [
                'product_id' => $item['product_id'],
                'product_name' => $product ? $product->name : 'Unknown Product',
                'quantity' => $item['quantity'],
                'current_quantity' => $item['quantity'] // For compatibility with view
            ];
        });

        $request->items = $items;

        return view('inventory_requests.show', compact('request'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $inventoryRequest = InventoryRequest::findOrFail($id);

        // Check if request can be updated - ONLY pending requests
        if ($inventoryRequest->status !== InventoryRequest::STATUS_PENDING) {
            // If it's an AJAX request, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be updated.'
                ], 403);
            }
            
            // If it's a regular request, redirect back with error
            Flash::error('Only pending requests can be updated.');
            return redirect()->route('inventoryRequests.index');
        }

        // For full updates (from edit modal) with multiple items
        if ($request->has('items')) {
            $validator = Validator::make($request->all(), InventoryRequest::$rules);
        } else {
            // For backward compatibility with old single-item updates
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
                'driver_id' => 'required|exists:drivers,id',
                'product_id' => 'required|exists:products,id'
            ]);
        }

        if ($validator->fails()) {
            // If it's an AJAX request, return JSON errors
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // If it's a regular request, redirect back with errors
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Update with new items array if provided
            if ($request->has('items')) {
                $items = $request->items;
                
                // Check for duplicate products
                $productIds = array_column($items, 'product_id');
                if (count($productIds) !== count(array_unique($productIds))) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Duplicate products are not allowed in the same request'
                        ], 422);
                    }
                    
                    Flash::error('Duplicate products are not allowed in the same request');
                    return redirect()->route('inventoryRequests.index');
                }

                $updateData = [
                    'driver_id' => $request->driver_id,
                    'items' => $items,
                    'remarks' => $request->remarks,
                ];
            } else {
                // For backward compatibility - convert single item to array
                $updateData = [
                    'driver_id' => $request->driver_id,
                    'items' => [[
                        'product_id' => $request->product_id,
                        'quantity' => $request->quantity
                    ]],
                    'remarks' => $request->remarks,
                ];
            }

            // Update the request
            $inventoryRequest->update($updateData);
            
            // Check if we need to save and approve
            if ($request->has('save_and_approve') && $request->save_and_approve == '1') {
                // Check if request can be approved
                if (!$inventoryRequest->canBeApproved()) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This request cannot be approved.'
                        ], 403);
                    }
                    
                    Flash::error('This request cannot be approved.');
                    return redirect()->route('inventoryRequests.index');
                }
                
                // Check if items exist
                $items = $inventoryRequest->items;
                if (empty($items) || !is_array($items)) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No items found in this request.'
                        ], 422);
                    }
                    
                    Flash::error('No items found in this request.');
                    return redirect()->route('inventoryRequests.index');
                }

                // Process each item
                foreach ($items as $item) {
                    if (!isset($item['product_id']) || !isset($item['quantity'])) {
                        continue; // Skip invalid items
                    }

                    // Update inventory balance for the driver
                    $inventoryBalance = InventoryBalance::firstOrNew([
                        'driver_id' => $inventoryRequest->driver_id,
                        'product_id' => $item['product_id']
                    ]);

                    // Add the requested quantity to existing balance
                    $inventoryBalance->quantity = ($inventoryBalance->quantity ?? 0) + $item['quantity'];
                    $inventoryBalance->save();

                    // Create inventory transaction record for STOCK IN
                    InventoryTransaction::createTransaction(
                        $inventoryRequest->driver_id,
                        $item['product_id'],
                        $item['quantity'],
                        InventoryTransaction::TYPE_STOCK_IN,
                        'Stock Request Approval - Approved by: ' . Auth::user()->name,
                    );
                }

                // Update request status
                $inventoryRequest->update([
                    'status' => InventoryRequest::STATUS_APPROVED,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
                
                // Set flash message based on request type
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Inventory request updated and approved successfully. Quantities added to driver inventory.',
                        'redirect' => route('inventoryRequests.index')
                    ]);
                }
                
                Flash::success('Inventory request updated and approved successfully. Quantities added to driver inventory.');
                return redirect()->route('inventoryRequests.index');
            }

            // If it's an AJAX request, return JSON response
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inventory request updated successfully.',
                    'data' => $inventoryRequest,
                    'redirect' => route('inventoryRequests.index')
                ]);
            }
            
            // For regular form submission, redirect back to index
            Flash::success('Inventory request updated successfully.');
            return redirect()->route('inventoryRequests.index');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update request: ' . $e->getMessage()
                ], 500);
            }
            
            Flash::error('Failed to update request: ' . $e->getMessage());
            return redirect()->route('inventoryRequests.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $inventoryRequest = InventoryRequest::findOrFail($id);

        // Only allow deletion if request is pending
        if ($inventoryRequest->status !== InventoryRequest::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be deleted.'
            ], 403);
        }

        try {
            $inventoryRequest->delete();

            Flash::success('Inventory Request deleted successfully.');
            return redirect(route('inventoryRequests.index'));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve the specified inventory request.
     */
    public function approve(Request $request, $id)
    {
        $inventoryRequest = InventoryRequest::findOrFail($id);

        // Check if request can be approved
        if (!$inventoryRequest->canBeApproved()) {
            // If it's an AJAX request, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This request cannot be approved.'
                ], 403);
            }
            
            Flash::error('This request cannot be approved.');
            return redirect()->route('inventoryRequests.index');
        }

        try {
            // Check if items exist
            $items = $inventoryRequest->items;
            if (empty($items) || !is_array($items)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No items found in this request.'
                    ], 422);
                }
                
                Flash::error('No items found in this request.');
                return redirect()->route('inventoryRequests.index');
            }

            // Process each item
            foreach ($items as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    continue; // Skip invalid items
                }

                // Update inventory balance for the driver
                $inventoryBalance = InventoryBalance::firstOrNew([
                    'driver_id' => $inventoryRequest->driver_id,
                    'product_id' => $item['product_id']
                ]);

                // Add the requested quantity to existing balance
                    $inventoryBalance->quantity = ($inventoryBalance->quantity ?? 0) + $item['quantity'];
                    $inventoryBalance->save();

                // Create inventory transaction record for STOCK IN
                InventoryTransaction::createTransaction(
                    $inventoryRequest->driver_id,
                    $item['product_id'],
                    $item['quantity'],
                    InventoryTransaction::TYPE_STOCK_IN,
                    'Stock Request Approval - Approved by: ' . Auth::user()->name,
                );
            }

            // Update request status
            $inventoryRequest->update([
                'status' => InventoryRequest::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // If it's an AJAX request, return JSON response with redirect
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inventory request approved successfully. Quantities added to driver inventory.',
                    'redirect' => route('inventoryRequests.index')
                ]);
            }
            
            // For regular form submission, redirect back to index
            Flash::success('Inventory request approved successfully. Quantities added to driver inventory.');
            return redirect()->route('inventoryRequests.index');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve request: ' . $e->getMessage()
                ], 500);
            }
            
            Flash::error('Failed to approve request: ' . $e->getMessage());
            return redirect()->route('inventoryRequests.index');
        }
    }

    /**
     * Reject the specified inventory request.
     */
    public function reject(Request $request, $id)
    {
        $inventoryRequest = InventoryRequest::findOrFail($id);
        
        // Check if request can be rejected
        if (!$inventoryRequest->canBeRejected()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This request cannot be rejected.'
                ], 403);
            }
            
            Flash::error('This request cannot be rejected.');
            return redirect()->route('inventoryRequests.index');
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:5|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $inventoryRequest->update([
                'status' => InventoryRequest::STATUS_REJECTED,
                'rejected_by' => Auth::id(),
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inventory request rejected successfully.',
                    'redirect' => route('inventoryRequests.index')
                ]);
            }
            
            Flash::success('Inventory request rejected successfully.');
            return redirect()->route('inventoryRequests.index');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reject request: ' . $e->getMessage()
                ], 500);
            }
            
            Flash::error('Failed to reject request: ' . $e->getMessage());
            return redirect()->route('inventoryRequests.index');
        }
    }

    /**
     * Get statistics for inventory requests
     */
    public function statistics()
    {
        $total = InventoryRequest::count();
        $pending = InventoryRequest::pending()->count();
        $approved = InventoryRequest::approved()->count();
        $rejected = InventoryRequest::rejected()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
            ]
        ]);
    }

    /**
     * Get request data for AJAX operations (for view modal)
     */
    public function getRequestData($id)
    {
        $request = InventoryRequest::with(['driver', 'approver', 'rejector'])->findOrFail($id);

        // Prepare items with product names
        $items = [];
        if ($request->items && is_array($request->items)) {
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $items[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product ? $product->name : 'Unknown Product',
                    'quantity' => $item['quantity']
                ];
            }
        }

        $requestData = [
            'id' => $request->id,
            'driver_id' => $request->driver_id,
            'driver_name' => $request->driver ? $request->driver->name : 'N/A',
            'items' => $items,
            'total_quantity' => $request->total_quantity,
            'item_count' => $request->item_count,
            'status' => $request->status,
            'remarks' => $request->remarks,
            'created_at' => $request->created_at ? $request->created_at->format('Y-m-d H:i:s') : 'N/A',
            'approved_by' => $request->approver ? $request->approver->name : null,
            'approved_at' => $request->approved_at ? $request->approved_at->format('Y-m-d H:i:s') : null,
            'rejected_by' => $request->rejector ? $request->rejector->name : null,
            'rejected_at' => $request->rejected_at ? $request->rejected_at->format('Y-m-d H:i:s') : null,
            'rejection_reason' => $request->rejection_reason,
        ];

        return response()->json([
            'success' => true,
            'data' => $requestData
        ]);
    }
}