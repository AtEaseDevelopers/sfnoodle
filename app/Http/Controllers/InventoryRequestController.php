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
            $inventoryRequest = InventoryRequest::create([
                'driver_id' => $request->driver_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'status' => InventoryRequest::STATUS_PENDING,
                'remarks' => $request->remarks,
            ]);

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
        $request = InventoryRequest::with(['driver', 'product', 'approver', 'rejector'])->findOrFail($id);

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
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be updated.'
            ], 403);
        }

        // For quantity-only updates (from view modal)
        if ($request->has('quantity') && !$request->has('driver_id') && !$request->has('product_id')) {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1'
            ]);
        } else {
            // For full updates (from edit modal)
            $validator = Validator::make($request->all(), InventoryRequest::$rules);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $inventoryRequest->update($request->only(['quantity', 'driver_id', 'product_id', 'status', 'remarks']));

            return response()->json([
                'success' => true,
                'message' => 'Inventory request updated successfully.',
                'data' => $inventoryRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update request: ' . $e->getMessage()
            ], 500);
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
            return response()->json([
                'success' => false,
                'message' => 'This request cannot be approved.'
            ], 403);
        }

        try {
            // Update inventory balance for the driver
            $inventoryBalance = InventoryBalance::firstOrNew([
                'driver_id' => $inventoryRequest->driver_id,
                'product_id' => $inventoryRequest->product_id
            ]);

            // Add the requested quantity to existing balance
            $inventoryBalance->quantity = ($inventoryBalance->quantity ?? 0) + $inventoryRequest->quantity;
            $inventoryBalance->save();

            // Create inventory transaction record for STOCK IN
            InventoryTransaction::createTransaction(
                $inventoryRequest->driver_id,
                $inventoryRequest->product_id,
                $inventoryRequest->quantity,
                InventoryTransaction::TYPE_STOCK_IN,
                'Stock Request Approval',
            );

            // Update request status
            $inventoryRequest->update([
                'status' => InventoryRequest::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inventory request approved successfully. Quantity added to driver inventory.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request: ' . $e->getMessage()
            ], 500);
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
            return response()->json([
                'success' => false,
                'message' => 'This request cannot be rejected.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:5|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $inventoryRequest->update([
                'status' => InventoryRequest::STATUS_REJECTED,
                'rejected_by' => Auth::id(),
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inventory request rejected successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject request: ' . $e->getMessage()
            ], 500);
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
}