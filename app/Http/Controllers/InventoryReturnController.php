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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $driver = Driver::find($request->driver_id);
        try {
            $inventoryReturn = InventoryReturn::create([
                'driver_id' => $request->driver_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'status' => InventoryReturn::STATUS_APPROVED,
                'remarks' => $request->remarks,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'trip_id'=> $driver->trip_id,
            ]);

            $inventoryBalance = InventoryBalance::firstOrNew([
                'driver_id' => $inventoryReturn->driver_id,
                'product_id' => $inventoryReturn->product_id
            ]);

            // Ensure quantity is not negative (validate driver has enough stock)
            $currentBalance = $inventoryBalance->quantity ?? 0;
            if ($currentBalance < $inventoryReturn->quantity) {
                // If driver doesn't have enough stock, delete the return and return error
                $inventoryReturn->delete();
                
                Flash::error('Driver does not have enough stock to return. Available: ' . $currentBalance);
                return redirect()->back()->withInput();
            }

            // Subtract the returned quantity from existing balance
            $inventoryBalance->quantity = $currentBalance - $inventoryReturn->quantity;
            $inventoryBalance->save();

            // Create inventory transaction record for STOCK OUT
            InventoryTransaction::createTransaction(
                $inventoryReturn->driver_id,
                $inventoryReturn->product_id,
                $inventoryReturn->quantity,
                InventoryTransaction::TYPE_STOCK_OUT,
                'Stock Return',
            );

            Flash::success('Inventory Return created successfully.');
            return redirect(route('inventoryReturns.index'));

        } catch (\Exception $e) {
            Flash::error('Failed to create return: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $inventoryReturn = InventoryReturn::with(['driver', 'product', 'approver', 'rejector'])->findOrFail($id);

        return view('inventory_returns.show', compact('inventoryReturn'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $inventoryReturn = InventoryReturn::findOrFail($id);

        // Check if return can be updated - ONLY pending returns
        if ($inventoryReturn->status !== InventoryReturn::STATUS_PENDING) {
            Flash::error('Only pending returns can be updated.');
            return redirect()->back();
        }

        // For quantity-only updates (from view modal)
        if ($request->has('quantity') && !$request->has('driver_id') && !$request->has('product_id')) {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1'
            ]);
        } else {
            // For full updates (from edit modal)
            $validator = Validator::make($request->all(), InventoryReturn::$rules);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $inventoryReturn->update($request->only(['quantity', 'driver_id', 'product_id', 'status', 'remarks']));

            Flash::success('Inventory return updated successfully.');
            return redirect()->back();
        } catch (\Exception $e) {
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
     * Get statistics for inventory requests
     */
    public function statistics()
    {
        $total = InventoryReturn::count();
        $pending = InventoryReturn::pending()->count();
        $approved = InventoryReturn::approved()->count();
        $rejected = InventoryReturn::rejected()->count();

        // Since this might be called via AJAX for dashboard, keep as JSON
        // If this is only used in blade views, you can pass it directly to view
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