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
        return $inventoryBalanceDataTable->render('inventory_balances.index');
    }
	public function stockin(Request $request)
	{
		$data = $request->all();
		$driverIds = $data['driver_id'];  // This will be an array of selected driver IDs

		foreach ($driverIds as $driverId) {
			$inventoryBalance = InventoryBalance::where('product_id', $data['product_id'])
				->where('driver_id', $driverId)
				->first();

			if (!empty($inventoryBalance)) {
				// Update the existing inventory balance
				$inventoryBalance->quantity = $inventoryBalance->quantity + $data['quantity'];
				$inventoryBalance->save();

				// Create an inventory transaction record
				$inventoryTransaction = new InventoryTransaction();
				$inventoryTransaction->type = 1;
				$inventoryTransaction->driver_id = $driverId;
				$inventoryTransaction->product_id = $inventoryBalance->product_id;
				$inventoryTransaction->quantity = $data['quantity'];
				$inventoryTransaction->date = date("Y-m-d H:i:s");
				$inventoryTransaction->user = Auth::user()->email . ' (' . Auth::user()->name . ')';
				$inventoryTransaction->save();

				Flash::success('Inventory Balance for driver ID ' . $driverId . ' has been updated successfully.');
			} else {
				// Insert a new inventory balance
				$newInventoryBalance = new InventoryBalance();
				$newInventoryBalance->product_id = $data['product_id'];
				$newInventoryBalance->driver_id = $driverId;
				$newInventoryBalance->quantity = $data['quantity'];
				$newInventoryBalance->save();

				// Create an inventory transaction record
				$inventoryTransaction = new InventoryTransaction();
				$inventoryTransaction->type = 1;
				$inventoryTransaction->driver_id = $driverId;
				$inventoryTransaction->product_id = $data['product_id'];
				$inventoryTransaction->quantity = $data['quantity'];
				$inventoryTransaction->date = date("Y-m-d H:i:s");
				$inventoryTransaction->user = Auth::user()->email . ' (' . Auth::user()->name . ')';
				$inventoryTransaction->save();

				Flash::success('Inventory Balance for driver ID ' . $driverId . ' has been inserted successfully.');
			}
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

    public function stockout(Request $request)
    {
        $data = $request->all();
        $inventoryBalance = InventoryBalance::where('product_id',$data['product_id'])->where('driver_id',$data['driver_id'])->first();
        if(!empty($inventoryBalance)){
            if($inventoryBalance->quantity >= $data['quantity']){
                $inventoryBalance->quantity = $inventoryBalance->quantity - $data['quantity'];
                $inventoryBalance->save();
                $inventorytransaction = new InventoryTransaction();
                $inventorytransaction->type = 2;
                $inventorytransaction->driver_id = $data['driver_id'];
                $inventorytransaction->product_id = $data['product_id'];
                $inventorytransaction->quantity = $data['quantity'] * -1;
                $inventorytransaction->date = date("Y-m-d H:i:s");
                $inventorytransaction->user = Auth::user()->email . ' (' . Auth::user()->name . ')';
                $inventorytransaction->save();
                Flash::success('Inventory Balance had been updated successfully.');
            }else{
                Flash::error('Transfer quantity cannot more than inventory balance quantity.');
            }
        }else{
            Flash::error('Inventory Balance not found.');
        }

        return redirect(route('inventoryBalances.index'));
    }
}
