<?php

namespace App\Http\Controllers;

use App\DataTables\InventoryBalanceDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateInventoryBalanceRequest;
use App\Http\Requests\UpdateInventoryBalanceRequest;
use App\Repositories\InventoryBalanceRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use App\Models\InventoryBalance;
use App\Models\Driver;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryBalanceController extends AppBaseController
{
    /** @var InventoryBalanceRepository $inventoryBalanceRepository*/
    private $inventoryBalanceRepository;

    public function __construct(InventoryBalanceRepository $inventoryBalanceRepo)
    {
        $this->inventoryBalanceRepository = $inventoryBalanceRepo;
    }

    /**
     * Display a listing of the InventoryBalance.
     *
     * @param InventoryBalanceDataTable $inventoryBalanceDataTable
     *
     * @return Response
     */
    public function index(InventoryBalanceDataTable $inventoryBalanceDataTable)
    {
        // Get drivers for the dropdowns
        $driverItems = Driver::pluck('name', 'id')->toArray();
        
        // Get products for the dropdowns
        $productItems = \App\Models\Product::pluck('name', 'id')->toArray();
        
        return $inventoryBalanceDataTable->render('inventory_balances.index', compact('driverItems', 'productItems'));
    }

	public function stockin(Request $request)
    {
        $data = $request->all();
        
        // Get driver_ids - could be array or comma-separated string
        $driverIds = $data['driver_ids'];
        
        // Convert to array if it's a string
        if (is_string($driverIds)) {
            $driverIds = explode(',', $driverIds);
        }
        
        // Ensure driver_ids is an array and remove any empty values
        if (!is_array($driverIds)) {
            $driverIds = [$driverIds];
        }
        
        $driverIds = array_filter($driverIds); // Remove empty values
        $productId = $data['product_id'];
        $quantity = $data['quantity'];
        
        $successCount = 0;
        $errorMessages = [];
        
        foreach ($driverIds as $driverId) {
            // Trim whitespace and convert to integer
            $driverId = trim($driverId);
            
            // Skip empty driver IDs
            if (empty($driverId)) {
                continue;
            }
            
            try {
                $inventoryBalance = InventoryBalance::where('product_id', $productId)
                    ->where('driver_id', $driverId)
                    ->first();

                if (!empty($inventoryBalance)) {
                    // Update the existing inventory balance
                    $inventoryBalance->quantity = $inventoryBalance->quantity + $quantity;
                    $inventoryBalance->save();

                    // Create an inventory transaction record
                    $inventoryTransaction = new InventoryTransaction();
                    $inventoryTransaction->type = 1; // Stock In
                    $inventoryTransaction->driver_id = $driverId;
                    $inventoryTransaction->product_id = $productId;
                    $inventoryTransaction->quantity = $quantity;
                    $inventoryTransaction->save();

                    $successCount++;
                } else {
                    // Insert a new inventory balance
                    $newInventoryBalance = new InventoryBalance();
                    $newInventoryBalance->product_id = $productId;
                    $newInventoryBalance->driver_id = $driverId;
                    $newInventoryBalance->quantity = $quantity;
                    $newInventoryBalance->save();

                    // Create an inventory transaction record
                    $inventoryTransaction = new InventoryTransaction();
                    $inventoryTransaction->type = 1; // Stock In
                    $inventoryTransaction->driver_id = $driverId;
                    $inventoryTransaction->product_id = $productId;
                    $inventoryTransaction->quantity = $quantity;
                    $inventoryTransaction->save();

                    $successCount++;
                }
            } catch (\Exception $e) {
                $errorMessages[] = "Driver ID {$driverId}: " . $e->getMessage();
            }
        }
        
        if ($successCount > 0) {
            if (count($driverIds) == 1) {
                Flash::success("Inventory Balance for driver has been updated successfully.");
            } else {
                Flash::success("{$successCount} out of " . count($driverIds) . " drivers have been updated successfully.");
            }
        }
        
        if (!empty($errorMessages)) {
            Flash::error("Errors occurred: " . implode("; ", $errorMessages));
        }

        return redirect(route('inventoryBalances.index'));
    }

    public function stockout(Request $request)
    {
        $data = $request->all();
        $driverId = $data['driver_id'];  // Now single value, not array
        
        $inventoryBalance = InventoryBalance::where('product_id', $data['product_id'])
            ->where('driver_id', $driverId)
            ->first();
            
        if (!empty($inventoryBalance)) {
            if ($inventoryBalance->quantity >= $data['quantity']) {
                $inventoryBalance->quantity = $inventoryBalance->quantity - $data['quantity'];
                $inventoryBalance->save();
                
                $inventorytransaction = new InventoryTransaction();
                $inventorytransaction->type = 2;
                $inventorytransaction->driver_id = $driverId;
                $inventorytransaction->product_id = $data['product_id'];
                $inventorytransaction->quantity = $data['quantity'] * -1;
                $inventorytransaction->save();
                
                Flash::success('Inventory Balance had been updated successfully.');
            } else {
                Flash::error('Transfer quantity cannot exceed inventory balance quantity.');
            }
        } else {
            Flash::error('Inventory Balance not found.');
        }

        return redirect(route('inventoryBalances.index'));
    }

    public function getstock($driver_id,$product_id)
    {
        $inventoryBalance = InventoryBalance::where('product_id',$product_id)->where('driver_id',$driver_id)->first();
        if(!empty($inventoryBalance)){
            if($inventoryBalance->quantity > 0){
                return response()->json(['status' => true, 'message' => 'Stock found!', 'quantity' => $inventoryBalance->quantity]);
            }else{
                return response()->json(['status' => false,'message' => 'Stock not found!', 'quantity' => 0]);
            }
        }else{
            return response()->json(['status' => false, 'message' => 'Stock not found!', 'quantity' => 0]);
        }
    }

    public function getProductsByDriver(Request $request)
    {
        $driverId = $request->get('driver_id');
        
        $driver = Driver::with(['inventoryBalances' => function($query) {
            $query->where('quantity', '<>', 0)
                ->with('product:id,name,code');
        }])->find($driverId);
        
        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }
        
        $products = [];
        foreach ($driver->inventoryBalances as $balance) {
            if ($balance->quantity != 0 && $balance->product) {
                $products[] = [
                    'product_id' => $balance->product_id,
                    'product_code' => $balance->product->code,
                    'product_name' => $balance->product->name,
                    'quantity' => $balance->quantity
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'driver' => $driver->name,
            'products' => $products,
            'total_products' => count($products)
        ]);
    }

}
