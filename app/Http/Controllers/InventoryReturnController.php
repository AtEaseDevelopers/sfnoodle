<?php

namespace App\Http\Controllers;

use App\DataTables\InventoryReturnDataTable;
use App\Models\InventoryReturn;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Driver;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Flash;

class InventoryReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(InventoryReturnDataTable $dataTable, Request $request)
    {
        // Get data for filters
        $drivers = Driver::all();
        $products = Product::all();
        $statuses = InventoryReturn::getStatusOptions();

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
        return $dataTable->render('inventory_returns.index', compact('drivers', 'products', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 
        $validator = Validator::make($request->all(), InventoryReturn::$rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $driver = Driver::find($request->driver_id);

        try {
            // Validate items array
            $items = $request->items;
            if (!is_array($items) || empty($items)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please add at least one item'
                    ], 422);
                }
                Flash::error('Please add at least one item');
                return redirect()->back()->withInput();
            }

            // Check for duplicate products
            $productIds = array_column($items, 'product_id');
            if (count($productIds) !== count(array_unique($productIds))) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Duplicate products are not allowed in the same return'
                    ], 422);
                }
                Flash::error('Duplicate products are not allowed in the same return');
                return redirect()->back()->withInput();
            }

            // Create inventory return with items (NO stock validation)
            $inventoryReturn = InventoryReturn::create([
                'driver_id' => $request->driver_id,
                'items' => $items,
                'status' => InventoryReturn::STATUS_APPROVED,
                'remarks' => $request->remarks,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'trip_id' => $driver->trip_id ?? null,
            ]);

            // Process each item - ONLY deduct if product exists in inventory
            foreach ($items as $item) {
                $inventoryBalance = InventoryBalance::where([
                    'driver_id' => $inventoryReturn->driver_id,
                    'product_id' => $item['product_id']
                ])->first();

                // Only deduct if inventory record exists
                if ($inventoryBalance) {
                    $currentBalance = $inventoryBalance->quantity ?? 0;
                    $inventoryBalance->quantity = $currentBalance - $item['quantity'];
                    $inventoryBalance->save();
                    
                    $user = Auth::user();
                    InventoryTransaction::createTransaction(
                        $inventoryReturn->driver_id,
                        $item['product_id'],
                        $item['quantity'],
                        InventoryTransaction::TYPE_STOCK_RETURN,
                        'Stock Return - Approved by: ' . ($user ? $user->name : 'System'),
                    );
                }
                // If inventory record doesn't exist, skip deduction (do nothing)
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inventory Return created successfully.',
                    'data' => $inventoryReturn
                ]);
            }
            
            Flash::success('Inventory Return created successfully.');
            return redirect(route('inventoryReturns.index'));

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create return: ' . $e->getMessage()
                ], 500);
            }
            Flash::error('Failed to create return: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $inventoryReturn = InventoryReturn::with(['driver', 'approver', 'rejector'])->findOrFail($id);

        // Load product information for each item
        $items = collect($inventoryReturn->items)->map(function($item) {
            $product = Product::find($item['product_id']);
            return [
                'product_id' => $item['product_id'],
                'product_name' => $product ? $product->name : 'Unknown Product',
                'quantity' => $item['quantity']
            ];
        });

        $inventoryReturn->items = $items;

        return view('inventory_returns.show', compact('inventoryReturn'));
    }

    public function update(Request $request, $id)
    {
        $inventoryReturn = InventoryReturn::findOrFail($id);

        // Check if return can be updated - ONLY pending returns
        if ($inventoryReturn->status !== InventoryReturn::STATUS_PENDING) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending returns can be updated.'
                ], 403);
            }
            
            Flash::error('Only pending returns can be updated.');
            return redirect()->back();
        }

        // Validate the request based on what's being updated
        if ($request->has('items')) {
            $validator = Validator::make($request->all(), InventoryReturn::$rules);
        } else if ($request->has('quantity') && !$request->has('driver_id') && !$request->has('product_id')) {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1'
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
                'driver_id' => 'required|exists:drivers,id',
                'product_id' => 'required|exists:products,id'
            ]);
        }

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get old items to reverse previous deductions
            $oldItems = $inventoryReturn->items;
            
            // Reverse previous deductions (add back the stock) - ONLY if inventory record exists
            if (!empty($oldItems) && is_array($oldItems)) {
                foreach ($oldItems as $oldItem) {
                    $inventoryBalance = InventoryBalance::where([
                        'driver_id' => $inventoryReturn->driver_id,
                        'product_id' => $oldItem['product_id']
                    ])->first();
                    
                    // Only add back if inventory record exists
                    if ($inventoryBalance) {
                        $inventoryBalance->quantity = ($inventoryBalance->quantity ?? 0) + $oldItem['quantity'];
                        $inventoryBalance->save();
                    }
                }
            }
            
            if ($request->has('items')) {
                $items = $request->items;
                
                // Check for duplicate products
                $productIds = array_column($items, 'product_id');
                if (count($productIds) !== count(array_unique($productIds))) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Duplicate products are not allowed in the same return'
                        ], 422);
                    }
                    
                    Flash::error('Duplicate products are not allowed in the same return');
                    return redirect()->back()->withInput();
                }
                
                $updateData = [
                    'driver_id' => $request->driver_id,
                    'items' => $items,
                    'remarks' => $request->remarks,
                ];
            } else {
                $updateData = [
                    'driver_id' => $request->driver_id,
                    'items' => [[
                        'product_id' => $request->product_id,
                        'quantity' => $request->quantity
                    ]],
                    'remarks' => $request->remarks,
                ];
            }

            $inventoryReturn->update($updateData);
            
            // Process new items - ONLY deduct if inventory record exists
            $newItems = $updateData['items'];
            foreach ($newItems as $item) {
                $inventoryBalance = InventoryBalance::where([
                    'driver_id' => $inventoryReturn->driver_id,
                    'product_id' => $item['product_id']
                ])->first();
                
                // Only deduct if inventory record exists
                if ($inventoryBalance) {
                    $currentBalance = $inventoryBalance->quantity ?? 0;
                    $inventoryBalance->quantity = $currentBalance - $item['quantity'];
                    $inventoryBalance->save();
                    
                    $user = Auth::user();
                    InventoryTransaction::createTransaction(
                        $inventoryReturn->driver_id,
                        $item['product_id'],
                        $item['quantity'],
                        InventoryTransaction::TYPE_STOCK_RETURN,
                        'Stock Return Updated - ' . ($user ? $user->name : 'System'),
                    );
                }
                // If inventory record doesn't exist, skip deduction (do nothing)
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inventory return updated successfully.',
                    'data' => $inventoryReturn,
                    'redirect' => route('inventoryReturns.index')
                ]);
            }
            
            Flash::success('Inventory return updated successfully.');
            return redirect()->back();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update return: ' . $e->getMessage()
                ], 500);
            }
            
            Flash::error('Failed to update return: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $inventoryReturn = InventoryReturn::findOrFail($id);

        // Only allow deletion if return is pending
        if ($inventoryReturn->status !== InventoryReturn::STATUS_PENDING) {
            Flash::error('Only pending returns can be deleted.');
            return redirect()->back();
        }

        try {
            $inventoryReturn->delete();
            Flash::success('Inventory return deleted successfully.');
            return redirect(route('inventoryReturns.index'));
        } catch (\Exception $e) {
            Flash::error('Failed to delete return: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    
    /**
     * Get statistics for inventory returns
     */
    public function statistics()
    {
        $total = InventoryReturn::count();
        $pending = InventoryReturn::pending()->count();
        $approved = InventoryReturn::approved()->count();
        $rejected = InventoryReturn::rejected()->count();

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

    public function getDriverInventory(Request $request)
    {
        $driverId = $request->get('driver_id');
        
        if (!$driverId) {
            return response()->json([
                'success' => false,
                'message' => 'Driver ID is required'
            ], 400);
        }
        
        // Get ALL inventory items (including zero quantities)
        $inventory = InventoryBalance::where('driver_id', $driverId)
            ->with('product:id,name,code')
            ->get()
            ->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_code' => $item->product->code,
                    'quantity' => $item->quantity
                ];
            });
        
        // Also include products that have no inventory record (quantity = 0)
        $allProducts = Product::all();
        $existingProductIds = $inventory->pluck('product_id')->toArray();
        
        foreach ($allProducts as $product) {
            if (!in_array($product->id, $existingProductIds)) {
                $inventory->push([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => 0
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'inventory' => $inventory
        ]);
    }

    /**
     * Get return data for AJAX operations (for view modal)
     */
    public function getReturnData($id)
    {
        $return = InventoryReturn::with(['driver', 'approver', 'rejector'])->findOrFail($id);

        // Prepare items with product names
        $items = [];
        if ($return->items && is_array($return->items)) {
            foreach ($return->items as $item) {
                $product = Product::find($item['product_id']);
                $items[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product ? $product->name : 'Unknown Product',
                    'quantity' => $item['quantity']
                ];
            }
        }

        $returnData = [
            'id' => $return->id,
            'driver_id' => $return->driver_id,
            'driver_name' => $return->driver ? $return->driver->name : 'N/A',
            'items' => $items,
            'total_quantity' => $return->total_quantity,
            'item_count' => $return->item_count,
            'status' => $return->status,
            'remarks' => $return->remarks,
            'created_at' => $return->created_at ? $return->created_at->format('Y-m-d H:i:s') : 'N/A',
            'approved_by' => $return->approver ? $return->approver->name : null,
            'approved_at' => $return->approved_at ? $return->approved_at->format('Y-m-d H:i:s') : null,
            'rejected_by' => $return->rejector ? $return->rejector->name : null,
            'rejected_at' => $return->rejected_at ? $return->rejected_at->format('Y-m-d H:i:s') : null,
            'rejection_reason' => $return->rejection_reason,
        ];

        return response()->json([
            'success' => true,
            'data' => $returnData
        ]);
    }
}