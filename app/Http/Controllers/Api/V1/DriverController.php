<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Datetime;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Kelindan;
use App\Models\Lorry;
use App\Models\Task;
use App\Models\TaskTransfer;
use App\Models\Assign;
use App\Models\Invoice;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\SpecialPrice;
use App\Models\Customer;
use App\Models\InvoicePayment;
use App\Models\InvoiceDetail;
use App\Models\Code;
use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use App\Models\foc;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetails;
use App\Models\DriverLocation;
use App\Models\Language;
use App\Models\MobileTranslationVersion;
use App\Models\MobileTranslation;
use App\Models\DriverCheckIn;
use App\Models\InventoryRequest;
use App\Models\InventoryCount;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\InventoryReturn;
use Carbon\Carbon;
use App\Services\NotificationService;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    protected $message_separator = "|";
    //Auth
    public function login(Request $request){
        // return "000002" <=> "000002";
        try{
            //validation
            $validator = Validator::make($request->all(), [
                'employeeid' => 'required|string',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $data = $request->all();
            $driver = Driver::where('employeeid', $data['employeeid'])->where('password', $data['password'])->first();
            if(!empty($driver)){
                $session = $driver->session;
                $driver->session = session_create_id();
                $driver->save();

                $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
                if(!empty($trip)){
                    if($trip->type == 2){
                        $status = false;
                    }else{
                        $status = true;
                    }
                }else{
                    $status = false;
                }

                $colorcode = Code::where('code','color_code_'.date("D"))->first()['value'] ?? '';

                if($status){
                    if($session == null){
                        return response()->json([
                                'result' => true,
                                'message' => __LINE__.$this->message_separator.'api.message.login_successfully',
                                'data' => [
                                    'driver' => $driver,
                                    'trip' => [
                                        'status' => true,
                                        'trip' => $trip
                                    ],
                                'colorcode' => $colorcode
                            ]
                        ], 200);
                    }else{
                        return response()->json([
                                'result' => true,
                                'message' => __LINE__.$this->message_separator.'api.message.previous_session_override',
                                'data' => [
                                    'driver' => $driver,
                                    'trip' => [
                                        'status' => true,
                                        'trip' => $trip
                                    ],
                                'colorcode' => $colorcode
                            ]
                        ], 200);
                    }
                }else{
                    if($session == null){
                        return response()->json([
                                'result' => true,
                                'message' => __LINE__.$this->message_separator.'api.message.login_successfully',
                                'data' => [
                                    'driver' => $driver,
                                    'trip' => [
                                        'status' => false
                                    ],
                                'colorcode' => $colorcode
                            ]
                        ], 200);
                    }else{
                        return response()->json([
                                'result' => true,
                                'message' => __LINE__.$this->message_separator.'api.message.previous_session_override',
                                'data' => [
                                    'driver' => $driver,
                                    'trip' => [
                                        'status' => false
                                    ],
                                'colorcode' => $colorcode
                            ]
                        ], 200);
                    }
                }

            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_credential',
                    'data' => null
                ], 401);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function logout(Request $request){
        try{
            //validation
            $validator = Validator::make($request->all(), [
                'session' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $data = $request->all();
            $driver = Driver::where('session', $data['session'])->first();
            if(!empty($driver)){
                $driver->session = NULL;
                $driver->save();
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.login_successfully',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function session(Request $request){
        try{
            //validation
            $validator = Validator::make($request->all(), [
                'session' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $data = $request->all();
            $driver = Driver::where('session', $data['session'])->first();
            $colorcode = Code::where('code','color_code_'.date("D"))->first()['value'] ?? '';
            if(!empty($driver)){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.session_found',

                    'data' => $driver,
                    'colorcode' => $colorcode
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function location(Request $request){
        $data = $request->all();
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validate
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $DriverLocation = new DriverLocation();
            $DriverLocation->date = $data['date'];
            $DriverLocation->latitude = $data['latitude'];
            $DriverLocation->longitude = $data['longitude'];
            $DriverLocation->driver_id = $trip->driver_id;
            $DriverLocation->kelindan_id = $trip->kelindan_id;
            $DriverLocation->lorry_id = $trip->lorry_id;
            $DriverLocation->save();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.driver_location_had_been_updated_successfully',
                'data' => $DriverLocation
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    //Trip
    public function checktrip(Request $request){
        try{
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //process
            $trip = $driver->trip_id;
            if($trip != null){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_started',
                    'data' => [
                        'status' => true,
                        'trip' => $trip
                    ]
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => [
                        'status' => false
                    ]
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function starttrip(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'kelindan_id' => 'nullable|numeric',
                'lorry_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            // $kelindan = Kelindan::where('id', $data['kelindan_id'])->first();
            // if(empty($kelindan)){
            //     return response()->json([
            //         'result' => false,
            //         'message' => __LINE__.$this->message_separator.'Invalid Kelindan',
            //         'data' => null
            //     ], 400);
            // }
            $lorry = Lorry::where('id', $data['lorry_id'])->first();
            if(empty($lorry)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_lorry',
                    'data' => null
                ], 400);
            }
            //process
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    //insert trip
                    $newtrip = new Trip();
                    $newtrip->driver_id = $driver->id;
                    $newtrip->kelindan_id = $data['kelindan_id'] ?? 0;
                    $newtrip->lorry_id = $data['lorry_id'];
                    $newtrip->type = 1;
                    $newtrip->date = date("Y-m-d H:i:s");
                    $newtrip->save();
                    //generate task
                    $assigns = Assign::where('driver_id', $driver->id)->orderby('sequence','asc')->get()->toarray();
                    $count = 1;
                    foreach($assigns as $assign){
                        $task = new Task();
                        $task->date = date("Y-m-d");
                        $task->driver_id = $driver->id;
                        $task->customer_id = $assign['customer_id'];
                        $task->sequence = $count;
                        $task->status = 0;
                        $task->trip_id = $newtrip->id;
                        $task->save();
                        $count = $count + 1;
                    }
                    $invoices = Invoice::where('driver_id', $driver->id)->where('status',0)->where('date',date('Y-m-d'))->get()->toarray();
                    foreach($invoices as $invoice){
                        $task = new Task();
                        $task->date = date("Y-m-d");
                        $task->driver_id = $driver->id;
                        $task->customer_id = $invoice['customer_id'];
                        $task->invoice_id = $invoice['id'];
                        $task->sequence = $count;
                        $task->status = 0;
                        $task->trip_id = $newtrip->id;
                        $task->save();
                        $count = $count + 1;
                    }
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_started_successfully',
                        'data' => $newtrip
                    ], 200);
                }else{
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_started',
                        'data' => null
                    ], 401);
                }
            }else{
                //insert trip
                $newtrip = new Trip();
                $newtrip->driver_id = $driver->id;
                $newtrip->kelindan_id = $data['kelindan_id'] ?? 0;
                $newtrip->lorry_id = $data['lorry_id'];
                $newtrip->type = 1;
                $newtrip->date = date("Y-m-d H:i:s");
                $newtrip->save();
                //generate task
                $assigns = Assign::where('driver_id', $driver->id)->orderby('sequence','asc')->get()->toarray();
                $count = 1;
                foreach($assigns as $assign){
                    $task = new Task();
                    $task->date = date("Y-m-d");
                    $task->driver_id = $driver->id;
                    $task->customer_id = $assign['customer_id'];
                    $task->sequence = $count;
                    $task->status = 0;
                    $task->save();
                    $count = $count + 1;
                }
                $invoices = Invoice::where('driver_id', $driver->id)->where('status',0)->where('date',date('Y-m-d'))->get()->toarray();
                foreach($invoices as $invoice){
                    $task = new Task();
                    $task->date = date("Y-m-d");
                    $task->driver_id = $driver->id;
                    $task->customer_id = $invoice['customer_id'];
                    $task->invoice_id = $invoice['id'];
                    $task->sequence = $count;
                    $task->status = 0;
                    $task->save();
                    $count = $count + 1;
                }
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_started_successfully',
                    'data' => $newtrip
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function endtrip(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'Invalid session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'kelindan_id' => 'required|numeric',
                'lorry_id' => 'required|numeric',
                'cash' => 'required|numeric',
                'advance_amount' => 'nullable|numeric',
                'wastage' => 'present|array',
                'wastage.*.product_id' => 'required|numeric',
                'wastage.*.quantity' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            // $kelindan = Kelindan::where('id', $data['kelindan_id'])->first();
            // if(empty($kelindan)){
            //     return response()->json([
            //         'result' => false,
            //         'message' => __LINE__.$this->message_separator.'Invalid Kelindan',
            //         'data' => null
            //     ], 400);
            // }
            $lorry = Lorry::where('id', $data['lorry_id'])->first();
            if(empty($lorry)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'Invalid Lorry',
                    'data' => null
                ], 400);
            }
            //process
            DB::beginTransaction();
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    DB::rollback();
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'Trip had not started',
                        'data' => null
                    ], 400);
                }else{
                    $newtrip = new Trip();
                    $newtrip->driver_id = $driver->id;
                    $newtrip->kelindan_id = $data['kelindan_id'];
                    $newtrip->lorry_id = $data['lorry_id'];
                    $newtrip->cash = $data['cash'];
                    $newtrip->advance_amount = $data['advance_amount'] ?? 0;
                    $newtrip->type = 2;
                    $newtrip->date = date("Y-m-d H:i:s");
                    $newtrip->save();
                    //cancelled task
                    $task = Task::where('driver_id', $driver->id)->where('date',date('Y-m-d'))->whereIn('status',[0,1])->update(['status' => 9]);
                    foreach($data["wastage"] as $wastage) {
                        $inventorybalance = InventoryBalance::where('lorry_id',$trip->lorry_id)->where('product_id',$wastage['product_id'])->first();
                        if(empty($inventorybalance)){
                            DB::rollback();
                            return response()->json([
                                'result' => false,
                                'message' => __LINE__.$this->message_separator.'Wastage quantity more than available quantity',
                                'data' => null
                            ], 400);
                        }else{
                            if($inventorybalance->quantity < $wastage["quantity"]){
                                DB::rollback();
                                return response()->json([
                                    'result' => false,
                                    'message' => __LINE__.$this->message_separator.'Wastage quantity more than available quantity',
                                    'data' => null
                                ], 400);
                            }else{
                                $inventorybalance->quantity = $inventorybalance->quantity - $wastage["quantity"];
                                $inventorybalance->save();
                                $inventorytransaction = New InventoryTransaction();
                                $inventorytransaction->lorry_id = $trip->lorry_id;
                                $inventorytransaction->product_id = $wastage["product_id"];
                                $inventorytransaction->quantity = $wastage["quantity"] * -1;
                                $inventorytransaction->type = 5;
                                $inventorytransaction->date = date('Y-m-d H:i:s');
                                $inventorytransaction->user = $driver->employeeid . " (" . $driver->name . ")";
                                $inventorytransaction->save();
                            }
                        }
                    }
                    DB::commit();
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'Trip had been ended successfully',
                        'data' => $newtrip
                    ], 200);
                }
            }else{
                DB::rollback();
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'Trip had not started',
                    'data' => null
                ], 400);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function trip(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $validator = Validator::make($request->all(), [
            'kelindan_id' => 'required|numeric',
            'lorry_id' => 'required|numeric',
            'type' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        // $kelindan = Kelindan::where('id', $data['kelindan_id'])->first();
        // if(empty($kelindan)){
        //     return response()->json([
        //         'result' => false,
        //         'message' => __LINE__.$this->message_separator.'Invalid Kelindan',
        //         'data' => null
        //     ], 400);
        // }
        $lorry = Lorry::where('id', $data['lorry_id'])->first();
        if(empty($lorry)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_lorry',
                'data' => null
            ], 400);
        }
        if(!($data['type'] == 1 || $data['type'] == 2)){
            return response()->json([
               'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_type',
                'data' => null
            ], 400);
        }
        //process
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if($data['type'] == 1){
            if(!empty($trip)){
                if($trip->type == 2){
                    //insert trip
                    $newtrip = new Trip();
                    $newtrip->driver_id = $driver->id;
                    $newtrip->kelindan_id = $data['kelindan_id'];
                    $newtrip->lorry_id = $data['lorry_id'];
                    $newtrip->type = 1;
                    $newtrip->date = date("Y-m-d H:i:s");
                    $newtrip->save();
                    //generate task
                    $assigns = Assign::where('driver_id', $driver->id)->orderby('sequence','asc')->get()->toarray();
                    $count = 1;
                    foreach($assigns as $assign){
                        $task = new Task();
                        $task->date = date("Y-m-d");
                        $task->driver_id = $driver->id;
                        $task->customer_id = $assign['customer_id'];
                        $task->sequence = $count;
                        $task->status = 0;
                        $task->save();
                        $count = $count + 1;
                    }
                    $invoices = Invoice::where('driver_id', $driver->id)->where('status',0)->where('date',date('Y-m-d'))->get()->toarray();
                    foreach($invoices as $invoice){
                        $task = new Task();
                        $task->date = date("Y-m-d");
                        $task->driver_id = $driver->id;
                        $task->customer_id = $invoice['customer_id'];
                        $task->invoice_id = $invoice['id'];
                        $task->sequence = $count;
                        $task->status = 0;
                        $task->save();
                        $count = $count + 1;
                    }
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_started_successfully',
                        'data' => $newtrip
                    ], 200);
                }else{
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_started',
                        'data' => null
                    ], 401);
                }
            }else{
                //insert trip
                $newtrip = new Trip();
                $newtrip->driver_id = $driver->id;
                $newtrip->kelindan_id = $data['kelindan_id'];
                $newtrip->lorry_id = $data['lorry_id'];
                $newtrip->type = 1;
                $newtrip->date = date("Y-m-d H:i:s");
                $newtrip->save();
                //generate task
                $assigns = Assign::where('driver_id', $driver->id)->orderby('sequence','asc')->get()->toarray();
                $count = 1;
                foreach($assigns as $assign){
                    $task = new Task();
                    $task->date = date("Y-m-d");
                    $task->driver_id = $driver->id;
                    $task->customer_id = $assign['customer_id'];
                    $task->sequence = $count;
                    $task->status = 0;
                    $task->save();
                    $count = $count + 1;
                }
                $invoices = Invoice::where('driver_id', $driver->id)->where('status',0)->where('date',date('Y-m-d'))->get()->toarray();
                foreach($invoices as $invoice){
                    $task = new Task();
                    $task->date = date("Y-m-d");
                    $task->driver_id = $driver->id;
                    $task->customer_id = $invoice['customer_id'];
                    $task->invoice_id = $invoice['id'];
                    $task->sequence = $count;
                    $task->status = 0;
                    $task->save();
                    $count = $count + 1;
                }
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_started_successfully',
                    'data' => $newtrip
                ], 200);
            }
        }else if($data['type'] == 2){
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 401);
                }else{
                    $newtrip = new Trip();
                    $newtrip->driver_id = $driver->id;
                    $newtrip->kelindan_id = $data['kelindan_id'];
                    $newtrip->lorry_id = $data['lorry_id'];
                    $newtrip->type = 2;
                    $newtrip->date = date("Y-m-d H:i:s");
                    $newtrip->save();
                    //cancelled task
                    $task = Task::where('driver_id', $driver->id)->where('date',date('Y-m-d'))->whereIn('status',[0,1])->update(['status' => 9]);
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_ended_successfully',
                        'data' => $newtrip
                    ], 200);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 401);
            }
        }
    }

    //Kelindan
    public function getkelindan(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //process
            // $kelindan = Kelindan::where('status',1)->select('id','name')->get()->toarray();
            $kelindan = DB::select("select k.id, k.name from kelindans k left join ( select driver_id, type, kelindan_id from trips where id in ( select max(id) as id from trips group by driver_id ) ) b on k.id = b.kelindan_id and b.type = 1 where b.kelindan_id is null;");
            if(count($kelindan) != 0){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.kelindan_found',
                    'data' => $kelindan
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.kelindan_not_found',
                    'data' => null
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    //Lorry
    public function getlorry(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //process
            // $lorry = Lorry::where('status',1)->select('id','lorryno')->get()->toarray();
            $lorry = DB::select("select l.id, l.lorryno from lorrys l left join ( select driver_id, type, lorry_id from trips where id in (select max(id) as id from trips group by driver_id) ) b on l.id = b.lorry_id and b.type = 1 where b.lorry_id is null;");
            if(count($lorry) != 0){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.lorry_found',
                    'data' => $lorry
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.lorry_not_found',
                    'data' => null
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    //Task
    public function gettask(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validate
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            //process
            $task = Task::where('driver_id', $driver->id)
                ->where('date',date('Y-m-d'))
                ->where(function ($query) use ($trip) {
                    $query->where('trip_id', $trip->id)
                        ->orWhere('trip_id', null);
                })
                // ->whereIn('trip_id',[NULL,$trip->id])
                ->with('customer.activefoc')
                ->with('invoice.invoicedetail.product:id,code,name')
                ->get()->toarray();
            if(count($task) != 0){
                $message = true;
                foreach($task as $c=>$t){
                    if(asset($t['customer']['id'])){
                        $task[$c]['customer']['credit'] = round(  (DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$t['customer']['id'].');')[0]->credit ?? 0) ,2);
                        // $task[$c]['customer']['credit'] = $t['customer']['id'];
                        $task[$c]['customer']['product'] = DB::table('products')
                            ->leftJoin('special_prices', function($join) use($t)
                                {
                                    $join->on('special_prices.customer_id','=',DB::raw("'".$t['customer']['id']."'"));
                                    $join->on('special_prices.product_id', '=', 'products.id');
                                    $join->on('special_prices.status', '=', DB::raw("'1'"));
                                })
                            ->where('products.status','1')
                            ->select('products.id','products.code','products.name',DB::raw('coalesce(special_prices.price,products.price) as "price"'))
                            ->get();
                        $task[$c]['customer']['groupcompany'] = DB::table('companies')
                            ->where('companies.group_id',explode(',',$t['customer']['group'])[0])
                            ->select('companies.*')
                            ->first() ?? null;
                    }
                }
            }else{
                $message = false;
            }
            $inventorybalance = InventoryBalance::where('lorry_id',$trip->lorry_id)->with('product')->get()->toarray();
            if($message){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.task_found',
                    'data' => [
                        'task' => $task,
                        'stock' => $inventorybalance
                    ]
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.task_not_found',
                    'data' => [
                        'task' => null,
                        'stock' => $inventorybalance
                    ]
                ], 200);

            }

        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function gettaskpage(Request $request){
        try{
            $data = $request->all();
            $size = 20;
            if(isset($data['size']))
            {
                $size = $data['size'];
            }
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validate
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            //process
            $task = Task::where('driver_id', $driver->id)
                ->where('date',date('Y-m-d'))
                //->where('status','!=',9)
                //->where('status','!=',0)
                ->where(function ($query) use ($trip) {
                    $query->where('trip_id', $trip->id)
                        ->orWhere('trip_id', null);
                })
                // ->whereIn('trip_id',[NULL,$trip->id])
                ->with('customer.activefoc')
                ->with('invoice.invoicedetail.product:id,code,name')
                ->paginate($size);

            if(count($task) != 0){
                $message = true;
                foreach($task as $c=>$t){
                    if(asset($t['customer']['id'])){
                        $task[$c]['customer']['credit'] = round(  (DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$t['customer']['id'].');')[0]->credit ?? 0) ,2);
                        // $task[$c]['customer']['credit'] = $t['customer']['id'];
                        $task[$c]['customer']['product'] = DB::table('products')
                            ->leftJoin('special_prices', function($join) use($t)
                                {
                                    $join->on('special_prices.customer_id','=',DB::raw("'".$t['customer']['id']."'"));
                                    $join->on('special_prices.product_id', '=', 'products.id');
                                    $join->on('special_prices.status', '=', DB::raw("'1'"));
                                })
                            ->where('products.status','1')
                            ->select('products.id','products.code','products.name',DB::raw('coalesce(special_prices.price,products.price) as "price"'))
                            ->get();
                        $task[$c]['customer']['groupcompany'] = DB::table('companies')
                            ->where('companies.group_id',explode(',',$t['customer']['group'])[0])
                            ->select('companies.*')
                            ->first() ?? null;
                    }
                }
            }else{
                $message = false;
            }
            $inventorybalance = InventoryBalance::where('lorry_id',$trip->lorry_id)->with('product')->get()->toarray();
            if($message){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.task_found',
                    'data' => [
                        'task' => $task,
                        'stock' => $inventorybalance
                    ]
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.task_not_found',
                    'data' => [
                        'task' => null,
                        'stock' => $inventorybalance
                    ]
                ], 200);

            }

        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function starttask(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validate
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $task = Task::where('id',$data['task_id'])->first();
            if(empty($task)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_task',
                    'data' => null
                ], 400);
            }else{
                if($task->status == 8){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_completed',
                        'data' => null
                    ], 400);
                }
                if($task->status == 9){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_cancelled',
                        'data' => null
                    ], 400);
                }
                if($task->status == 1){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_in_progress',
                        'data' => null
                    ], 400);
                }
            }
            //process
            $task->status = 1;
            $task->save();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.task_had_been_started_successfully',
                'data' => $task
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function canceltask(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validate
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $task = Task::where('id',$data['task_id'])->first();
            if(empty($task)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_task',
                    'data' => null
                ], 400);
            }else{
                if($task->status == 8){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_completed',
                        'data' => null
                    ], 400);
                }
                if($task->status == 9){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_cancelled',
                        'data' => null
                    ], 400);
                }
            }
            //process
            $task->status = 9;
            $task->save();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.task_had_been_cancelled_successfully',
                'data' => $task
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getproduct(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            if(isset($data['customer_id'])){
                $customer = Customer::where('id', $data['customer_id'])->first();
                if(empty($customer)){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                        'data' => null
                    ], 400);
                }
            }
            //process
            if(isset($data['customer_id'])){
                $product = DB::table('products')
                ->leftJoin('special_prices', function($join) use($data)
                    {
                        $join->on('special_prices.customer_id','=',DB::raw("'".$data['customer_id']."'"));
                        $join->on('special_prices.product_id', '=', 'products.id');
                        $join->on('special_prices.status', '=', DB::raw("'1'"));
                    })
                ->where('products.status','1')
                ->select('products.id','products.code','products.name',DB::raw('coalesce(special_prices.price,products.price) as "price"'))
                ->get();
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.product_found',
                    'data' => $product
                ], 200);
            }else{
                $product = DB::table('products')
                ->where('products.status','1')
                ->select('products.id','products.code','products.name',DB::raw('products.price as "price"'))
                ->get();
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.product_found',
                    'data' => $product
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getcustomer(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //process
            $customer = DB::select("SELECT customers.*,COALESCE(b.credit,0) as credit FROM customers customers RIGHT JOIN ( SELECT customer_id FROM assigns assigns WHERE driver_id = ".$driver->id." UNION SELECT customer_id FROM invoices invoices WHERE driver_id = ".$driver->id." ) a on a.customer_id = customers.id LEFT JOIN ( select invoices.customer_id, sum(invoice_details.totalprice) as totalprice, COALESCE(paymentsummary.amount,0) as paid, ( sum(invoice_details.totalprice) - COALESCE(paymentsummary.amount,0) ) as credit from invoices left join invoice_details on invoices.id = invoice_details.invoice_id left join ( select invoice_payments.customer_id, sum(COALESCE(invoice_payments.amount,0)) as amount from invoice_payments where invoice_payments.status = 1 group by invoice_payments.customer_id ) as paymentsummary on invoices.customer_id = paymentsummary.customer_id where invoices.status = 1 group by invoices.customer_id, paymentsummary.customer_id, paymentsummary.amount ) b on b.customer_id = customers.id");
            if(count($customer) != 0){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.customer_found',
                    'data' => $customer
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.customer_not_found',
                    'data' => null
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function customerdetail(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $customer = Customer::where('id', $data['customer_id'])->first();
            if(empty($customer)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                    'data' => null], 400);
            }
            //process
            $customer->customerdetail = DB::select("select i.date,i.id,'Invoice' as type, i.invoiceno as name, sum(COALESCE(id.totalprice,0)) as amount from invoices i left join invoice_details id on i.id = id.invoice_id where i.customer_id = ".$customer->id." group by i.date, i.id, i.invoiceno, i.customer_id union select ip.created_at as date,ip.id, 'Payment' as type, '' as name, ip.amount as amount from invoice_payments ip where ip.customer_id = ".$customer->id.";");
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.customer_found',
                'data' => $customer
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function customermakepayment(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|numeric',
                'amount' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $customer = Customer::where('id', $data['customer_id'])->first();
            if(empty($customer)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                    'data' => null
                ], 400);
            }
            //process
            $invoicepayment = New InvoicePayment();
            $invoicepayment->customer_id = $customer->id;
            $invoicepayment->amount = $data['amount'];
            $invoicepayment->type = 1;
            $invoicepayment->status = 1;
            $invoicepayment->driver_id = $driver->id;
            $invoicepayment->approve_by = $driver->name;
            $invoicepayment->approve_at = date('Y-m-d H:i:s');
            $invoicepayment->save();
            $invoicepayment->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$invoicepayment->customer_id.');')[0]->credit,2);
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.payment_insert_successfully_found',
                'data' => $invoicepayment
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function customerinvoice(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|numeric',
                'invoice_id' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $invoice = Invoice::where('customer_id', $data['customer_id'])
            ->where('id', $data['invoice_id'])
            ->with('invoicedetail.product')
            ->with('customer')
            ->with('driver')
            ->with('invoicepayment')
            ->first();
            if(empty($invoice)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invoice_not_found',
                    'data' => null
                ], 200);
            }else{
               
               
                  
             try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.$invoice->updated_at.'",'.$invoice->customer_id.');');
                
                if($credit)
                {
                    $invoice->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $invoice->newcredit  = 0;
            }
            
               
               //$invoice->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.$invoice->updated_at.'",'.$invoice->customer_id.');')[0]->credit,2);
               
               
               
                $invoice->customer->groupcompany = DB::table('companies')
                ->where('companies.group_id',explode(',',$invoice->customer->group)[0])
                ->select('companies.*')
                ->first() ?? null;
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.invoice_found',
                    'data' => $invoice
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function customerpayment(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|numeric',
            'payment_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        //process
        $invoicepayment = InvoicePayment::where('customer_id', $data['customer_id'])->where('id', $data['payment_id'])->with('customer')->first();
        if(empty($invoicepayment)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invoice_payment_not_found',
                'data' => null
            ], 200);
        }else{
            
            
              try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.$invoicepayment->updated_at.'",'.$invoicepayment->customer_id.');');
                
                if($credit)
                {
                    $invoicepayment->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $invoicepayment->newcredit  = 0;
            }
            
            //$invoicepayment->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.$invoicepayment->created_at.'",'.$invoicepayment->customer_id.');')[0]->credit,2);
            
            
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.invoice_payment_found',
                'data' => $invoicepayment
            ], 200);
        }
    }

    public function addinvoice(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 401);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'date' => 'date_format:Y-m-d H:i:s',
                'customer_id' => 'required|numeric',
                'type' => 'required|numeric|gt:0|lt:6',
                'remark' => 'present|nullable|string',
                'invoice_id' => 'present|nullable|numeric',
                'invoiceno' => 'present|nullable|string',
                'invoicedetail' => 'required|array',
                'invoicedetail.*.product_id' => 'required',
                'invoicedetail.*.quantity' => 'required',
                'invoicedetail.*.price' => 'required',
                'invoicedetail.*.foc' => 'required|boolean'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $customer = Customer::where('id',$data['customer_id'])->first();
            if(empty($customer)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                    'data' => null
                ], 400);
            }
            //process
            $runningno = Code::where('code','invoicerunningnumber')->first();
            $runningno->value = intval($runningno->value) + 1;
            $runningno->save();
            DB::beginTransaction();
            $extinvoice = Invoice::where('id',$data['invoice_id'])->where('status',0)->first();
            $invoiceno = null;
            $id = null;
            if(!empty($extinvoice)){
                if($extinvoice->invoiceno != $data['invoiceno'] && $data['invoiceno'] != null){
                    $invoiceno = $data['invoiceno'] . "(" . $extinvoice->invoiceno . ")";
                }else{
                    $invoiceno = $extinvoice->invoiceno;
                }
                $id = $extinvoice->id;
                Invoice::where('id',$data['invoice_id'])->delete();
                InvoiceDetail::where('invoice_id',$data['invoice_id'])->delete();
            }else{
                if($data['invoiceno'] != null){
                    $invoiceno = $data['invoiceno'];
                    $invoicerunningnumber = substr($invoiceno, -6);
                    if(($driver->invoice_runningnumber <=> $invoicerunningnumber) == -1){
                        Driver::where('id',$driver->id)->update(['invoice_runningnumber' => $invoicerunningnumber]);
                    }

                }else{
                    $invoiceno = "INV".str_pad($runningno->value, 7, '0', STR_PAD_LEFT);
                }
            }
            $invoice = new Invoice();
            if($id != null){
                $invoice->id = $id;
            }
            $invoice->date = $data['date'] ?? date('Y-m-d H:i:s');
            $invoice->invoiceno = $invoiceno;
            $invoice->customer_id = $data['customer_id'];
            $invoice->driver_id = $trip->driver_id;
            $invoice->kelindan_id = $trip->kelindan_id;
            $invoice->agent_id = $customer->agent_id;
            $invoice->supervisor_id = $customer->supervisor_id;
            $invoice->paymentterm = $data['type'];
            $invoice->status = 1;
            $invoice->chequeno = $data['cheque_no'];
            $invoice->remark = $data['remark'];
            $invoice->save();
            $totalprice = 0;
            foreach($data['invoicedetail'] as $id){
                $product = Product::where('id',$id['product_id'])->first();
                if(empty($product)){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.invalid_product',
                        'data' => null
                    ], 400);
                    DB::rollback();
                }
                $invoicedetail = new InvoiceDetail();
                $invoicedetail->invoice_id = $invoice->id;
                $invoicedetail->product_id = $id['product_id'];
                $invoicedetail->quantity = $id['quantity'];
                $invoicedetail->price = $id['price'];
                $invoicedetail->totalprice = $id['quantity'] * $id['price'];
                $totalprice = $totalprice + $invoicedetail->totalprice;
                if($id['foc']) {
                    $invoicedetail->remark = "FOC"; // Mark as FOC but do NOT count towards achievequantity
                } else {
                    // Only update FOC achievequantity if the product is NOT FOC
                    $foc = Foc::where('customer_id', $customer->id)
                        ->where('product_id', $id['product_id'])
                        ->where('startdate', '<=', date('Y-m-d H:i:s'))
                        ->where('enddate', '>', date('Y-m-d H:i:s'))
                        ->where('status', 1)
                        ->first();

                    if($foc) {
                        $newAchieveQuantity = $foc->achievequantity + $id['quantity'];
                        $newStatus = ($newAchieveQuantity >= $foc->quantity) ? 0 : 1;

                        $foc->update([
                            'achievequantity' => $newAchieveQuantity,
                            'status' => $newStatus
                        ]);
                    }
                }
                $invoicedetail->save();
                $inventorybalance = InventoryBalance::where('lorry_id', $trip->lorry_id)->where('product_id', $id['product_id'])->first();
                if(empty($inventorybalance)){
                    $newinventorybalance = New InventoryBalance();
                    $newinventorybalance->lorry_id = $trip->lorry_id;
                    $newinventorybalance->product_id = $id['product_id'];
                    $newinventorybalance->quantity = 0 - $id['quantity'];
                    $newinventorybalance->save();
                }else{
                    $inventorybalance->quantity = $inventorybalance->quantity - $id['quantity'];
                    $inventorybalance->save();
                }
                $inventorytransaction = New InventoryTransaction();
                $inventorytransaction->lorry_id = $trip->lorry_id;
                $inventorytransaction->product_id = $id['product_id'];
                $inventorytransaction->quantity = $id['quantity'] * -1;
                $inventorytransaction->type = 3;
                $inventorytransaction->user = $driver->employeeid . " (".$driver->name.")";
                $inventorytransaction->date = date('Y-m-d H:i:s');
                $inventorytransaction->save();
            }
            if($data['type'] == 1){
                $invoicepayment = New InvoicePayment();
                $invoicepayment->invoice_id = $invoice->id;
                $invoicepayment->type = 1;
                $invoicepayment->customer_id = $invoice->customer_id;
                $invoicepayment->amount = $totalprice;
                $invoicepayment->status = 1;
                $invoicepayment->driver_id = $driver->id;
                $invoicepayment->approve_by = $driver->name;
                $invoicepayment->approve_at = date('Y-m-d H:i:s');
                $invoicepayment->save();
            }
            $task = Task::where('customer_id', $data['customer_id'])->where('driver_id',$driver->id)->update(['status' => 8]);
            DB::commit();
            $iv = Invoice::where('id',$invoice->id)->with('invoicedetail.product')->get()->first();
            
             
             try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$iv->customer_id.');');
                
                if($credit)
                {
                    $iv->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $iv->newcredit  = 0;
            }
            
            
           //$iv->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$iv->customer_id.');')[0]->credit,2);
            
            
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.invoice_add_successfully',
                'data' => $iv
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
      public function invoicepdf(Request $request)
	{
	    try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'invoice_id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            
            $id = $data['invoice_id'];
            
            
            $invoice = Invoice::where('id',$id)
            ->with('customer')
            ->with('driver')
            ->with('invoicedetail.product')
            ->first();
    
            if (empty($invoice)) {
                abort('404');
            }
    
            $min = 450;
            $each = 23;
            $height = (count($invoice['invoicedetail']) * $each) + $min;
    
            try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.$invoice->updated_at.'",'.$invoice->customer_id.');');
                
                if($credit)
                {
                    $invoice->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $invoice->newcredit  = 0;
            }
            $invoice->customer->groupcompany = DB::table('companies')
            ->where('companies.group_id',explode(',',$invoice->customer->group)[0])
            ->select('companies.*')
            ->first() ?? null;
            
              $pdf = Pdf::loadView('invoices.print', array(
                    'invoice' => $invoice
                ));
    
            $pdf->setPaper(array(0, 0, 300, $height), 'portrait')->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
    
            $invoiceFilename = 'invoice-' . $invoice->invoiceno . '.pdf';
            $path = 'invoices-pdf/' . $invoiceFilename;
            
            Storage::disk('public')->put($path, $pdf->output());
            $url = url($path);

            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.load_success',
                'data' => $url
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
        
	  
	}
	
	
	
     public function addpayment(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null,
                    'color_code' => ''
                ], 401);
            }
            //validation
            
            $validator = Validator::make($request->all(), [
                'date' => 'date_format:Y-m-d H:i:s',
                'customer_id' => 'required|numeric',
                'type' => 'required|numeric|gt:0|lt:6',
                'remark' => 'present|nullable|string',
                'amount' =>'required|numeric',
                
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null,
                ], 400);
            }
            $customer = Customer::where('id',$data['customer_id'])->first();
            if(empty($customer)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                    'data' => null,
                ], 400);
            }
            //process
            
            DB::beginTransaction();
            
            $invoicepayment = New InvoicePayment();
            if(isset($data['invoice_id'])){
                $invoicepayment->invoice_id = $data['invoice_id'];
            }
            
            $invoicepayment->type = $data['type'];
            $invoicepayment->customer_id = $data['customer_id'];
            $invoicepayment->amount = $data['amount'];
            $invoicepayment->status = 1;
            $invoicepayment->chequeno = $data['cheque_no'];
            $invoicepayment->driver_id = $driver->id;
            $invoicepayment->approve_by = $driver->name;
            $invoicepayment->approve_at = date('Y-m-d H:i:s');
            //$invoicepayment->created_at = $data['date'];
            $invoicepayment->save();
            
            DB::commit();
            $iv = InvoicePayment::where('id',$invoicepayment->id)->get()->first();
           
            $iv['payment_no'] = sprintf('PR%05d',$iv->id);
            
            
             try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$iv->customer_id.');');
                
                if($credit)
                {
                    $iv->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $iv->newcredit  = 0;
            }
            
           
           // $iv->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$iv->customer_id.');')[0]->credit,2);
           
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.invoice_add_successfully',
                'data' => $iv
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    public function paymentpdf(Request $request)
	{
	    try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'payment_id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            
            $id = $data['payment_id'];
            
            
            $invoice = InvoicePayment::where('id',$id)
                    ->with('customer')
                    ->first();
    
            if (empty($invoice)) {
                abort('404');
            }
    
            $min = 450;
            $each = 23;
    
            try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.$invoice->updated_at.'",'.$invoice->customer_id.');');
                
                if($credit)
                {
                    $invoice->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $invoice->newcredit  = 0;
            }
            
            $invoice->customer->groupcompany = DB::table('companies')
            ->where('companies.group_id',explode(',',$invoice->customer->group)[0])
            ->select('companies.*')
            ->first() ?? null;
            
            $pdf = Pdf::loadView('invoice_payments.print', array(
                'invoice' => $invoice
            ));

    
            $pdf->setPaper(array(0, 0, 300, $min), 'portrait')->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
            
            $invoiceFilename = 'payment-' . $invoice->id . '.pdf';
            $path = 'payments/' . $invoiceFilename;
            
            Storage::disk('public')->put($path, $pdf->output());
            $url = url($path);
            
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.load_success',
                'data' => $url
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
        
	  
	}
	
	
    public function getstock(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            //if(!empty($trip)){
            //    if($trip->type == 2){
            //        return response()->json([
            //            'result' => false,
            //            'message' => __LINE__.$this->message_separator.'Trip had not started',
            //            'data' => null
            //        ], 401);
            //    }
            //}else{
            //    return response()->json([
            //        'result' => false,
            //        'message' => __LINE__.$this->message_separator.'Trip had not started',
            //        'data' => null
            //    ], 401);
            //}
            //process
            $inventorybalance = InventoryBalance::where('lorry_id',$trip->lorry_id)
            ->leftjoin('products','products.id','=','inventory_balances.product_id')
            ->get(['inventory_balances.id','inventory_balances.quantity','inventory_balances.product_id','products.name'])->toarray();
            if(count($inventorybalance) == 0){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.no_stock_found',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.stock_found',
                    'data' => $inventorybalance
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    // public function getstock(Request $request){
    //     try{
    //         $data = $request->all();
    //         //check session
    //         $driver = Driver::where('session', $request->header('session'))->first();
    //         if(empty($driver)){
    //             return response()->json([
    //                 'result' => false,
    //                 'message' => __LINE__.$this->message_separator.'Invalid session',
    //                 'data' => null
    //             ], 401);
    //         }
    //         //validation
    //         $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
    //         if(!empty($trip)){
    //             if($trip->type == 2){
    //                 return response()->json([
    //                     'result' => false,
    //                     'message' => __LINE__.$this->message_separator.'Trip had not started',
    //                     'data' => null
    //                 ], 401);
    //             }
    //         }else{
    //             return response()->json([
    //                 'result' => false,
    //                 'message' => __LINE__.$this->message_separator.'Trip had not started',
    //                 'data' => null
    //             ], 401);
    //         }
    //         //process
    //         $inventorybalance = InventoryBalance::where('lorry_id',$trip->lorry_id)
    //         ->leftjoin('products','products.id','=','inventory_balances.product_id')
    //         ->get(['inventory_balances.id','inventory_balances.quantity','inventory_balances.product_id','products.name'])->toarray();
    //         if(count($inventorybalance) == 0){
    //             return response()->json([
    //                 'result' => false,
    //                 'message' => __LINE__.$this->message_separator.'No stock found',
    //                 'data' => null
    //             ], 200);
    //         }else{
    //             return response()->json([
    //                 'result' => true,
    //                 'message' => __LINE__.$this->message_separator.'Stock found',
    //                 'data' => $inventorybalance
    //             ], 200);
    //         }
    //     }
    //     catch(Exception $e){
    //         return response()->json([
    //             'result' => false,
    //             'message' => __LINE__.$this->message_separator.$e->getMessage(),
    //             'data' => null
    //         ], 500);
    //     }
    // }

    public function listotherdriver(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            //process
            $drivers = Trip::where('driver_id','!=',$trip->driver_id)
            ->select('driver_id','drivers.name','drivers.employeeid')
            ->groupby('driver_id','drivers.name','drivers.employeeid')
            ->havingRaw('(count(driver_id) % 2) > 0')
            ->leftjoin('drivers','drivers.id','=','trips.driver_id')
            ->get()->toarray();
            if(count($drivers) == 0){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.no_driver_found',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.driver_found',
                    'data' => $drivers
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function transferstock(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|numeric',
            'transferdetail' => 'present|array',
            'transferdetail.*.product_id' => 'required|numeric',
            'transferdetail.*.quantity' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        $todriver = Driver::where('id',$data['driver_id'])->first();
        if(empty($todriver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        $totrip = Trip::where('driver_id', $data['driver_id'])->orderby('date','desc')->first();
        if(!empty($totrip)){
            if($totrip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.selected_driver_trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.selected_driver_trip_had_not_started',
                'data' => null
            ], 400);
        }
        //process
        try{

            DB::beginTransaction();
            foreach($data['transferdetail'] as $td){
                $product = Product::where('id',$td['product_id'])->first();
                if(empty($product)){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.invalid_product',
                        'data' => null
                    ], 400);
                }
                $inventorytransfer = New InventoryTransfer();
                $inventorytransfer->date = date('Y-m-d H:i:s');
                $inventorytransfer->from_driver_id = $trip->driver_id;
                $inventorytransfer->from_lorry_id = $trip->lorry_id;
                $inventorytransfer->to_driver_id = $totrip->driver_id;
                $inventorytransfer->to_lorry_id = $totrip->lorry_id;
                $inventorytransfer->product_id = $td['product_id'];
                $inventorytransfer->quantity = $td['quantity'];
                $inventorytransfer->status = 1;
                $inventorytransfer->save();
            }
            DB::commit();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.pending_driver_accept_transfer',
                'data' => null
            ], 200);
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function gettransfer(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            //process
            $request = InventoryTransfer::where('from_driver_id', $trip->driver_id)
            ->where('date', '>=', date('Y-m-d 00:00:00'))
            ->with('product:id,name')
            ->with('todriver:id,name')
            ->orderby('date','desc')
            ->get(['id','date','status','quantity','product_id','to_driver_id'])
            ->toarray();
            $pending = InventoryTransfer::where('to_driver_id', $trip->driver_id)
            ->where('date', '>=', date('Y-m-d 00:00:00'))
            // ->where('status', 1)
            ->with('product:id,name')
            ->with('fromdriver:id,name')
            ->orderby('date','desc')
            ->get(['id','date','status','quantity','product_id','from_driver_id'])
            ->toarray();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.transfer_found',
                'data' => [
                    'request' => $request,
                    'pending' => $pending
                ]
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function updatetransfer(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'transfer_id' => 'required|numeric',
            'status' => 'required|numeric|gt:1|lt:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        // $inventorytransfer = InventoryTransfer::where('id', $data['transfer_id'])->where('to_driver_id',$driver->id)->first();
        $inventorytransfer = InventoryTransfer::where('id', $data['transfer_id'])->first();
        if(empty($inventorytransfer)){
            return response()->json([
               'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.transfer_not_found',
                'data' => null
            ], 400);
        }
        if($inventorytransfer->status == 2){
            return response()->json([
              'result' => false,
              'message' => __LINE__.$this->message_separator.'api.message.transfer_already_accepted',
                'data' => null
            ], 400);
        }
        if($inventorytransfer->status == 3){
            return response()->json([
              'result' => false,
              'message' => __LINE__.$this->message_separator.'api.message.transfer_already_rejected',
              'data' => null
            ], 400);
        }
        $fromdriver = Driver::where('id',$inventorytransfer->from_driver_id)->first();
        if(empty($fromdriver)){
            return response()->json([
              'result' => false,
              'message' => __LINE__.$this->message_separator.'api.message.from_driver_not_found',
                'data' => null
            ], 400);
        }
        $todriver = Driver::where('id',$inventorytransfer->to_driver_id)->first();
        if(empty($fromdriver)){
            return response()->json([
              'result' => false,
              'message' => __LINE__.$this->message_separator.'api.message.to_driver_not_found',
                'data' => null
            ], 400);
        }
        //process
        try{

            DB::beginTransaction();
            if($data['status'] == 3){
                $inventorytransfer->status = 3;
                $inventorytransfer->save();
                DB::commit();
                return response()->json([
                   'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.transfer_rejecet_successfully',
                    'data' => null
                ], 200);
            }
            if($data['status'] == 2){
                $inventorytransfer->status = 2;
                $inventorytransfer->save();
                 //from
                 $frominventorybalance = Inventorybalance::where('lorry_id',$inventorytransfer->from_lorry_id)
                 ->where('product_id',$inventorytransfer->product_id)->first();
                 if(empty($frominventorybalance)){
                     $newfrominventorybalance = New Inventorybalance();
                     $newfrominventorybalance->lorry_id = $inventorytransfer->from_lorry_id;
                     $newfrominventorybalance->product_id = $inventorytransfer->product_id;
                     $newfrominventorybalance->quantity = 0 - $inventorytransfer->quantity;
                     $newfrominventorybalance->save();
                 }else{
                     $frominventorybalance->quantity = $frominventorybalance->quantity - $inventorytransfer->quantity;
                     $frominventorybalance->save();
                 }
                 $frominventorytransaction = New InventoryTransaction();
                 $frominventorytransaction->lorry_id = $inventorytransfer->from_lorry_id;
                 $frominventorytransaction->product_id = $inventorytransfer->product_id;
                 $frominventorytransaction->quantity = $inventorytransfer->quantity * -1;
                 $frominventorytransaction->type = 4;
                 $frominventorytransaction->user = $fromdriver->employeeid . " (".$fromdriver->name.") => " . $todriver->employeeid . " (".$todriver->name.")";
                 $frominventorytransaction->date = date('Y-m-d H:i:s');
                 $frominventorytransaction->save();
                 //to
                 $toinventorybalance = Inventorybalance::where('lorry_id',$inventorytransfer->to_lorry_id)
                 ->where('product_id',$inventorytransfer->product_id)->first();
                 if(empty($toinventorybalance)){
                     $newtoinventorybalance = New Inventorybalance();
                     $newtoinventorybalance->lorry_id = $inventorytransfer->to_lorry_id;
                     $newtoinventorybalance->product_id = $inventorytransfer->product_id;
                     $newtoinventorybalance->quantity = $inventorytransfer->quantity;
                     $newtoinventorybalance->save();
                 }else{
                     $toinventorybalance->quantity = $toinventorybalance->quantity + $inventorytransfer->quantity;
                     $toinventorybalance->save();
                 }
                 $toinventorytransaction = New InventoryTransaction();
                 $toinventorytransaction->lorry_id = $inventorytransfer->to_lorry_id;
                 $toinventorytransaction->product_id = $inventorytransfer->product_id;
                 $toinventorytransaction->quantity = $inventorytransfer->quantity;
                 $toinventorytransaction->type = 4;
                 $toinventorytransaction->user = $fromdriver->employeeid . " (".$fromdriver->name.") => " . $todriver->employeeid . " (".$todriver->name.")";
                 $toinventorytransaction->date = date('Y-m-d H:i:s');
                 $toinventorytransaction->save();
                 DB::commit();
                 return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.transfer_accept_successfully',
                     'data' => null
                 ], 200);
            }
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getstocktransaction(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            if($data['date'] > date('Y-m-d H:i:s')){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.date_cannot_be_future_date',
                    'data' => null
                ], 400);
            }
            //process
            $inventorytransaction = InventoryTransaction::where('lorry_id',$trip->lorry_id)
            ->leftjoin('products','products.id','=','inventory_transactions.product_id')
            ->where('date','>=',$data['date'])
            ->where('date','<',date('Y-m-d', strtotime("+1 day", strtotime($data['date']))))
            ->orderby('date','desc')
            // ->select('lorry_id','product_id','quantity','type','date');
            ->select('inventory_transactions.id','inventory_transactions.quantity','inventory_transactions.type','inventory_transactions.date','products.name');

            $finalinventorytransaction = InventoryTransaction::where('lorry_id',$trip->lorry_id)
            ->leftjoin('products','products.id','=','inventory_transactions.product_id')
            ->where('date','<',$data['date'])
            ->groupby('inventory_transactions.product_id','products.id','products.name')
            // ->select('lorry_id','product_id',DB::raw('sum(quantity) as quantity'),DB::raw('0 as type'),DB::raw('"'.$data['date'].'" as date'))
            ->select(DB::raw('0 as id'),DB::raw('sum(inventory_transactions.quantity) as quantity'),DB::raw('0 as type'),DB::raw('"'.$data['date'].'" as date'),'products.name')
            ->union($inventorytransaction)
            ->orderby('date','desc')
            ->get()
            ->toarray();
            if(count($finalinventorytransaction) == 0){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.transaction_not_found',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.transaction_found',
                    'data' => $finalinventorytransaction
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function listalldriver(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            //process
            $driver = Driver::where('id','!=',$trip->driver_id)->get()->toarray();
            if(count($driver) == 0){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_driver',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.driver_found',
                    'data' => $driver
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    //NA
    public function getdrivertask(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json(['result' => false, 'message' => 'Session not found', 'data' => null], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json(['result' => false, 'message' => 'Trip had not started', 'data' => null], 400);
            }
        }else{
            return response()->json(['result' => false, 'message' => 'Trip had not started', 'data' => null], 400);
        }
        $messages = array(
            'driver_id.required' => 'Driver ID is required',
        );
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }
        $fromdriver = Driver::where('id',$data['driver_id'])->first();
        if(empty($fromdriver)){
            return response()->json(['result' => false,'message' => 'Driver not found', 'data' => null], 400);
        }
        //process
        $fromdrivertrip = Trip::where('driver_id', $fromdriver->id)->orderby('date','desc')->first();
        if(!empty($fromdrivertrip)){
            if($fromdrivertrip->type == 2){
                //Take from assign & invoice
                $assigns = Assign::where('driver_id', $fromdriver->id)
                ->orderby('sequence','asc')
                ->select('customer_id','sequence',DB::RAW('0 as invoice_id'));
                $task = Invoice::where('driver_id', $fromdriver->id)
                ->where('status',0)
                ->where('date',date('Y-m-d'))
                ->select('customer_id',DB::RAW('0 as sequence'),DB::RAW('id as invoice_id'))
                ->union($assigns)
                ->with('customer')
                ->get()->toarray();
                if(empty($task)){
                    return response()->json(['result' => false,'message' => 'Task not found', 'data' => null], 200);
                }else{
                    return response()->json(['result' => true,'message' => 'Task found', 'data' => $task], 200);
                }
            }else{
                //Take from task
                $task = Task::where('driver_id',$fromdriver->id)
                ->wherein('status',[0,1])
                ->select('customer_id','sequence','invoice_id')
                ->with('customer')
                ->get()->toarray();
                if(empty($task)){
                    return response()->json(['result' => false,'message' => 'Task not found', 'data' => null], 200);
                }else{
                    return response()->json(['result' => true,'message' => 'Task found', 'data' => $task], 200);
                }
            }
        }else{
            //Take from assign & invoice
            $assigns = Assign::where('driver_id', $fromdriver->id)
            ->orderby('sequence','asc')
            ->select('customer_id','sequence',DB::RAW('0 as invoice_id'));
            $task = Invoice::where('driver_id', $fromdriver->id)
            ->where('status',0)
            ->where('date',date('Y-m-d'))
            ->select('customer_id',DB::RAW('0 as sequence'),DB::RAW('id as invoice_id'))
            ->union($assigns)
            ->with('customer')
            ->get()->toarray();
            if(empty($task)){
                return response()->json(['result' => false,'message' => 'Task not found', 'data' => null], 200);
            }else{
                return response()->json(['result' => true,'message' => 'Task found', 'data' => $task], 200);
            }
        }
    }

    //NA
    public function pulldrivertask(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json(['result' => false, 'message' => 'Session not found', 'data' => null], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json(['result' => false, 'message' => 'Trip had not started', 'data' => null], 400);
            }
        }else{
            return response()->json(['result' => false, 'message' => 'Trip had not started', 'data' => null], 400);
        }
        $messages = array(
            'driver_id.required' => 'Driver ID is required',
            'transferdetail.*.customer_id.required' => 'Customer ID is required',
        );
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required',
            'transferdetail.*.customer_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }
        try{
            if(count($data['transferdetail']) == 0){
                return response()->json(['result' => false, 'message' => 'Invalid format, transfer detail is empty', 'data' => null], 400);
            }
        }
        catch(Exception $e){
            return response()->json(['result' => false, 'message' => 'Invalid format', 'data' => null], 400);
        }
        $fromdriver = Driver::where('id', $data['driver_id'])->first();
        if(empty($fromdriver)){
            return response()->json(['result' => false,'message' => 'Driver not found', 'data' => null], 400);
        }
        //process
        try{
            DB::beginTransaction();
            foreach($data['transferdetail'] as $key => $c){
                $customer = Customer::where('id',$c['customer_id'])->first();
                if(empty($customer)){
                    DB::rollback();
                    return response()->json(['result' => false,'message' => 'Customer not found', 'data' => null], 400);
                }else{
                    $fromdrivertrip = Trip::where('driver_id', $fromdriver->id)->orderby('date','desc')->first();
                    if(!empty($fromdrivertrip)){
                        if($fromdrivertrip->type == 2){
                            //take from assign & invoice
                            $invoice = Invoice::where('driver_id', $fromdriver->id)
                            ->where('status',0)
                            ->where('date',date('Y-m-d'))
                            ->where('customer_id',$customer->id)
                            ->get()->toarray();
                            if(empty($invoice)){
                                $newtask =  New Task();
                                $newtask->driver_id = $driver->id;
                                $newtask->customer_id = $customer->id;
                                $newtask->status = 0;
                                $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                                if(empty($sequence)){
                                    $sequence = 0;
                                }else{
                                    $sequence = $sequence->sequence;
                                }
                                $newtask->sequence =  $sequence + 1;
                                $newtask->date = date('Y-m-d');
                                $newtask->save();
                            }else{
                                foreach($invoice as $i){
                                    $newtask =  New Task();
                                    $newtask->driver_id = $driver->id;
                                    $newtask->customer_id = $customer->id;
                                    $newtask->invoice_id = $i['id'];
                                    $newtask->status = 0;
                                    $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                                    if(empty($sequence)){
                                        $sequence = 0;
                                    }else{
                                        $sequence = $sequence->sequence;
                                    }
                                    $newtask->sequence =  $sequence + 1;
                                    $newtask->date = date('Y-m-d');
                                    $newtask->save();
                                }
                            }
                        }else{
                            //take from task
                            $task = Task::where('driver_id',$fromdriver->id)
                            ->wherein('status',[0,1])
                            ->where('customer_id',$customer->id)->first();
                            $newtask =  New Task();
                            $newtask->driver_id = $driver->id;
                            $newtask->customer_id = $customer->id;
                            $newtask->status = 0;
                            $newtask->invoice_id = $task->invoice_id;
                            $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                            if(empty($sequence)){
                                $sequence = 0;
                            }else{
                                $sequence = $sequence->sequence;
                            }
                            $newtask->sequence =  $sequence + 1;
                            $newtask->date = date('Y-m-d');
                            $newtask->save();
                            $task->update(['status' => 9]);
                        }
                    }else{
                        //take from assign & invoice
                        $invoice = Invoice::where('driver_id', $fromdriver->id)
                        ->where('status',0)
                        ->where('date',date('Y-m-d'))
                        ->where('customer_id',$customer->id)
                        ->get()->toarray();
                        if(empty($invoice)){
                            $newtask =  New Task();
                            $newtask->driver_id = $driver->id;
                            $newtask->customer_id = $customer->id;
                            $newtask->status = 0;
                            $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                            if(empty($sequence)){
                                $sequence = 0;
                            }else{
                                $sequence = $sequence->sequence;
                            }
                            $newtask->sequence =  $sequence + 1;
                            $newtask->date = date('Y-m-d');
                            $newtask->save();
                        }else{
                            foreach($invoice as $i){
                                $newtask =  New Task();
                                $newtask->driver_id = $driver->id;
                                $newtask->customer_id = $customer->id;
                                $newtask->invoice_id = $i['id'];
                                $newtask->status = 0;
                                $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                                if(empty($sequence)){
                                    $sequence = 0;
                                }else{
                                    $sequence = $sequence->sequence;
                                }
                                $newtask->sequence =  $sequence + 1;
                                $newtask->date = date('Y-m-d');
                                $newtask->save();
                            }
                        }
                    }

                }
            }
            DB::commit();
            return response()->json(['result' => true, 'message' => 'Pulled task successfully', 'data' => null], 200);
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json(['result' => false,'message' => $e->getMessage(), 'data' => null], 400);
        }
    }

    public function pushdrivertask(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|numeric',
            'transferdetail' => 'present|array',
            'transferdetail.*.task_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        $todriver = Driver::where('id', $data['driver_id'])->first();
        if(empty($todriver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_driver',
                'data' => null
            ], 400);
        }
        //process
        try{
            DB::beginTransaction();
            foreach($data['transferdetail'] as $key => $c){
                $task = Task::where('id',$c['task_id'])->first();
                if(empty($task)){
                    DB::rollback();
                    return response()->json([
                       'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.invalid_task',
                        'data' => null
                    ], 400);
                }
                if($task->status == 9){
                    DB::rollback();
                    return response()->json([
                       'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_cancelled',
                        'data' => null
                    ], 400);
                }
                if($task->status == 8){
                    DB::rollback();
                    return response()->json([
                       'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_completed',
                        'data' => null
                    ], 400);
                }
                $sequence = Task::where('driver_id',$todriver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                if(empty($sequence)){
                    $sequence = 0;
                }else{
                    $sequence = $sequence->sequence;
                }
                $task->sequence = $sequence + 1;
                $task->driver_id = $todriver->id;
                $task->status = 0;
                $task->based = 0;
                $task->trip_id = null;
                $task->save();

                $tasktransfer = new TaskTransfer();
                $tasktransfer->date = date("Y-m-d H:i:s");
                $tasktransfer->from_driver_id = $driver->id;
                $tasktransfer->to_driver_id = $todriver->id;
                $tasktransfer->task_id = $c['task_id'];
                $tasktransfer->save();
            }
            DB::commit();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.push_task_successfully',
                'data' => null
            ], 200);
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function listtranfer(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        //process
        try{
            $tasktransfer = TaskTransfer::where('from_driver_id',$driver->id)
            ->where('date', '>=', date('Y-m-d 00:00:00'))
            ->with('fromdriver:id,name')
            ->with('todriver:id,name')
            ->with('task.customer')
            ->get()->toArray();
            if(!empty($tasktransfer)){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.task_transfer_found',
                    'data' => $tasktransfer
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.task_transfer_not_found',
                    'data' => null
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function dashboard_bk(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            if($data['date'] > date('Y-m-d H:i:s')){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.date_cannot_be_future_date',
                    'data' => null
                ], 400);
            }
            //process
            $sales = DB::Select('select sum(a.totalprice) as sales from(select i.id,sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' group by i.id) a')[0]->sales;
            $cash = DB::Select('select coalesce(sum(coalesce(amount,0)),0) as cash from invoice_payments where type = 1 and status = 1 and driver_id = '.$driver->id.' and approve_at >= "'.$data['date'].'" and approve_at < "'.date('Y-m-d', strtotime("+1 day", strtotime($data['date']))).'";')[0]->cash;
            // $credit = DB::select('select sum(a.totalprice) as credit from ( select i.id,sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id left join invoice_payments ip on ip.invoice_id = i.id where i.status = 1 and i.date = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and ip.id is null group by i.id ) a')[0]->credit;
            $credit = DB::select('select sum(a.totalprice) as credit from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and i.paymentterm = 2 group by i.id ) a')[0]->credit;
            $productsold = DB::Select('select sum(id.quantity) as productsold from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id)[0]->productsold;
            $solddetail = DB::select('select p.name, sum(id.quantity) as quantity from invoices i left join invoice_details id on id.invoice_id = i.id left join products p on p.id = id.product_id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' group by id.product_id, p.id, p.name');
            $trip = DB::select('select t.id, d.name as driver_name, k.name as kelindan_name, l.lorryno from trips t left join drivers d on d.id = t.driver_id left join kelindans k on k.id = t.kelindan_id left join lorrys l on l.id = t.lorry_id where t.driver_id = '.$driver->id.' and t.type = 1 and t.date >= "'.$data['date'].'" and t.date < "'.$data['date'].' 23:59:59"');
            // $trip = Trip::where('driver_id', $driver->id)
            // ->where('date','>=',$data['date'].' 00:00:00')
            // ->where('date','<',$data['date'].' 23:59:59')
            // ->where('type',1)
            // ->with('driver')
            // ->with('kelindan')
            // ->with('lorry')
            // ->get()
            // ->toArray();
            $result = [
                'sales' => round($sales,2),
                'cash' => round($cash,2),
                'credit' => round($credit,2),
                'productsold' => [
                    'total_quantity' =>round($productsold,2),
                    'details' =>$solddetail
                ],
                'trip' => $trip
            ];
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.get_dashboard_successfully',
                'data' => $result
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    

    public function getAllLanguages(Request $request)
    {
        $data = $request->all();
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        $languages = MobileTranslationVersion::with('language')->get();

        $translations = [];

        foreach ($languages as $languageVersion) {
            $translations[] = [
                'language' => $languageVersion->language->name, 
                'code'     => $languageVersion->language->code,  
                'version'  => $languageVersion->version,
            ];
        }
        return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator,
                'data' => $translations
            ], 200);
    }

    public function getTranslations(Request $request)
    {
        $data = $request->all();
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]); 
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        $code = $data['code'];
        $language = Language::where('code', $code)->first();

        if(empty($language)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'Invalid Language Code',
                    'data' => null
                ], 401);
            }
        $version = MobileTranslationVersion::where('language_id', $language->id)->first();
        $translations = MobileTranslation::where('language_id', $language->id)
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        $result = [
            'version' => $version->version,
            'translation' => $translations
        ];

        return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.language_update_successfully',
                'data' => $result
            ], 200);
       
    }  
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                                //New APIs//
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function getsalesorderFields(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        // Get driver's assigned customer group
        $assign = Assign::where('driver_id', $driver->id)->first();
        
        if (!$assign || !$assign->customer_group_id) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'No customer group assigned to driver',
                'data' => null
            ], 200);
        }

        // Get customer group and customer IDs
        $customerGroup = CustomerGroup::where('id', $assign->customer_group_id)->first();
        
        if (!$customerGroup) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Customer group not found',
                'data' => null
            ], 200);
        }

        // Get assigned customer IDs (convert string or array to array)
        $assignedCustomers = $customerGroup->customer_ids ?? [];

        // If it's a JSON string, decode it
        if (is_string($assignedCustomers)) {
            $assignedCustomers = json_decode($assignedCustomers, true);
        }

        // Ensure it's an array
        if (!is_array($assignedCustomers)) {
            $assignedCustomers = [];
        }

        // Now extract just the customer IDs if needed
        $assignedCustomerIds = [];

        foreach ($assignedCustomers as $customer) {
            // Handle both array format and object format
            if (is_array($customer)) {
                if (isset($customer['id']) && is_numeric($customer['id'])) {
                    $assignedCustomerIds[] = (int) $customer['id'];   
                    
                }
            } elseif (is_object($customer)) {
                if (isset($customer->id) && is_numeric($customer->id)) {
                    $assignedCustomerIds[] = (int) $customer->id;
                    
                }
            } elseif (is_numeric($customer)) {
                // Handle legacy format where it might just be a numeric ID
                $assignedCustomerIds[] = (int) $customer;
            }
        }

        // Filter out empty/null values and remove duplicates
        $assignedCustomerIds = array_unique(array_filter($assignedCustomerIds, function($id) {
            return !empty($id) && is_numeric($id);
        }));

        // Get customers only from assigned IDs
        $customers = Customer::whereIn('id', $assignedCustomerIds)
            ->orderBy('company')
            ->get(['id', 'company', 'paymentterm'])
            ->keyBy('id');

        // If no customers found, return error
        if ($customers->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'No customers found for the assigned customer group',
                'data' => null
            ], 200);
        }

        $driverProductIds = InventoryBalance::where('driver_id', $driver->id)
        ->pluck('product_id')
        ->toArray();       
        
        if (!empty($driverProductIds)) {
            $products = Product::whereIn('id', $driverProductIds)
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $products = collect([]);
        }

        // Prepare options
        $customerOptions = [];
        foreach ($customers as $customer) {
            $customerOptions[] = [
                'id' => $customer->id,
                'name' => $customer->company
            ];
        }

        $productOptions = [];
        foreach ($products as $product) {
            $productOptions[] = [
                'id' => $product->id,
                'name' => $product->name
            ];
        }

        // Define fields
        $fields = [
            [
                'key' => 'invoiceno',
                'label' => 'Order No',
                'type' => 'text',
                'value' => \App\Models\SalesInvoice::getNextInvoiceNumber($driver->id),
                'required' => true,
                'placeholder' => 'Auto-generated',
            ],
            [
                'key' => 'date',
                'label' => 'Date',
                'type' => 'date',
                'required' => true,
                'placeholder' => 'DD-MM-YYYY',
            ],
            [
                'key' => 'customer_id',
                'label' => 'Customer',
                'type' => 'select',
                'required' => true,
                'placeholder' => 'Pick a Customer...',
                'options' => $customerOptions
            ],
            [
                'key' => 'remark',
                'label' => 'Remark',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Enter remarks',
                'maxlength' => 255
            ],
            [
                'key' => 'items',
                'label' => 'Order Items',
                'type' => 'repeater',
                'required' => true,
                'fields' => [
                    [
                        'key' => 'product_id',
                        'label' => 'Product',
                        'type' => 'select',
                        'required' => true,
                        'placeholder' => 'Select Product...',
                        'options' => $productOptions
                    ],
                    [
                        'key' => 'quantity',
                        'label' => 'Quantity',
                        'type' => 'number',
                        'required' => true,
                        'placeholder' => '0.00'
                    ],
                    [
                        'key' => 'price',
                        'label' => 'Price',
                        'type' => 'number',
                        'required' => true,
                        'placeholder' => '0.00'
                    ]
                ],
                'add_button_label' => 'Add Item',
                'remove_button_label' => 'Remove',
                'min_rows' => 1
            ]
        ];

        return response()->json([
            'result' => true,
            'message' => '' . __LINE__ . $this->message_separator . 'Sales order fields retrieved successfully',
            'data' => $fields
        ], 200);
    }

    public function createSalesOrder(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        // Get driver's assigned customer IDs for validation
        $assign = Assign::where('driver_id', $driver->id)->first();
        
        if (!$assign || !$assign->customer_group_id) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'No customer group assigned to driver',
                'data' => null
            ], 200);
        }

        $customerGroup = CustomerGroup::where('id', $assign->customer_group_id)->first();
        
        if (!$customerGroup) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Customer group not found',
                'data' => null
            ], 200);
        }

        $assignedCustomerIds = $customerGroup->customer_ids ?? [];

        // Handle different formats
        if (is_string($assignedCustomerIds)) {
            if (str_contains($assignedCustomerIds, ',')) {
                $assignedCustomerIds = explode(',', $assignedCustomerIds);
                $assignedCustomerIds = array_map('trim', $assignedCustomerIds);
            } else {
                $assignedCustomerIds = json_decode($assignedCustomerIds, true) ?? [$assignedCustomerIds];
            }
        }

        // Extract IDs from array - handle both nested and flat formats
        $customerIds = [];
        foreach ((array)$assignedCustomerIds as $item) {
            if (is_array($item) && isset($item['id'])) {
                // Handle nested array format: [['id' => 1, 'sequence' => 1], ...]
                $customerIds[] = $item['id'];
            } elseif (is_numeric($item)) {
                // Handle flat array format: [1, 2, 3]
                $customerIds[] = $item;
            }
        }

        // Filter and reindex
        $customerIds = array_values(array_filter($customerIds, 'is_numeric'));

        // Prepare validation rules
        $validationRules = [
            'invoiceno' => 'required|string|max:255|unique:sales_invoices,invoiceno',
            'date' => 'required|date_format:d-m-Y',
            'customer_id' => [
                'required',
                'exists:customers,id',
                function ($attribute, $value, $fail) use ($customerIds) {
                    if (!in_array($value, $customerIds)) {
                        $fail('The selected customer is not assigned to you.');
                    }
                },
            ],
            'remark' => 'nullable|string|max:255',
            'chequeno' => 'nullable|string|max:20',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:0.01',
            'details.*.price' => 'required|numeric|min:0'
        ];

        // Custom validation messages
        $validationMessages = [
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'details.required' => 'At least one order item is required.',
            'details.*.product_id.required' => 'Product is required for all items.',
            'details.*.product_id.exists' => 'Selected product does not exist.',
            'details.*.quantity.required' => 'Quantity is required for all items.',
            'details.*.quantity.min' => 'Quantity must be at least 0.01.',
            'details.*.price.required' => 'Price is required for all items.',
            'details.*.price.min' => 'Price must be at least 0.'
        ];

        // Validate request data
        $validator = Validator::make($request->all(), $validationRules, $validationMessages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Validation failed',
                'errors' => $validator->errors()->toArray(),
                'data' => null
            ], 200);
        }

        // $insufficientProducts = [];
        // $details = $request->input('details', []);
        
        // if (!empty($details)) {
        //     foreach ($details as $index => $detail) {
        //         $productId = $detail['product_id'];
        //         $quantityNeeded = $detail['quantity'];
                
        //         // Get product name for error message
        //         $product = Product::find($productId);
                
        //         // Check inventory balance
        //         $inventoryBalance = InventoryBalance::where('driver_id', $driver->id)
        //             ->where('product_id', $productId)
        //             ->first();
                
        //         if (!$inventoryBalance) {
        //             // No inventory record found for this product
        //             $insufficientProducts[] = [
        //                 'product_id' => $productId,
        //                 'product_name' => $product ? $product->name : 'Unknown Product',
        //                 'required_quantity' => $quantityNeeded,
        //                 'available_quantity' => 0,
        //                 'error' => 'No inventory record found'
        //             ];
        //         } elseif ($inventoryBalance->quantity < $quantityNeeded) {
        //             // Insufficient quantity
        //             $insufficientProducts[] = [
        //                 'product_id' => $productId,
        //                 'product_name' => $product ? $product->name : 'Unknown Product',
        //                 'required_quantity' => $quantityNeeded,
        //                 'available_quantity' => $inventoryBalance->quantity,
        //                 'error' => 'Insufficient inventory'
        //             ];
        //         }
        //     }
        // }

        // // If there are insufficient products, return error
        // if (!empty($insufficientProducts)) {
        //     return response()->json([
        //         'result' => false,
        //         'message' => __LINE__ . $this->message_separator . 'Insufficient inventory balance',
        //         'errors' => [
        //             'inventory' => ['Some products have insufficient inventory']
        //         ],
        //         'insufficient_products' => $insufficientProducts,
        //         'data' => null
        //     ], 200);
        // }
        
        $customer = Customer::where('id', $request->input('customer_id'))->first();
        DB::beginTransaction(); // Start transaction

        try {
            $input = $request->all();

            // Convert date format
            $input['date'] = Carbon::createFromFormat('d-m-Y', $input['date'])->format('Y-m-d');
            
            // Handle invoice number generation
            if (empty($input['invoiceno']) || $input['invoiceno'] == 'SYSTEM GENERATED IF BLANK') {
                // Generate new invoice number with driver ID
                $input['invoiceno'] = SalesInvoice::getNextInvoiceNumber($driver->id);
            } else {
                // Check if the provided invoice number already exists
                if (SalesInvoice::invoiceNumberExists($input['invoiceno'])) {
                    // If exists, generate a new one with driver ID
                    $input['invoiceno'] = SalesInvoice::getNextInvoiceNumber($driver->id);
                }
            }
            
            // Set driver information
            $input['created_by'] = $driver->id;
            $input['is_driver'] = true; // Mark as created by driver
            
            // Set default status
            $input['status'] = SalesInvoice::STATUS_PENDING;

            // Calculate total
            $total = 0;
            foreach ($input['details'] as $detail) {
                $total += ($detail['quantity'] * $detail['price']);
            }

            $input['total'] = $total;
            $input['customer_id'] = $customer->id;
            $input['paymentterm'] = $customer->paymentterm ;
            $input['trip_id'] = $driver->trip_id;
            $input['driver_id'] = $driver->id;

            // Create sales invoice
            $salesInvoice = SalesInvoice::create($input);

            // Create sales invoice details
            if (isset($input['details']) && is_array($input['details'])) {
                foreach ($input['details'] as $detail) {
                    SalesInvoiceDetails::create([
                        'sales_invoice_id' => $salesInvoice->id,
                        'product_id' => $detail['product_id'],
                        'quantity' => $detail['quantity'],
                        'price' => $detail['price'],
                        'totalprice' => $detail['quantity'] * $detail['price']
                    ]);
                }
            }

            

            DB::commit(); // Commit transaction if everything is successful

            // Prepare response data
            $responseData = [
                'id' => $salesInvoice->id,
                'invoiceno' => $salesInvoice->invoiceno,
                'date' => Carbon::parse($salesInvoice->date)->format('d-m-Y'),
                'customer_id' => $salesInvoice->customer_id,
                'total' => $salesInvoice->total,
                'status' => $salesInvoice->getStatusTextAttribute(),
                'remark' => $salesInvoice->remark,
                'created_at' => $salesInvoice->created_at->format('Y-m-d H:i:s'),
                'items' => $salesInvoice->salesInvoiceDetails->map(function($detail) {
                    return [
                        'product_id' => $detail->product_id,
                        'product_name' => $detail->product->name ?? 'N/A',
                        'quantity' => $detail->quantity,
                        'price' => $detail->price,
                        'total' => $detail->totalprice
                    ];
                })->toArray()
            ];

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Sales order created successfully',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            
            // Log error for debugging
            \Log::error('Sales order creation failed: ' . $e->getMessage(), [
                'driver_id' => $driver->id ?? 'N/A',
                'request_data' => $request->all()
            ]);

            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error creating sales order: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function cancelSalesOrder(Request $request)
    {
        $id = $request->input('id');
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        // Find sales order
        $salesOrder = SalesInvoice::find($id);

        if (!$salesOrder) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Sales order not found',
                'data' => null
            ], 200);
        }

        $isDriverOrder = $salesOrder->is_driver && $salesOrder->created_by == $driver->id;
        if (!$isDriverOrder) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'You are not authorized to cancel this order',
                'data' => []
            ], 200);
        }

        // Check if already cancelled
        if ($salesOrder->status == SalesInvoice::STATUS_CANCELLED) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Sales order is already cancelled',
                'data' => null
            ], 200);
        }

        // Check if already convert to invoice 
        if ($salesOrder->status == SalesInvoice::STATUS_CONVERTED_TO_INVOICE) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Sales order is already converted to Invoice, you are not allow to cancel this Sales Order',
                'data' => null
            ], 200);
        }

        try {
            // Update status to cancelled
            $salesOrder->status = SalesInvoice::STATUS_CANCELLED;
            $salesOrder->save();

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Sales order cancelled successfully',
                'data' => [
                    'id' => $salesOrder->id,
                    'invoiceno' => $salesOrder->invoiceno,
                    'status' => $salesOrder->getStatusTextAttribute(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error cancelling sales order: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function getDriverSalesInvoices(Request $request, $customer_id = null)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        
        try {
            // Start building the query
            $query = SalesInvoice::where('is_driver', true)
                ->where('created_by', $driver->id)
                ->where('status', '=', SalesInvoice::STATUS_PENDING);

            // Apply customer filter if customer_id is provided
            if ($customer_id) {
                $query->where('customer_id', $customer_id);
            }

            // Get sales invoices
            $salesInvoices = $query->with(['customer:id,company,phone,paymentterm', 'salesInvoiceDetails.product:id,name'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Format the response
            $formattedInvoices = $salesInvoices->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoiceno' => $invoice->invoiceno,
                    'date' => $invoice->date,
                    'customer_id' => $invoice->customer_id,
                    'customer' => [
                        'id' => $invoice->customer_id,
                        'name' => $invoice->customer->company ?? 'N/A',
                        'paymentterm' => $invoice->customer->paymentterm ?? '',
                        'phone' => $invoice->customer->phone ?? '',
                    ],                    
                    'paymentterm' => $invoice->paymentterm,
                    'status' => $invoice->getStatusTextAttribute(),
                    'remark' => $invoice->remark,
                    'total' => number_format($invoice->total, 2),
                    'is_driver' => $invoice->is_driver,
                    'created_by' => $invoice->created_by,
                    'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $invoice->updated_at->format('Y-m-d H:i:s'),
                    'items_count' => $invoice->salesInvoiceDetails->count(),
                    'items' => $invoice->salesInvoiceDetails->map(function($detail) {
                        return [
                            'product_id' => $detail->product_id,
                            'product_name' => optional($detail->product)->name ?? 'N/A',
                            'quantity' => (float) $detail->quantity,
                            'price' => (float) $detail->price,
                            'total' => (float) $detail->totalprice,
                            'total_formatted' => number_format($detail->totalprice, 2)
                        ];
                    })->toArray(),
                    'pdf_url' => $this->getSalesInvoicepdf($invoice->id)
                ];
            });

            // Prepare response message based on filter
            $message = $customer_id 
                ? 'Sales invoices for customer retrieved successfully'
                : 'All sales invoices retrieved successfully';

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . $message,
                'data' => [
                    'count' => $salesInvoices->count(),
                    'invoices' => $formattedInvoices->toArray()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error retrieving sales invoices: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function getSalesOrderById(Request $request, $id)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        try {
            // Get sales invoice with proper authorization check
            $salesInvoice = SalesInvoice::where('is_driver', true)
                ->where('created_by', $driver->id)
                ->where('id', $id)
                ->with([
                    'customer:id,company,address,phone,paymentterm',
                    'salesInvoiceDetails.product:id,name,code'
                ])
                ->first();

            if (!$salesInvoice) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Sales order not found or not authorized',
                    'data' => null
                ], 200);
            }

            // Format the response
            $formattedInvoice = [
                'id' => $salesInvoice->id,
                'invoiceno' => $salesInvoice->invoiceno,
                'date' => $salesInvoice->date, // Already formatted in getDateAttribute
                'customer_id' => $salesInvoice->customer_id,
                'customer' => [
                    'id' => $salesInvoice->customer_id,
                    'name' => $salesInvoice->customer->company ?? 'N/A',
                    'paymentterm' => $salesInvoice->customer->paymentterm ?? '',
                    'phone' => $salesInvoice->customer->phone ?? '',
                ],
                'paymentterm' => $salesInvoice->paymentterm,
                'status' => $salesInvoice->getStatusTextAttribute(),
                'remark' => $salesInvoice->remark,
                'total' => number_format($salesInvoice->total, 2),
                'is_driver' => $salesInvoice->is_driver,
                'created_by' => $salesInvoice->created_by,
                'created_at' => $salesInvoice->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $salesInvoice->updated_at->format('Y-m-d H:i:s'),
                'items_count' => $salesInvoice->salesInvoiceDetails->count(),
                'items' => $salesInvoice->salesInvoiceDetails->map(function($detail) {
                    return [
                        'id' => $detail->id,
                        'product_id' => $detail->product_id,
                        'product_name' => optional($detail->product)->name ?? 'N/A',
                        'product_code' => optional($detail->product)->code ?? 'N/A',
                        'quantity' => (float) $detail->quantity,
                        'price' => (float) $detail->price,
                        'total' => (float) $detail->totalprice,
                        'total_formatted' => number_format($detail->totalprice, 2)
                    ];
                })->toArray(),
                'pdf_url' => $this->getSalesInvoicepdf($salesInvoice->id)
            ];

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Sales order retrieved successfully',
                'data' => $formattedInvoice
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error retrieving sales order: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function getSalesInvoicepdf($invoice_id)
    {
        try {

            $salesInvoice = SalesInvoice::where('id', $invoice_id)
                ->with(['customer', 'salesInvoiceDetails.product', 'createdByUser', 'createdByDriver'])
                ->first();

            if (empty($salesInvoice)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Invoice not found',
                    'data' => null
                ], 200);
            }

            $min = 450;
            $each = 23;
            $height = (count($salesInvoice['salesInvoiceDetails']) * $each) + $min;
            $creator = $salesInvoice->creator;
            
            $pdf = Pdf::loadView('sales_invoices.print', array(
                'salesInvoice' => $salesInvoice,
                'creatorName' => $creator->name
            ));
            
            $pdf->setPaper(array(0, 0, 300, $height), 'portrait')
                ->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
            
            return base64_encode($pdf->output());
            
        } catch (Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function convertSalesInvoice(Request $request, $id)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        if($driver->trip_id == NULL ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }

        try {
            $salesInvoice = SalesInvoice::with(['createdByUser', 'createdByDriver', 'customer'])->find($id);

            if (empty($salesInvoice)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Sales Order not found',
                    'data' => null
                ], 200);
            }
            if ($salesInvoice->salesInvoiceDetails->isEmpty()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Sales Order Item not found',
                    'data' => null
                ], 200);
            }

            // Check if driver created this invoice
            if (!$salesInvoice->is_driver || $salesInvoice->created_by != $driver->id) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'You are not authorized to convert this sales invoice',
                    'data' => null
                ], 200);
            }

            // Check if can be converted
            if (!$salesInvoice->canBeConvertedToInvoice()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Cannot convert this sales order. Status: ' . $salesInvoice->getStatusTextAttribute(),
                    'data' => null
                ], 200);
            }

            $insufficientProducts = [];

            if($request->input('details')){
                $details = $request->input('details');
            }else{
                $details = $salesInvoice->salesInvoiceDetails;
            }

            if (!empty($details)) {
                foreach ($details as $index => $detail) {
                    $productId = $detail['product_id'];
                    $quantityNeeded = $detail['quantity'];
                    
                    // Get product name for error message
                    $product = Product::find($productId);
                    
                    // Check inventory balance
                    $inventoryBalance = InventoryBalance::where('driver_id', $driver->id)
                        ->where('product_id', $productId)
                        ->first();
                    
                    if (!$inventoryBalance) {
                        // No inventory record found for this product
                        $insufficientProducts[] = [
                            'product_id' => $productId,
                            'product_name' => $product ? $product->name : 'Unknown Product',
                            'required_quantity' => $quantityNeeded,
                            'available_quantity' => 0,
                            'error' => 'No inventory record found'
                        ];
                    } elseif ($inventoryBalance->quantity < $quantityNeeded) {
                        // Insufficient quantity
                        $insufficientProducts[] = [
                            'product_id' => $productId,
                            'product_name' => $product ? $product->name : 'Unknown Product',
                            'required_quantity' => $quantityNeeded,
                            'available_quantity' => $inventoryBalance->quantity,
                            'error' => 'Insufficient inventory'
                        ];
                    }
                }
            }

            // If there are insufficient products, return error
            if (!empty($insufficientProducts)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Insufficient inventory balance',
                    'errors' => [
                        'inventory' => ['Some products have insufficient inventory']
                    ],
                    'insufficient_products' => $insufficientProducts,
                    'data' => null
                ], 200);
            }

            // Route based on payment term
            if ($salesInvoice->paymentterm == 'Cash') {
                return $this->convertWithPayment($request, $salesInvoice, $driver ,$details);
            } else {
                // For Credit, Cheque, Online BankIn, E-wallet, etc.
                return $this->convertToInvoiceOnly($salesInvoice, $driver, $details);
            }
            

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    /**
     * Convert to invoice with payment proof (for Cash payments)
     */
    private function convertWithPayment(Request $request, $salesInvoice, $driver , $details)
    {
        // Validate request for cash payment
        $validator = Validator::make($request->all(), [
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,gif|max:5120',
            'amount' => 'required|numeric',
            'remark' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Validation failed',
                'errors' => $validator->errors()->toArray(),
                'data' => null
            ], 200);
        }

        DB::beginTransaction();

        try {
            // Convert to invoice
            $invoice = $salesInvoice->convertToInvoice($driver->id, $details);
            
            if (!$invoice) {
                DB::rollBack();
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Failed to convert sales order',
                    'data' => null
                ], 200);
            }

            // Handle attachment upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $attachmentPath = $file->storeAs('invoice_payments', $fileName, 'public');
            }

            // Create APPROVED invoice payment record for cash
            $invoicePayment = new InvoicePayment();
            $invoicePayment->invoice_id = $invoice->id;
            $invoicePayment->type = $salesInvoice->paymentterm; // Should be 'Cash'
            $invoicePayment->customer_id = $salesInvoice->customer_id;
            $invoicePayment->amount = $request->amount;
            $invoicePayment->status = 1; // Approved for cash payment
            
            if ($attachmentPath) {
                $invoicePayment->attachment = $attachmentPath;
            }
            
            // Set driver information (since this is called from driver API)
            $invoicePayment->driver_id = $driver->id;
            $invoicePayment->user_id = null; // Created by driver, not admin user
            
            $invoicePayment->approve_by = $driver->name ?? 'Driver';
            $invoicePayment->approve_at = now(); // Approved immediately for cash
            $invoicePayment->remark = $request->remark ?? 'Cash payment with proof';
            $invoicePayment->save();

            DB::commit();

            $details = $salesInvoice->salesInvoiceDetails;
             // Create inventory transactions and deduct driver inventory balance with the invoice items
            if (!empty($details)) {
                foreach ($details as $detail) {
                    $productId = $detail->product_id;
                    $quantity = $detail->quantity;
                    
                    // 1. Create inventory transaction record
                    try {
                        InventoryTransaction::create([
                            $driver->id,
                            $productId,
                            $quantity,
                            InventoryTransaction::TYPE_STOCK_OUT,
                           'Sales order converted to invoice: ' . $invoice->invoiceno,
                            $invoice->id,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to create inventory transaction for sales order conversion', [
                            'error' => $e->getMessage(),
                            'driver_id' => $driver->id,
                            'product_id' => $productId,
                            'invoice_id' => $invoice->id
                        ]);
                    }
                    
                    // 2. Update inventory balance for each product
                    try {
                        $inventoryBalance = InventoryBalance::where('driver_id', $driver->id)
                            ->where('product_id', $productId)
                            ->first();
                        
                        if ($inventoryBalance) {
                            $inventoryBalance->quantity -= $quantity;
                            $inventoryBalance->save();
                        } else {
                            // Create new record (shouldn't normally happen)
                            InventoryBalance::create([
                                'driver_id' => $driver->id,
                                'product_id' => $productId,
                                'quantity' => -$quantity
                            ]);
                            
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to update inventory balance during sales order conversion', [
                            'error' => $e->getMessage(),
                            'driver_id' => $driver->id,
                            'product_id' => $productId,
                            'quantity' => $quantity
                        ]);
                    }
                }
            }

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Sales order converted successfully with payment proof',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoiceno,
                    // 'credit_amount' => $driver->credit_amount,
                    'payment_status' => 'approved',
                    'payment_amount' => $request->amount,
                    'payment_type' => 'Cash'
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error converting with payment: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    /**
     * Convert to invoice only (for Credit payments)
     */
    private function convertToInvoiceOnly($salesInvoice, $driver, $details)
    {
        DB::beginTransaction();

        try {
            // Convert to invoice (this only creates the invoice, not payment)
            $invoice = $salesInvoice->convertToInvoice($driver->id, $details);

            if (!$invoice) {
                DB::rollBack();
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Failed to convert sales order',
                    'data' => null
                ], 200);
            }

            // Update driver credit amount (for credit sales)
            // $driver->credit_amount = ($driver->credit_amount ?? 0) + $salesInvoice->total;
            // $driver->save();

            DB::commit();

            $details = $salesInvoice->salesInvoiceDetails;
            // Create inventory transactions and deduct driver inventory balance with the invoice items
            if (!empty($details)) {
                foreach ($details as $detail) {
                    $productId = $detail->product_id;
                    $quantity = $detail->quantity;
                    
                    // 1. Create inventory transaction record
                    try {
                        InventoryTransaction::create([
                            $driver->id,
                            $productId,
                            $quantity,
                            InventoryTransaction::TYPE_STOCK_OUT,
                            'Sales order converted to invoice: ' . $invoice->invoiceno,
                            $invoice->id,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to create inventory transaction for credit sales order conversion', [
                            'error' => $e->getMessage(),
                            'driver_id' => $driver->id,
                            'product_id' => $productId,
                            'invoice_id' => $invoice->id
                        ]);
                    }
                    
                    // 2. Update inventory balance for each product
                    try {
                        $inventoryBalance = InventoryBalance::where('driver_id', $driver->id)
                            ->where('product_id', $productId)
                            ->first();
                        
                        if ($inventoryBalance) {
                            $inventoryBalance->quantity -= $quantity;
                            $inventoryBalance->save();
                        } else {
                            // Create new record (shouldn't normally happen)
                            InventoryBalance::create([
                                'driver_id' => $driver->id,
                                'product_id' => $productId,
                                'quantity' => -$quantity
                            ]);
                            
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to update inventory balance during credit sales order conversion', [
                            'error' => $e->getMessage(),
                            'driver_id' => $driver->id,
                            'product_id' => $productId,
                            'quantity' => $quantity
                        ]);
                    }
                }
            }

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Sales order converted successfully for credit',
                'data' => [
                    'invoice_no' => $invoice->invoiceno,
                    // 'credit_amount' => $driver->credit_amount,
                    'payment_amount' => $salesInvoice->total,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error converting to invoice: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }


    ////////// INVOICE SECTION  ///////////
    public function getInvoiceFields(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        // Get driver's assigned customer group
        $assign = Assign::where('driver_id', $driver->id)->first();
        
        if (!$assign || !$assign->customer_group_id) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'No customer group assigned to driver',
                'data' => null
            ], 200);
        }

        // Get customer group and customer IDs
        $customerGroup = CustomerGroup::where('id', $assign->customer_group_id)->first();
        
        if (!$customerGroup) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Customer group not found',
                'data' => null
            ], 200);
        }

        // Get assigned customer IDs (convert string or array to array)
        $assignedCustomers = $customerGroup->customer_ids ?? [];

        // If it's a JSON string, decode it
        if (is_string($assignedCustomers)) {
            $assignedCustomers = json_decode($assignedCustomers, true);
        }

        // Ensure it's an array
        if (!is_array($assignedCustomers)) {
            $assignedCustomers = [];
        }

        // Now extract just the customer IDs if needed
        $assignedCustomerIds = [];

        foreach ($assignedCustomers as $customer) {
            // Handle both array format and object format
            if (is_array($customer)) {
                if (isset($customer['id']) && is_numeric($customer['id'])) {
                    $assignedCustomerIds[] = (int) $customer['id'];   
                    
                }
            } elseif (is_object($customer)) {
                if (isset($customer->id) && is_numeric($customer->id)) {
                    $assignedCustomerIds[] = (int) $customer->id;
                    
                }
            } elseif (is_numeric($customer)) {
                // Handle legacy format where it might just be a numeric ID
                $assignedCustomerIds[] = (int) $customer;
            }
        }

        // Filter out empty/null values and remove duplicates
        $assignedCustomerIds = array_unique(array_filter($assignedCustomerIds, function($id) {
            return !empty($id) && is_numeric($id);
        }));

        // Get customers only from assigned IDs
        $customers = Customer::whereIn('id', $assignedCustomerIds)
            ->orderBy('company')
            ->get(['id', 'company', 'paymentterm'])
            ->keyBy('id');

        // If no customers found, return error
        if ($customers->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'No customers found for the assigned customer group',
                'data' => null
            ], 200);
        }

        $driverProductIds = InventoryBalance::where('driver_id', $driver->id)
        ->pluck('product_id')
        ->toArray();       

        if (!empty($driverProductIds)) {
            $products = Product::whereIn('id', $driverProductIds)
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $products = collect([]);
        }

        // Prepare options
        $customerOptions = [];
        foreach ($customers as $customer) {
            $customerOptions[] = [
                'id' => $customer->id,
                'name' => $customer->company,
                'paymentterm' => $customer->paymentterm
            ];
        }

        $productOptions = [];
        foreach ($products as $product) {
            $productOptions[] = [
                'id' => $product->id,
                'name' => $product->name
            ];
        }

        // Define fields
        $fields = [
            [
                'key' => 'invoiceno',
                'label' => 'Order No',
                'type' => 'text',
                'value' => \App\Models\Invoice::getNextInvoiceNumber($driver->id),
                'required' => true,
                'placeholder' => 'Auto-generated',
            ],
            [
                'key' => 'date',
                'label' => 'Date',
                'type' => 'date',
                'required' => true,
                'placeholder' => 'DD-MM-YYYY',
            ],
            [
                'key' => 'customer_id',
                'label' => 'Customer',
                'type' => 'select',
                'required' => true,
                'placeholder' => 'Pick a Customer...',
                'options' => $customerOptions,
            ],
            [
                'key' => 'paymentterm_display',
                'label' => 'Payment Term',
                'type' => 'info',
                'readonly' => true,
                'value' => '',
                'depends_on' => 'customer_id',
                'depends_logic' => 'payment_term'
            ],
            [
                'key' => 'remark',
                'label' => 'Remark',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Enter remarks',
                'maxlength' => 255
            ],
            [
                'key' => 'items',
                'label' => 'Order Items',
                'type' => 'repeater',
                'required' => true,
                'fields' => [
                    [
                        'key' => 'product_id',
                        'label' => 'Product',
                        'type' => 'select',
                        'required' => true,
                        'placeholder' => 'Select Product...',
                        'options' => $productOptions
                    ],
                    [
                        'key' => 'quantity',
                        'label' => 'Quantity',
                        'type' => 'number',
                        'required' => true,
                        'placeholder' => '0.00'
                    ],
                    [
                        'key' => 'price',
                        'label' => 'Price',
                        'type' => 'number',
                        'required' => true,
                        'placeholder' => '0.00'
                    ]
                ],
                'add_button_label' => 'Add Item',
                'remove_button_label' => 'Remove',
                'min_rows' => 1
            ]
        ];

        $paymentFields = [
            [
                'key' => 'payment_section',
                'label' => 'Payment Information (Cash Payment)',
                'type' => 'section',
                'conditional' => true,
                'condition' => 'paymentterm === "Cash"',
                'fields' => [
                    [
                        'key' => 'payment_amount_info',
                        'label' => 'Payment Amount',
                        'type' => 'info',
                        'readonly' => true,
                        'value' => '0.00',
                        'hint' => 'Auto-calculated from invoice items',
                        'prefix' => 'RM'
                    ],
                    [
                        'key' => 'payment_attachment',
                        'label' => 'Payment Receipt/Attachment',
                        'type' => 'file',
                        'required' => true,
                        'accept' => '.jpg,.jpeg,.png,.pdf',
                        'max_size' => 2048, // 2MB in KB
                        'hint' => 'Accept .jpg, .jpeg, .png, .pdf (Max: 2MB)'
                    ],
                    [
                        'key' => 'payment_remark',
                        'label' => 'Payment Remark',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Optional payment note',
                        'maxlength' => 255
                    ]
                ]
            ]
        ];

        $allFields = array_merge($fields, $paymentFields);

        return response()->json([
            'result' => true,
            'message' => '' . __LINE__ . $this->message_separator . 'Invoice fields retrieved successfully',
            'data' => $allFields
        ], 200);
    }

    public function createInvoice(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        if($driver->trip_id == NULL ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }

        // Get customer payment term
        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Customer not found',
                'data' => null
            ], 200);
        }

        $paymentTerm = $customer->paymentterm;

        // Define validation rules (NO status field required)
        $validationRules = [
            'invoiceno' => 'required|string|max:255',
            'date' => 'required|date_format:d-m-Y',
            'customer_id' => 'required|exists:customers,id',
            'remark' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:0.01',
            'details.*.price' => 'required|numeric|min:0'
        ];
        
        // Add payment validation for cash
        if ($paymentTerm == 'Cash') {
            $validationRules['payment_attachment'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048';
            $validationRules['payment_remark'] = 'nullable|string|max:255';
        }

        // Validate request
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Validation failed',
                'errors' => $validator->errors()->toArray(),
                'data' => null
            ], 200);
        }

        $insufficientProducts = [];
        $details = $request->input('details', []);
        
        if (!empty($details)) {
            foreach ($details as $index => $detail) {
                $productId = $detail['product_id'];
                $quantityNeeded = $detail['quantity'];
                
                // Get product name for error message
                $product = Product::find($productId);
                
                // Check inventory balance
                $inventoryBalance = InventoryBalance::where('driver_id', $driver->id)
                    ->where('product_id', $productId)
                    ->first();
                
                if (!$inventoryBalance) {
                    // No inventory record found for this product
                    $insufficientProducts[] = [
                        'product_id' => $productId,
                        'product_name' => $product ? $product->name : 'Unknown Product',
                        'required_quantity' => $quantityNeeded,
                        'available_quantity' => 0,
                        'error' => 'No inventory record found'
                    ];
                } elseif ($inventoryBalance->quantity < $quantityNeeded) {
                    // Insufficient quantity
                    $insufficientProducts[] = [
                        'product_id' => $productId,
                        'product_name' => $product ? $product->name : 'Unknown Product',
                        'required_quantity' => $quantityNeeded,
                        'available_quantity' => $inventoryBalance->quantity,
                        'error' => 'Insufficient inventory'
                    ];
                }
            }
        }

        // If there are insufficient products, return error
        if (!empty($insufficientProducts)) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Insufficient inventory balance',
                'errors' => [
                    'inventory' => ['Some products have insufficient inventory']
                ],
                'insufficient_products' => $insufficientProducts,
                'data' => null
            ], 200);
        }
        
        DB::beginTransaction();

        try {
            $input = $request->all();
            
            // Convert date format
            $input['date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $input['date'])->format('Y-m-d');
            
            // Handle invoice number generation
            if (empty($input['invoiceno']) || $input['invoiceno'] == 'SYSTEM GENERATED IF BLANK') {
                $input['invoiceno'] = \App\Models\Invoice::getNextInvoiceNumber($driver->id);
            }
            
            // Set driver information
            $input['driver_id'] = $driver->id;
            $input['created_by'] = $driver->id;
            $input['is_driver'] = true;
            $input['paymentterm'] = $paymentTerm;
            
            // Status is automatically set to COMPLETED by model boot method
            // No need to set it explicitly

            // Calculate total
            $total = 0;
            $details = [];
            if (isset($input['details']) && is_array($input['details'])) {
                foreach ($input['details'] as $detail) {
                    $itemTotal = $detail['quantity'] * $detail['price'];
                    $total += $itemTotal;
                    
                    $details[] = [
                        'product_id' => $detail['product_id'],
                        'quantity' => $detail['quantity'],
                        'price' => $detail['price'],
                        'totalprice' => $itemTotal
                    ];
                }
            }
            $input['total'] = $total;
            $input['trip_id'] = $driver->trip_id; //store trip id for invoice
            $input['driver_id'] = $driver->id; //store trip id for invoice

            // Create invoice (status will be automatically set to COMPLETED)
            $invoice = Invoice::create($input);

            // Create invoice details
            if (!empty($details)) {
                foreach ($details as $detail) {
                    InvoiceDetail::create(array_merge(
                        $detail,
                        ['invoice_id' => $invoice->id]
                    ));
                }
            }

            // Handle payment for cash invoices
            if ($paymentTerm == 'Cash') {
                $attachmentPath = null;
                
                // Handle attachment upload
                if ($request->hasFile('payment_attachment')) {
                    $file = $request->file('payment_attachment');
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $attachmentPath = $file->storeAs('invoice_payments', $fileName, 'public');
                }

                // Create approved invoice payment
                $invoicePayment = new \App\Models\InvoicePayment();
                $invoicePayment->invoice_id = $invoice->id;
                $invoicePayment->type = 'Cash';
                $invoicePayment->customer_id = $invoice->customer_id;
                $invoicePayment->amount = $total;
                $invoicePayment->status = 1; // Approved
                
                if ($attachmentPath) {
                    $invoicePayment->attachment = $attachmentPath;
                }
                
                $invoicePayment->driver_id = $driver->id;
                $invoicePayment->user_id = null;
                $invoicePayment->approve_by = $driver->name;
                $invoicePayment->approve_at = now();
                $invoicePayment->remark = $request->input('payment_remark', 'Cash payment');
                $invoicePayment->save();
            }

            // Update driver credit amount
            // $driver->credit_amount = ($driver->credit_amount ?? 0) + $total;
            // $driver->save();

            DB::commit();

            //deduct driver inventory balance with the invoice items
            if (!empty($details)) {
                foreach ($details as $detail) {
                    // 1. Create inventory transaction record for each product
                    try {
                        InventoryTransaction::createTransaction(
                            $driver->id,
                            $detail['product_id'], // ✅ Use product_id from invoice detail
                            $detail['quantity'],    // ✅ Use quantity from invoice detail
                            InventoryTransaction::TYPE_STOCK_OUT,
                            'Create Invoice with ID: ' . $invoice->invoiceno,
                            $invoice->id
                        );
                    } catch (\Exception $e) {
                        \Log::error('Failed to create inventory transaction: ' . $e->getMessage(), [
                            'driver_id' => $driver->id,
                            'product_id' => $detail['product_id'],
                            'invoice_id' => $invoice->id
                        ]);
                        // Continue with other products even if one fails
                        continue;
                    }
                    
                    // 2. Update inventory balance for each product
                    try {
                        $inventoryBalance = InventoryBalance::where('driver_id', $driver->id)
                            ->where('product_id', $detail['product_id'])
                            ->first();
                        
                        if ($inventoryBalance) {
                            $inventoryBalance->quantity -= $detail['quantity'];
                            $inventoryBalance->save();
                        } else {
                            // This shouldn't happen since we checked inventory balance earlier,
                            // but create record if it doesn't exist (with negative quantity)
                            InventoryBalance::create([
                                'driver_id' => $driver->id,
                                'product_id' => $detail['product_id'],
                                'quantity' => -$detail['quantity'] // Negative since it's stock out
                            ]);
                            
                            \Log::warning('Created new inventory balance record for product during invoice creation', [
                                'driver_id' => $driver->id,
                                'product_id' => $detail['product_id'],
                                'quantity' => -$detail['quantity']
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to update inventory balance: ' . $e->getMessage(), [
                            'driver_id' => $driver->id,
                            'product_id' => $detail['product_id'],
                            'quantity' => $detail['quantity']
                        ]);
                        // Continue with other products
                        continue;
                    }
                }
            }

            // Prepare response
            $responseData = [
                'id' => $invoice->id,
                'invoiceno' => $invoice->invoiceno,
                'date' => \Carbon\Carbon::parse($invoice->date)->format('d-m-Y'),
                'customer_id' => $invoice->customer_id,
                'customer_name' => optional($invoice->customer)->company ?? 'N/A',
                'total' => (float) $invoice->total,
                'paymentterm' => $invoice->paymentterm,
                'status' => $invoice->status, // Will be 0 (COMPLETED)
                'status_text' => $invoice->getStatusTextAttribute(), // "Completed"
                'created_by_driver' => true,
                'driver_id' => $driver->id,
                // 'driver_credit_amount' => $driver->credit_amount,
                'payment_created' => $paymentTerm == 'Cash',
                'items_count' => count($details),
                'created_at' => $invoice->created_at->format('Y-m-d H:i:s')
            ];

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Invoice created successfully with Completed status',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Invoice creation failed: ' . $e->getMessage(), [
                'driver_id' => $driver->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error creating invoice: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function getDriverInvoices(Request $request, $customer_id = null)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        try {
            // Start building the query
            $query = Invoice::where('is_driver', true)
                ->where('created_by', $driver->id);

            // Apply customer filter if customer_id is provided
            if ($customer_id) {
                $query->where('customer_id', $customer_id);
            }

            // Get sales invoices
            $invoices = $query->with(['customer:id,company,phone,paymentterm', 'invoiceDetails.product:id,name'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Get driver's current trip ID
            $driverTripId = $driver->trip_id ?? null;

            // Define cancellable statuses
            $cancellableStatuses = [Invoice::STATUS_COMPLETED];

            // Format the response
            $formattedInvoices = $invoices->map(function($invoice) use ($driverTripId, $cancellableStatuses) {
                
                // Check if this specific invoice can be cancelled
                $allowCancel = true;
                
                // Rule 1: Driver must have an active trip
                if (!$driverTripId) {
                    $allowCancel = false;
                }
                
                // Rule 2: Invoice must belong to the same trip as driver's current trip
                // If invoice has no trip_id OR doesn't match driver's current trip, cannot cancel
                if (!$invoice->trip_id || $driverTripId != $invoice->trip_id) {
                    $allowCancel = false;
                }
                
                // Rule 3: Invoice must be in a cancellable status
                if (isset($invoice->status) && !in_array($invoice->status, $cancellableStatuses)) {
                    $allowCancel = false;
                }
                
                // If any rule failed, set to false (already done above)
                
                return [
                    'id' => $invoice->id,
                    'invoiceno' => $invoice->invoiceno,
                    'date' => $invoice->date,
                    'customer_id' => $invoice->customer_id,
                    'customer' => [
                        'id' => $invoice->customer_id,
                        'name' => $invoice->customer->company ?? 'N/A',
                        'paymentterm' => $invoice->customer->paymentterm ?? '',
                        'phone' => $invoice->customer->phone ?? '',
                    ],                    
                    'paymentterm' => $invoice->paymentterm,
                    'status' => $invoice->getStatusTextAttribute(),
                    'remark' => $invoice->remark,
                    'total' => number_format($invoice->total, 2),
                    'is_driver' => $invoice->is_driver,
                    'created_by' => $invoice->created_by,
                    'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $invoice->updated_at->format('Y-m-d H:i:s'),
                    'trip_id' => $invoice->trip_id,
                    'items_count' => $invoice->invoiceDetails->count(),
                    'allow_cancel' => $allowCancel,
                    'items' => $invoice->invoiceDetails->map(function($detail) {
                        return [
                            'product_id' => $detail->product_id,
                            'product_name' => optional($detail->product)->name ?? 'N/A',
                            'quantity' => (float) $detail->quantity,
                            'price' => (float) $detail->price,
                            'total' => (float) $detail->totalprice,
                            'total_formatted' => number_format($detail->totalprice, 2)
                        ];
                    })->toArray(),
                    'pdf_url' => $this->getinvoicepdf($invoice->id)
                ];
            });

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Sales invoices retrieved successfully',
                'data' => [
                    'count' => $invoices->count(),
                    'driver_trip_id' => $driverTripId,
                    'invoices_with_cancel_permission' => $formattedInvoices->where('allow_cancel', true)->count(),
                    'invoices' => $formattedInvoices->toArray()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error retrieving sales invoices: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }
    public function getInvoiceById(Request $request, $id)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        try {
            // Get sales invoice with proper authorization check
            $invoice = Invoice::where('is_driver', true)
                ->where('created_by', $driver->id)
                ->where('id', $id)
                ->with([
                    'customer:id,company,address,phone,paymentterm',
                    'invoiceDetails.product:id,name,code'
                ])
                ->first();

            if (!$invoice) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Sales order not found or not authorized',
                    'data' => null
                ], 200);
            }

            // Get driver's current trip ID
            $driverTripId = $driver->trip_id;
            
            $allowCancel = true;
                
                // Rule 1: Driver must have an active trip
            if (!$driverTripId) {
                $allowCancel =  false;
            }

            // Rule 2: Invoice must belong to the same trip as driver's current trip
            if (!$invoice->trip_id || $driverTripId != $invoice->trip_id) {
                $allowCancel =  false;
            }
            // Rule 3: Invoice must be in a cancellable status
            $cancellableStatuses = [Invoice::STATUS_COMPLETED];

            if (isset($invoice->status) && !in_array($invoice->status, $cancellableStatuses)) {
                $allowCancel =  false;
            }
            
            // Format the response
            $formattedInvoice = [
                'id' => $invoice->id,
                'invoiceno' => $invoice->invoiceno,
                'date' => $invoice->date, // Already formatted in getDateAttribute
                'customer_id' => $invoice->customer_id,
                'customer' => [
                    'id' => $invoice->customer_id,
                    'name' => $invoice->customer->company ?? 'N/A',
                    'paymentterm' => $invoice->customer->paymentterm ?? '',
                    'phone' => $invoice->customer->phone ?? '',
                ],
                'paymentterm' => $invoice->paymentterm,
                'status' => $invoice->getStatusTextAttribute(),
                'remark' => $invoice->remark,
                'total' => number_format($invoice->total, 2),
                'is_driver' => $invoice->is_driver,
                'created_by' => $invoice->created_by,
                'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                'items_count' => $invoice->invoiceDetails->count(),
                'allow_cancel' => $allowCancel,
                'items' => $invoice->invoiceDetails->map(function($detail) {
                    return [
                        'id' => $detail->id,
                        'product_id' => $detail->product_id,
                        'product_name' => optional($detail->product)->name ?? 'N/A',
                        'product_code' => optional($detail->product)->code ?? 'N/A',
                        'quantity' => (float) $detail->quantity,
                        'price' => (float) $detail->price,
                        'total' => (float) $detail->totalprice,
                        'total_formatted' => number_format($detail->totalprice, 2)
                    ];
                })->toArray(),
                'pdf_url' => $this->getinvoicepdf($invoice->id)
            ];

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Sales order retrieved successfully',
                'data' => $formattedInvoice
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error retrieving sales order: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

	public function getinvoicepdf($invoice_id)
    {
        try {

            $invoice = Invoice::where('id', $invoice_id)
                ->with(['customer', 'InvoiceDetails.product', 'createdByUser', 'createdByDriver'])
                ->first();

            if (empty($invoice)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Invoice not found',
                    'data' => null
                ], 200);
            }
            
            $min = 450;
            $each = 23;
            $height = (count($invoice['invoiceDetails']) * $each) + $min;
            $creator = $invoice->creator;
            
            $pdf = Pdf::loadView('invoices.print', [
                'invoices' => $invoice,
                'creatorName' => $creator->name
            ]);
            
            $pdf->setPaper(array(0, 0, 300, $height), 'portrait')
                ->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
            
            return base64_encode($pdf->output());
            
        } catch (Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function cancelInvoice(Request $request, $id)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        if($driver->trip_id == NULL ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }

        // Validate cancellation reason
        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500'
        ]);

        try {
            // Get invoice with proper authorization check
            $invoice = Invoice::where('is_driver', true)
                ->where('created_by', $driver->id)
                ->where('id', $id)
                ->first();
            if (!$invoice) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Invoice not found or not authorized',
                    'data' => null
                ], 404);
            }

            // Check if invoice can be cancelled (only completed invoices can be cancelled)
            if ($invoice->status != Invoice::STATUS_COMPLETED) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Only completed invoices can be cancelled',
                    'data' => null
                ], 400);
            }

            // Start transaction
            DB::beginTransaction();

            // Cancel the invoice
            $invoice->cancel();

            // Store cancellation reason if provided
            $cancellationReason = $request->input('cancellation_reason');
            if ($cancellationReason) {
                // Append cancellation reason to remark
                $cancellationNote = "\n[Cancelled by Driver: " . $cancellationReason . " - " . date('Y-m-d H:i:s') . "] by " . $driver->name;
                $invoice->remark = $cancellationNote;
                $invoice->save();
            }

            // Commit transaction
            DB::commit();

            // Reload the invoice with relationships for response
            $invoice->refresh();
            $invoice->load([
                'customer:id,company,address,phone,paymentterm',
                'invoiceDetails.product:id,name,code'
            ]);

            // Format the response
            $formattedInvoice = [
                'invoice' => $invoice,
                'cancellation_reason' => $cancellationReason,
                'cancelled_at' => now()->format('Y-m-d H:i:s'),
                'cancelled_by' => [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'type' => 'driver'
                ]
            ];

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Invoice cancelled successfully',
                'data' => $formattedInvoice
            ], 200);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error cancelling invoice: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function checkInOut(Request $request)
    {   
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'action' => 'required|in:checkin,checkout',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null
            ], 200);
        }

        try {
            $driver_id = $driver->id;
            $checkTime = now();
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $action = $request->input('action');
            
            // Get last record for this driver
            $lastRecord = DriverCheckIn::where('driver_id', $driver_id)
                ->orderBy('check_time', 'desc')
                ->first();

            // Validate check-in/check-out sequence
            if ($action === 'checkin') {
                // For check-in: Verify that the last action was a check-out or no previous records
                if ($lastRecord && $lastRecord->type !== DriverCheckIn::TYPE_CHECK_OUT) {
                    return response()->json([
                        'result' => false,
                        'message' => 'You must check out before checking in again',
                        'data' => [
                            'last_action' => $lastRecord->type,
                            'last_check_time' => $lastRecord->check_time->format('Y-m-d H:i:s')
                        ]
                    ], 200);
                }
            }

            if ($action === 'checkout') {
                // For check-out: Verify that the last action was a check-in
                if (!$lastRecord || $lastRecord->type !== DriverCheckIn::TYPE_CHECK_IN) {
                    return response()->json([
                        'result' => false,
                        'message' => 'You must check in before checking out',
                        'data' => null
                    ], 200);
                }

                // Check if check-out time is after check-in time
                if ($checkTime->lt($lastRecord->check_time)) {
                    return response()->json([
                        'result' => false,
                        'message' => 'Check-out time cannot be before check-in time',
                        'data' => [
                            'last_checkin_time' => $lastRecord->check_time->format('Y-m-d H:i:s')
                        ]
                    ], 200);
                }
            }
            $type = ($action === 'checkin')? DriverCheckIn::TYPE_CHECK_IN : DriverCheckIn::TYPE_CHECK_OUT;
            // Create the record
            $createdRecord = DriverCheckIn::create([
                'driver_id' => $driver_id,
                'type' => $type,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'check_time' => $checkTime,
            ]);

            $responseData = [
                'id' => $createdRecord->id,
                'driver_id' => $createdRecord->driver_id,
                'type' => $createdRecord->type,
                'action' => $action,
                'latitude' => $createdRecord->latitude,
                'longitude' => $createdRecord->longitude,
                'check_time' => $createdRecord->check_time->format('Y-m-d H:i:s'),
                'created_at' => $createdRecord->created_at->format('Y-m-d H:i:s')
            ];

            // Add current status if needed
            if ($action === 'checkin') {
                $responseData['current_status'] = 'checked_in';
            } else {
                $responseData['current_status'] = 'checked_out';
            }

            return response()->json([
                'result' => true,
                'message' => ucfirst($action) . ' successful',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => ucfirst($action) . ' failed',
                'error' => $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function getAllProduct(Request $request, $customer_id = null)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        try {
            // Get driver's inventory balances
            $driverInventory = InventoryBalance::where('driver_id', $driver->id)
                ->pluck('quantity', 'product_id')
                ->toArray();
            
            // Get special prices if customer_id is provided
            $specialPrices = [];
            if ($customer_id) {
                $specialPrices = SpecialPrice::where('customer_id', $customer_id)
                    ->where('status', 1)
                    ->pluck('price', 'product_id')
                    ->toArray();
            }
            
            $categories = ProductCategory::with(['products' => function($query) {
                $query->select('id', 'name', 'category_id', 'price', 'status')
                    ->where('status', 1)
                    ->orderBy('name');
            }])
            ->where('status', 1)
            ->orderBy('name')
            ->get();

            // Format the response with driver's inventory quantity
            $output = $categories->map(function($category) use ($driverInventory, $specialPrices) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'products' => $category->products->map(function($product) use ($driverInventory, $specialPrices) {
                        // Get quantity from driver's inventory, default to 0 if not found
                        $quantity = $driverInventory[$product->id] ?? 0;
                        
                        // Get price: use special price if available, otherwise default price
                        $price = $specialPrices[$product->id] ?? $product->price;
                        
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $price,
                            'quantity' => $quantity,
                            'status' => $product->getStatusTextAttribute()
                        ];
                    })
                ];
            });

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Product Retrieved successfully',
                'data' => $output
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error getting product: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function StockRequest(Request $request)
    {

        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 200);
        }

        if($driver->trip_id == NULL ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }

            $rules = [
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'remarks' => 'nullable|string|max:500'
            ];
            
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 200);
            }

            try {
                // Get items from request
                $items = $request->items;
                
                // Check for duplicate products
                $productIds = array_column($items, 'product_id');
                if (count($productIds) !== count(array_unique($productIds))) {
                    return response()->json([
                        'result' => false,
                        'message' => 'Duplicate products are not allowed in the same request',
                        'data' => null
                    ], 200);
                }

                // Create inventory request with items array
                $inventoryRequest = InventoryRequest::create([
                    'driver_id' => $driver->id,
                    'items' => $items, // Store as JSON array
                    'status' => InventoryRequest::STATUS_PENDING,
                    'trip_id' => $driver->trip_id,
                    'remarks' => $request->remarks ?? null,
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
            ], 200);
        }
    }

    public function getStockRequestRecord(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        if($driver->trip_id == NULL){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }
        
        try {
            $inventoryRequests = InventoryRequest::where('driver_id', $driver->id)
                ->where('trip_id', $driver->trip_id)
                ->get()
                ->map(function ($inventoryRequest) {
                    // Get approver and rejector names
                    $approver = $inventoryRequest->approved_by ? User::find($inventoryRequest->approved_by) : null;
                    $rejector = $inventoryRequest->rejected_by ? User::find($inventoryRequest->rejected_by) : null;
                    
                    // Process items array to add product names
                    $itemsWithProductNames = [];
                    if ($inventoryRequest->items && is_array($inventoryRequest->items)) {
                        foreach ($inventoryRequest->items as $item) {
                            $product = Product::find($item['product_id'] ?? null);
                            $itemsWithProductNames[] = [
                                'product_id' => $item['product_id'] ?? null,
                                'product_name' => $product ? $product->name : 'Unknown Product',
                                'quantity' => $item['quantity'] ?? 0
                            ];
                        }
                    }
                    
                    // Return formatted data
                    return [
                        'id' => $inventoryRequest->id,
                        'driver_id' => $inventoryRequest->driver_id,
                        'trip_id' => $inventoryRequest->trip_id,
                        'items' => $itemsWithProductNames,
                        'status' => $inventoryRequest->status,
                        'remarks' => $inventoryRequest->remarks,
                        'rejection_reason' => $inventoryRequest->rejection_reason,
                        'approved_by' => $inventoryRequest->approved_by,
                        'approved_by_name' => $approver ? $approver->name : null,
                        'rejected_by' => $inventoryRequest->rejected_by,
                        'rejected_by_name' => $rejector ? $rejector->name : null,
                        'approved_at' => $inventoryRequest->approved_at,
                        'rejected_at' => $inventoryRequest->rejected_at,
                        'created_at' => $inventoryRequest->created_at,
                        'updated_at' => $inventoryRequest->updated_at,
                        'item_count' => $inventoryRequest->item_count,
                        'total_quantity' => $inventoryRequest->total_quantity,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Stock Request Record retrieved successfully.',
                'data' => $inventoryRequests
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stock request record: ' . $e->getMessage()
            ], 200);
        }
    }
    

    public function getStockReturnRecord(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        if($driver->trip_id == NULL){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }
        
        try {
            $inventoryReturns = InventoryReturn::where('driver_id', $driver->id)
                ->where('trip_id', $driver->trip_id)
                ->get()
                ->map(function ($inventoryReturn) {
                    // Get approver and rejector names
                    $approver = $inventoryReturn->approved_by ? User::find($inventoryReturn->approved_by) : null;
                    $rejector = $inventoryReturn->rejected_by ? User::find($inventoryReturn->rejected_by) : null;
                    
                    // Process items array to add product names
                    $itemsWithProductNames = [];
                    if ($inventoryReturn->items && is_array($inventoryReturn->items)) {
                        foreach ($inventoryReturn->items as $item) {
                            $product = Product::find($item['product_id'] ?? null);
                            $itemsWithProductNames[] = [
                                'product_id' => $item['product_id'] ?? null,
                                'product_name' => $product ? $product->name : 'Unknown Product',
                                'quantity' => $item['quantity'] ?? 0
                            ];
                        }
                    }
                    
                    // Return formatted data
                    return [
                        'id' => $inventoryReturn->id,
                        'driver_id' => $inventoryReturn->driver_id,
                        'trip_id' => $inventoryReturn->trip_id,
                        'items' => $itemsWithProductNames,
                        'status' => $inventoryReturn->status,
                        'remarks' => $inventoryReturn->remarks,
                        'rejection_reason' => $inventoryReturn->rejection_reason,
                        'approved_by' => $inventoryReturn->approved_by,
                        'approved_by_name' => $approver ? $approver->name : null,
                        'rejected_by' => $inventoryReturn->rejected_by,
                        'rejected_by_name' => $rejector ? $rejector->name : null,
                        'approved_at' => $inventoryReturn->approved_at,
                        'rejected_at' => $inventoryReturn->rejected_at,
                        'created_at' => $inventoryReturn->created_at,
                        'updated_at' => $inventoryReturn->updated_at,
                        'item_count' => $inventoryReturn->item_count,
                        'total_quantity' => $inventoryReturn->total_quantity,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Stock Return Record retrieved successfully.',
                'data' => $inventoryReturns
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stock return record: ' . $e->getMessage()
            ], 200);
        }
    }
    public function getStockReturn(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        if($driver->trip_id == NULL){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }
        
        try {
            $inventoryReturns = InventoryReturn::where('driver_id', $driver->id)
                ->where('trip_id', $driver->trip_id)
                ->get()
                ->map(function ($inventoryReturn) {
                    // Get approver and rejector names
                    $approver = $inventoryReturn->approved_by ? User::find($inventoryReturn->approved_by) : null;
                    $rejector = $inventoryReturn->rejected_by ? User::find($inventoryReturn->rejected_by) : null;
                    
                    // Process items array to add product names
                    $itemsWithProductNames = [];
                    if ($inventoryReturn->items && is_array($inventoryReturn->items)) {
                        foreach ($inventoryReturn->items as $item) {
                            $product = Product::find($item['product_id'] ?? null);
                            $itemsWithProductNames[] = [
                                'product_id' => $item['product_id'] ?? null,
                                'product_name' => $product ? $product->name : 'Unknown Product',
                                'quantity' => $item['quantity'] ?? 0
                            ];
                        }
                    }
                    
                    // Return formatted data
                    return [
                        'id' => $inventoryReturn->id,
                        'driver_id' => $inventoryReturn->driver_id,
                        'trip_id' => $inventoryReturn->trip_id,
                        'items' => $itemsWithProductNames,
                        'status' => $inventoryReturn->status,
                        'remarks' => $inventoryReturn->remarks,
                        'rejection_reason' => $inventoryReturn->rejection_reason,
                        'approved_by' => $inventoryReturn->approved_by,
                        'approved_by_name' => $approver ? $approver->name : null,
                        'rejected_by' => $inventoryReturn->rejected_by,
                        'rejected_by_name' => $rejector ? $rejector->name : null,
                        'approved_at' => $inventoryReturn->approved_at,
                        'rejected_at' => $inventoryReturn->rejected_at,
                        'created_at' => $inventoryReturn->created_at,
                        'updated_at' => $inventoryReturn->updated_at,
                        'item_count' => $inventoryReturn->item_count,
                        'total_quantity' => $inventoryReturn->total_quantity,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Stock Return Record retrieved successfully.',
                'data' => $inventoryReturns
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stock return record: ' . $e->getMessage()
            ], 200);
        }
    }

    public function StockCount(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        if($driver->trip_id == NULL ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }

        // Check if there's already a pending inventory count for this driver
        $inventoryCount = InventoryCount::where('driver_id', $driver->id)
            ->where('trip_id', $driver->trip_id)
            ->first();

        if($inventoryCount){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'You have request for Stock Count, please Contact your Stock Manager to approved.',
                'data' => null
            ], 200);
        }

        try {
            // Get current inventory balance for this driver
            $inventoryBalances = InventoryBalance::where('driver_id', $driver->id)
            ->where('quantity', '>', 0)
            ->with('product')
            ->get();

            $items = [];
            foreach ($inventoryBalances as $balance) {
                $items[] = [
                    'product_id' => $balance->product_id,
                    'current_quantity' => $balance->quantity, // Store current quantity from balance
                    'counted_quantity' => "", // Empty string for counted quantity initially
                ];
            }

            // Create inventory count with current items
            $inventoryCount = InventoryCount::create([
                'driver_id' => $driver->id,
                'items' => $items, // Store as JSON with current quantities
                'status' => InventoryCount::STATUS_PENDING,
                'trip_id' => $driver->trip_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock Count Request successfully.',
                'data' => $inventoryCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create request: ' . $e->getMessage()
            ], 200);
        }
    }

    public function StockCountStatus(Request $request)
    {

        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        if($driver->trip_id == NULL ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }

        try {
            $inventoryCount = InventoryCount::where('driver_id', $driver->id)->where('trip_id',$driver->trip_id)->where('status', InventoryCount::STATUS_APPROVED)->first();
            if($inventoryCount){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__ . $this->message_separator . 'Stock Count Completed',
                    'data' => [
                        'isDone' => true
                    ]
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__ . $this->message_separator . 'Stock Count Not Complete yet.',
                    'data' => [
                        'isDone' => false
                    ]
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stock count status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function tripStart(Request $request)
    {

        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        
        if($driver->trip_id != NULL ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to end trip before start new trip.',
                'data' => null
            ], 200);
        }

        try {

            $currentStock = InventoryBalance::where('driver_id', $driver->id)
                ->with('product')
                ->get()
                ->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_code' => $item->product->code,
                        'quantity' => $item->quantity
                    ];
                });

            $trip = Trip::create([
                'date'=> now(),
                'uuid' => Trip::generateUniqueReference(),
                'driver_id' => $driver->id,
                'type' => Trip::START_TRIP,
                'stock_data' => $currentStock, // JSON store
            ]);
            
            $driver->trip_id = $trip->uuid; 
            $driver->save();
                
            return response()->json([
                'success' => true,
                'message' => 'Driver Start Trip successfully.',
                'data' => $trip
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create request: ' . $e->getMessage()
            ], 200);
        }
    }

    public function tripEnd(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        
        if($driver->trip_id == NULL ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before end trip.',
                'data' => null
            ], 200);
        }

        $inventoryCount = InventoryCount::where('driver_id', $driver->id)->where('trip_id',$driver->trip_id)->where('status', InventoryCount::STATUS_APPROVED)->first();

        if(!$inventoryCount ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to complete Stock Count before end trip.',
                'data' => null
            ], 200);
        }

        $inventoryReturns = InventoryReturn::where('driver_id', $driver->id)->where('trip_id',$driver->trip_id)->where('status', InventoryReturn::STATUS_PENDING)->first();

        if($inventoryReturns ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have Pending Stock Returns.',
                'data' => null
            ], 200);
        }

        $inventoryRequests = InventoryRequest::where('driver_id', $driver->id)->where('trip_id',$driver->trip_id)->where('status', InventoryRequest::STATUS_PENDING)->first();

        if($inventoryRequests ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have Pending Stock Requests.',
                'data' => null
            ], 200);
        }

        try {

        // **UPDATE INVENTORY BALANCE BASED ON INVENTORY COUNT**
            $this->updateInventoryBalanceFromCount($driver->id, $inventoryCount);

            $currentStock = InventoryBalance::where('driver_id', $driver->id)
            ->with('product')
            ->get()
            ->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_code' => $item->product->code,
                    'quantity' => $item->quantity
                ];
            });
            $trip = Trip::create([
                'uuid'=> $driver->trip_id,
                'date'=> now(),
                'driver_id' => $driver->id,
                'type' => Trip::END_TRIP,
                'stock_data' => $currentStock, // JSON store
            ]); 

            $tripSummary = TripController::generateTripReport($trip->uuid);

            $inventoryBalances = InventoryBalance::where('driver_id', $driver->id)
                ->with('product')
                ->get()
                ->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? 'Unknown',
                        'product_code' => $item->product->code ?? '',
                        'quantity' => $item->quantity
                    ];
                })
                ->toArray();       

            $summaryData = [
                'trip_summary' => [
                    'trip_id' => $tripSummary['trip_info']['trip_id'] ?? 'T-' . $driver->trip_id,
                    'driver_name' => $tripSummary['trip_info']['driver']['name'] ?? $driver->name,
                    'start_time' => $tripSummary['trip_info']['start_time'] ?? null,
                    'end_time' => $tripSummary['trip_info']['end_time'] ?? now(),
                    'trip_duration' => isset($tripSummary['trip_info']['start_time'], $tripSummary['trip_info']['end_time']) 
                        ? $this->calculateDuration($tripSummary['trip_info']['start_time'], $tripSummary['trip_info']['end_time'])
                        : null,
                ],
                'sales_summary' => [
                    'total_invoices' => $tripSummary['sales_summary']['total_invoices'] ?? 0,
                    'total_sales_orders' => $tripSummary['sales_summary']['total_sales_orders'] ?? 0,
                    'total_amount' => $tripSummary['sales_summary']['total_amount'] ?? 0,
                    'total_credit' => $tripSummary['sales_summary']['total_credit'] ?? 0,
                    'total_cash' => $tripSummary['sales_summary']['total_cash']?? 0,                
                ],
                'stock_summary' => [
                    $inventoryBalances
                ],
            ];
            
            // send notification to admin this driver has end trip

            $notificationService = app(NotificationService::class);
            $notificationService->createTripEndNotification($driver, $trip);

            $driver->trip_id = NULL;
            $driver->save();

            return response()->json([
                'success' => true,
                'message' => 'Driver End Trip successfully.',
                'data' => $summaryData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to end trip: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateInventoryBalanceFromCount($driverId, InventoryCount $inventoryCount)
    {
        // Loop through each item in the inventory count
        foreach ($inventoryCount->items as $item) {
            $productId = $item['product_id'];
            $countedQuantity = (int) $item['counted_quantity'];
            
            // Find or create inventory balance record for this driver and product
            $inventoryBalance = InventoryBalance::where('driver_id', $driverId)
                ->where('product_id', $productId)
                ->first();
            
            if ($inventoryBalance) {
                // Update existing balance with counted quantity
                $inventoryBalance->quantity = $countedQuantity;
                $inventoryBalance->save();
                
            } else {
                // Create new inventory balance record
                InventoryBalance::create([
                    'driver_id' => $driverId,
                    'product_id' => $productId,
                    'quantity' => $countedQuantity,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
            }
        }
    }

    private function calculateDuration($startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        
        
        return  $start->diff($end)->format('%H:%I:%S');

    }

    public function getInventoryBalance(Request $request)
    {

        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        
        try {
            $inventoryBalances = InventoryBalance::with('product')
            ->where('driver_id', $driver->id)
            ->get()
            ->groupBy(function($item) {
                return $item->product->name ?? 'Unknown Product';
            })
            ->map(function($items, $productName) {
                return [
                    'product_name' => $productName,
                    'total_quantity' => $items->sum('quantity'),
                ];
            })
            ->values() 
            ->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Driver Inventory Balance retrieved successfully.',
                'data' => $inventoryBalances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get inventory balance record: ' . $e->getMessage()
            ], 200);
        }
    }

     public function getInventoryTransaction(Request $request)
    {

        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        
        try {
           $inventoryTransactions = InventoryTransaction::with(['product:id,name'])
                ->where('driver_id', $driver->id)
                ->orderBy('created_at', 'desc')
            ->get()
                ->toArray();

            // Load invoice numbers for transactions that have invoice_id
            $transactionsWithInvoices = array_filter($inventoryTransactions, function($transaction) {
                return !is_null($transaction['invoice_id']);
            });

            if (!empty($transactionsWithInvoices)) {
                $invoiceIds = array_column($transactionsWithInvoices, 'invoice_id');
                $invoices = Invoice::whereIn('id', $invoiceIds)
                    ->pluck('invoiceno', 'id')
                    ->toArray();

                // Add invoice_no to transactions
                foreach ($inventoryTransactions as &$transaction) {
                    if (isset($invoices[$transaction['invoice_id']])) {
                        $transaction['invoiceno'] = $invoices[$transaction['invoice_id']];
                    } else {
                        $transaction['invoiceno'] = null;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Driver Inventory Transactions retrieved successfully.',
                'data' => $inventoryTransactions
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get inventory balance record: ' . $e->getMessage()
            ], 200);
        }
    }

    public function dashboard(Request $request){
        try{
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }

            //process
        
            $inventoryBalances = InventoryBalance::with('product')
            ->where('driver_id', $driver->id)
            ->get()
            ->groupBy(function($item) {
                return $item->product->name ?? 'Unknown Product';
            })
            ->map(function($items, $productName) {
                return [
                    'product_name' => $productName,
                    'total_quantity' => $items->sum('quantity'),
                ];
            })
            ->values() 
            ->toArray();

            $totals = Invoice::selectRaw('
                    SUM(CASE WHEN paymentterm = "Credit" THEN invoice_totals.total ELSE 0 END) as total_credit,
                    SUM(invoice_totals.total) as total_all,
                    SUM(CASE WHEN paymentterm = "Cash" THEN invoice_totals.total ELSE 0 END) as total_cash
                ')
                ->leftJoinSub(
                    InvoiceDetail::select('invoice_id', DB::raw('SUM(totalprice) as total'))
                        ->groupBy('invoice_id'),
                    'invoice_totals',
                    'invoices.id',
                    '=',
                    'invoice_totals.invoice_id'
                )
                ->where('invoices.status', Invoice::STATUS_COMPLETED)
                ->where('invoices.is_driver', 1)
                ->where('invoices.trip_id', $driver->trip_id)
                ->where('invoices.created_by', $driver->id)
                ->first();
            
            $totalAmount = $totals->total_all ?? 0;
            $totalCreditAmount = $totals->total_credit ?? 0;
            $totalCashAmount = $totals->total_cash ?? 0;
            
            $invoices = Invoice::where('is_driver', 1)
                ->where('trip_id', $driver->trip_id)
                ->where('created_by', $driver->id)
                ->where('status', Invoice::STATUS_COMPLETED)
                ->with(['invoiceDetails.product'])
                ->get(); 

            $productsSold = $invoices->flatMap(function($invoice) {
                    return $invoice->invoiceDetails;
                })
                ->groupBy('product_id')
                ->map(function($details, $productId) {
                    $firstDetail = $details->first();
                    return [
                        'name' => $firstDetail->product ? $firstDetail->product->name : 'Unknown Product',
                        'quantity' => $details->sum('quantity')
                    ];
                })
                ->values()
                ->toArray();
            
            $trip = Trip::where('driver_id', $driver->id)
                ->orderBy('date', 'desc')
                ->first();
                
            if ($trip->type == Trip::END_TRIP) {
                $end_time = $trip->date;

                $start_trip = Trip::where('driver_id', $driver->id)
                    ->orderBy('date', 'desc')
                    ->where('type', Trip::START_TRIP) // Assuming START_TRIP is 1
                    ->first();

                $start_time = $start_trip->date ?? null;
                
                // When we have both start and end trip, return both in the array
                $tripArray = [
                    [
                        'trip_id' => $start_trip->uuid ?? '',
                        'start_time' => $start_time ?? '',
                        'type' => 'Start Trip',
                    ],
                    [
                        'trip_id' => $trip->uuid,
                        'end_time' => $end_time ?? '',
                        'type' => 'End Trip',
                    ]
                ];
            } else {
                // When we only have start trip, return only one array
                $start_time = $trip->date;
                $end_time = null;
                
                $tripArray = [
                    [
                        'trip_id' => $trip->uuid,
                        'start_time' => $start_time ?? '',
                        'type' => 'Start Trip',
                    ]
                ];
            }
            
            $result = [
                'sales' => round($totalAmount,2),
                'credit' => round($totalCreditAmount,2),
        
                'productsold' => $productsSold,

                'inventory_balance'=> $inventoryBalances,
                'trip' => $tripArray,
            ];
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.get_dashboard_successfully',
                'data' => $result
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function getLastTripSummary(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        try{

            $trip = Trip::where('driver_id', $driver->id)
            ->where('type', Trip::END_TRIP)
            ->orderBy('date', 'desc')
            ->first();

            $tripSummary = TripController::generateTripReport($trip->uuid);

            $inventoryBalances = json_decode($trip->stock_data, true);

            $productIds = array_column($inventoryBalances, 'product_id');
            $products = Product::whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            // Map product names to the inventory balances
            foreach ($inventoryBalances as &$item) {
                $item['product_name'] = $products[$item['product_id']]->name ?? 'Unknown';
            }

            $invoices = Invoice::where('is_driver', 1)
                ->where('trip_id', $trip->uuid)
                ->where('created_by', $driver->id)
                ->where('status', Invoice::STATUS_COMPLETED)
                ->with(['invoiceDetails.product'])
                ->get(); 

            $productsSold = $invoices->flatMap(function($invoice) {
                    return $invoice->invoiceDetails;
                })
                ->groupBy('product_id')
                ->map(function($details, $productId) {
                    $firstDetail = $details->first();
                    return [
                        'name' => $firstDetail->product ? $firstDetail->product->name : 'Unknown Product',
                        'quantity' => $details->sum('quantity')
                    ];
                })
                ->values()
                ->toArray();

            $summaryData = [
                'trip_summary' => [
                    'trip_id' => $tripSummary['trip_info']['trip_id'] ?? 'T-' . $driver->trip_id,
                    'driver_name' => $tripSummary['trip_info']['driver']['name'] ?? $driver->name,
                    'start_time' => $tripSummary['trip_info']['start_time'] ?? null,
                    'end_time' => $tripSummary['trip_info']['end_time'] ?? now(),
                    'trip_duration' => isset($tripSummary['trip_info']['start_time'], $tripSummary['trip_info']['end_time']) 
                        ? $this->calculateDuration($tripSummary['trip_info']['start_time'], $tripSummary['trip_info']['end_time'])
                        : null,
                ],
                'sales_summary' => [
                    'total_invoices' => $tripSummary['sales_summary']['total_invoices'] ?? 0,
                    'total_sales_orders' => $tripSummary['sales_summary']['total_sales_orders'] ?? 0,
                    'total_amount' => $tripSummary['sales_summary']['total_amount'] ?? 0,
                    'total_credit' => $tripSummary['sales_summary']['total_credit'] ?? 0,
                    'total_cash' => $tripSummary['sales_summary']['total_cash'] ?? 0,                
                ],
                'stock_summary' => $inventoryBalances, // Directly use the array
                'products_sold' => $productsSold,
            ];
            
             return response()->json([
                'success' => true,
                'message' => 'Driver Last Trip Data retrieved successfully.',
                'data' => $summaryData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get last trip: ' . $e->getMessage()
            ], 500);
        }
        
    }

    public function getCustomers(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        // Get driver's assigned customer group
        $assign = Assign::where('driver_id', $driver->id)->first();
        
        if (!$assign || !$assign->customer_group_id) {
            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'No customer group assigned to driver',
                'data' => []
            ], 200);
        }

        // Get customer group and customer IDs
        $customerGroup = CustomerGroup::where('id', $assign->customer_group_id)->first();
        
        if (!$customerGroup) {
            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Customer group not found',
                'data' => []
            ], 200);
        }

        // Get assigned customer IDs with sequence from the new structure
        $customerData = $customerGroup->customer_ids ?? [];
        
        // Handle both old format (array of IDs) and new format (array of objects with id and sequence)
        if (empty($customerData)) {
            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'No customers in the assigned group',
                'data' => []
            ], 200);
        }
        
        // Process customer data based on format
        $processedCustomerData = [];
        
        if (is_string($customerData)) {
            // Handle JSON string
            $customerData = json_decode($customerData, true);
        }
        
        if (is_array($customerData)) {
            // Check if it's the new format (array of objects with id and sequence)
            if (isset($customerData[0]) && is_array($customerData[0]) && isset($customerData[0]['id'])) {
                // New format: [{"id": 1, "sequence": 1}, {"id": 2, "sequence": 2}]
                $processedCustomerData = $customerData;
            } else {
                // Old format: [1, 2, 3] - convert to new format with default sequence
                foreach ($customerData as $index => $customerId) {
                    if (is_numeric($customerId)) {
                        $processedCustomerData[] = [
                            'id' => (int) $customerId,
                            'sequence' => $index + 1
                        ];
                    }
                }
            }
        }
        
        // Sort by sequence
        usort($processedCustomerData, function($a, $b) {
            return $a['sequence'] <=> $b['sequence'];
        });
        
        // Extract customer IDs for query
        $assignedCustomerIds = array_column($processedCustomerData, 'id');
        
        if (empty($assignedCustomerIds)) {
            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'No valid customer IDs found',
                'data' => []
            ], 200);
        }
        
        // Get customers only from assigned IDs
        $customers = Customer::whereIn('id', $assignedCustomerIds)
            ->get(['id', 'company', 'paymentterm', 'code', 'phone', 'address'])
            ->map(function($customer) use ($processedCustomerData) {
                // Find sequence for this customer
                $sequence = 0;
                foreach ($processedCustomerData as $data) {
                    if ($data['id'] == $customer->id) {
                        $sequence = $data['sequence'];
                        break;
                    }
                }
                
                return [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->company,
                    'phone' => $customer->phone,
                    'paymentterm' => $customer->paymentterm,
                    'address' => $customer->address,
                    'sequence' => $sequence
                ];
            })
            ->sortBy('sequence') // Sort by sequence
            ->values(); // Reset array keys
        
        return response()->json([
            'result' => true,
            'message' => '' . __LINE__ . $this->message_separator . 'Customer list retrieved successfully',
            'data' => $customers
        ], 200);
    }


    public function getStockCountPdf(Request $request)
    {
        // Validate session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        $inventoryCount = InventoryCount::where('driver_id', $driver->id)->where('trip_id',$driver->trip_id)->where('status', InventoryCount::STATUS_APPROVED)->first();

        if(!$inventoryCount ){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to complete Stock Count before end trip.',
                'data' => null
            ], 200);
        }

        $pdf_url = $this->generateStockCountReport($driver->id, $driver->trip_id);
        
        return response()->json([
            'result' => true,
            'message' => '' . __LINE__ . $this->message_separator . 'Customer list retrieved successfully',
            'pdf_url' => $pdf_url
        ], 200);
    }


    private function generateStockCountReport($driver_id, $trip_id)
    {
        $driverId = $driver_id;
        $tripId = $trip_id;

        $driver = Driver::find($driverId);

        $starttrip = Trip::where('driver_id', $driverId)->where('type',Trip::START_TRIP)->where('uuid', $tripId)->first();

        // Get all approved inventory counts for this trip
        $inventoryCounts = InventoryCount::where('driver_id', $driverId)
            ->where('status', InventoryCount::STATUS_APPROVED)
            ->where('trip_id', $tripId)
            ->get();

        // Process inventory counts to get summary data
        $stockCountSummary = [];
        $productData = [];
        
        foreach ($inventoryCounts as $count) {
            $items = $count->items ?? [];
            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $currentQty = $item['current_quantity'] ?? 0;
                $countedQty = $item['counted_quantity'] ?? 0;
                
                if ($productId && $countedQty !== '' && $countedQty !== null) {
                    if (!isset($stockCountSummary[$productId])) {
                        $stockCountSummary[$productId] = [
                            'product_id' => $productId,
                            'product_name' => $item['product_name'] ?? 'Product ' . $productId,
                            'current_quantity' => 0,
                            'counted_quantity' => 0,
                            'difference' => 0
                        ];
                    }
                    
                    // Sum up quantities from all counts
                    $stockCountSummary[$productId]['current_quantity'] += (float)$currentQty;
                    $stockCountSummary[$productId]['counted_quantity'] += (float)$countedQty;
                    $stockCountSummary[$productId]['difference'] = 
                        $stockCountSummary[$productId]['counted_quantity'] - 
                        $stockCountSummary[$productId]['current_quantity'];
                }
            }
        }
        
        // Convert to collection for easier handling
        $stockCounts = collect($stockCountSummary)->values();
        
        // Get product details for all products in the count
        $productIds = $stockCounts->pluck('product_id')->toArray();
        $products = \App\Models\Product::whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
        
        // Update product names from database (more accurate)
        foreach ($stockCounts as &$count) {
            $product = $products[$count['product_id']] ?? null;
            if ($product) {
                $count['product_name'] = $product->name;
                $count['product_code'] = $product->code ?? '';
            } else {
                $count['product_code'] = '';
            }
        }
        
        // Get latest approved count for approved_by info
        $latestCount = $inventoryCounts->sortByDesc('created_at')->first();
        
        // Calculate totals
        $totalCurrent = $stockCounts->sum('current_quantity');
        $totalCounted = $stockCounts->sum('counted_quantity');
        $totalDifference = $stockCounts->sum('difference');
        
        $data =[
            'company_name' => 'SF Noodles Sdn. Bhd.',
            'roc_no' => '(FKA Soon Fatt Foods Sdn Bhd) ROC No. 201001017887',
            'address' => '48, Jin TPP 1/18, Taman Industri Puchong, 47100 Puchong, Selangor',
            'phone' => 't: 03-80611490 / 012-3111531',
            'email' => 'email: account@sfnoodles.com',
            
            // Trip Information
            'salesman' => $driver->name ?? 'N/A',
            'printed_time' => Carbon::now()->format('d M Y h:i A'),
            'approved_by' => $latestCount ? (User::find($latestCount->approved_by)->name ?? '-') : '-',
            'trip_id' => 'T-' . ($starttrip->uuid ?? $tripId),
            'start_time' => $starttrip ? Carbon::parse($starttrip->date)->format('d M Y h:i A') : 'N/A',
            'end_time' => Carbon::parse(now())->format('d M Y h:i A') ,
            
            // Stock Count Data
            'stock_counts' => $stockCounts,
            'products' => $products,
            'total_current' => $totalCurrent,
            'total_counted' => $totalCounted,
            'total_difference' => $totalDifference,
            
            // Additional data for display
            'inventory_counts' => $inventoryCounts,
            'has_data' => $stockCounts->isNotEmpty(),
        ];
        
        try {
            $pdf = Pdf::loadView('reports.stockCountreport', $data);

            $pdf->setPaper('a4', 'portrait')
                    ->setOptions([
                        'isPhpEnabled' => true, 
                        'isRemoteEnabled' => true,
                        'defaultFont' => 'sans-serif',
                    ]);
            return base64_encode($pdf->output());

        } catch(Exception $e) {
            dd($e->getMessage());
            abort(404);
        }
    }
    
    //manager part
    public function managerLogin(Request $request){
        try{
            //validation
            $validator = Validator::make($request->all(), [
                'employeeid' => 'required|string',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $data = $request->all();
            $user = User::where('email', $data['employeeid'])->first();

            if (empty($user) || !$user) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'User not found.',
                    'data' => null
                ], 401);
            }
          
            if (Hash::check($data['password'], $user->password)) {
                
                $session = $user->session;
                $user->session = session_create_id();
                $user->save();

                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.login_successfully',
                    'data' => [
                        'manager' => $user,
                    ]
                ], 200);

            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_credential',
                    'data' => null
                ], 401);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function managerLogout(Request $request){
        try{
            //validation
            $validator = Validator::make($request->all(), [
                'session' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $data = $request->all();
            $user = User::where('session', $data['session'])->first();
            if(!empty($user)){
                $user->session = NULL;
                $user->save();
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.logout_successfully',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getStockRequest(Request $request)
    {
        // Validate session
        $user = User::where('session', $request->header('session'))->first();
        if(empty($user)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        
        try {
            $inventoryRequests = InventoryRequest::with([
                'driver:id,name',
                'approver:id,name',    // User who approved
                'rejector:id,name',    // User who rejected
                // Removed single product relationship since we now have multiple products
            ])->get();
            
            // Transform the data to include product names in items array
            $inventoryRequests->transform(function($request) {
                // Get approver and rejector names from relationships
                $approverName = $request->approver ? $request->approver->name : null;
                $rejectorName = $request->rejector ? $request->rejector->name : null;
                
                // Process items array to add product names
                $itemsWithProductNames = [];
                if ($request->items && is_array($request->items)) {
                    foreach ($request->items as $item) {
                        $product = Product::with('category:id,name')->find($item['product_id'] ?? null);
                        $itemsWithProductNames[] = [
                            'product_id' => $item['product_id'] ?? null,
                            'product_name' => $product ? $product->name : 'Unknown Product',
                            'quantity' => $item['quantity'] ?? 0,
                            'product_category' => $product && $product->category ? $product->category->name : null,
                            'product_code' => $product ? $product->code : null,
                            'product_price' => $product ? $product->price : null
                        ];
                    }
                }
                
                // Convert to array and add the processed data
                $requestArray = $request->toArray();
                
                // Add processed items with product names
                $requestArray['items'] = $itemsWithProductNames;
                
                // Add approver/rejector names
                $requestArray['approved_by_name'] = $approverName;
                $requestArray['rejected_by_name'] = $rejectorName;
                
                // Add useful metadata
                $requestArray['item_count'] = $request->item_count;
                $requestArray['total_quantity'] = $request->total_quantity;
                
                // Remove old single product fields if they exist
                unset($requestArray['product']);
                unset($requestArray['product_id']);
                
                return $requestArray;
            });

            return response()->json([
                'result' => true,
                'message' => '' . __LINE__ . $this->message_separator . 'Stock Request list retrieved successfully',
                'data' => $inventoryRequests
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => '' . __LINE__ . $this->message_separator . 'Failed to get stock requests: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function approveStockRequest(Request $request)
    {
        // Validate session
        $user = User::where('session', $request->header('session'))->first();
        if(empty($user)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:inventory_requests,id',
            'items' => 'sometimes|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 200);
        }
        
        $data = $request->all();
        $id = $data['id'];

        $inventoryRequest = InventoryRequest::find($id);

        if (!$inventoryRequest->canBeApproved()) {
            return response()->json([
                'result' => false,
                'message' => '' . __LINE__ . $this->message_separator . 'This request cannot be approved, the status is not in pending.',
                'data' => ''
            ], 200);
        }
        
        try {
            // Get original items from the request
            $originalItems = $inventoryRequest->items ?? [];
            
            // Check if admin wants to modify items
            if (isset($data['items']) && is_array($data['items']) && !empty($data['items'])) {
                // Admin is modifying items during approval
                $modifiedItems = $data['items'];
                
                // Validate modified items
                if (empty($modifiedItems)) {
                    return response()->json([
                        'result' => false,
                        'message' => '' . __LINE__ . $this->message_separator . 'Items array cannot be empty.',
                        'data' => null
                    ], 200);
                }
                
                // Check for duplicate products in modified items
                $productIds = array_column($modifiedItems, 'product_id');
                if (count($productIds) !== count(array_unique($productIds))) {
                    return response()->json([
                        'result' => false,
                        'message' => '' . __LINE__ . $this->message_separator . 'Duplicate products are not allowed.',
                        'data' => null
                    ], 200);
                }

                // Update the request with modified items
                $inventoryRequest->update([
                    'items' => $modifiedItems,
                    'status' => InventoryRequest::STATUS_APPROVED,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
                
                // Process each modified item
                foreach ($modifiedItems as $item) {
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
                        'Stock Request Approval - Approved by: ' . $user->name,
                    );
                }
                
                $approvalMessage = 'Stock Request approved successfully with modified items.';
                
            } else {
                // No modification, approve with original items
                if (empty($originalItems) || !is_array($originalItems)) {
                    return response()->json([
                        'result' => false,
                        'message' => '' . __LINE__ . $this->message_separator . 'No items found in this request.',
                        'data' => null
                    ], 200);
                }
                
                // Update request status
                $inventoryRequest->update([
                    'status' => InventoryRequest::STATUS_APPROVED,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
                
                // Process each original item
                foreach ($originalItems as $item) {
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
                        'Stock Request Approval - Approved by: ' . $user->name,
                    );
                }
                
                $approvalMessage = 'Stock Request approved successfully.';
            }
            
            // Add product details to response
            $itemsWithDetails = [];
            $finalItems = isset($modifiedItems) ? $modifiedItems : $originalItems;
            
            foreach ($finalItems as $item) {
                $product = Product::find($item['product_id']);
                $itemsWithDetails[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product ? $product->name : 'Unknown Product',
                    'quantity' => $item['quantity']
                ];
            }
            
            return response()->json([
                'result' => true,
                'message' => '' . __LINE__ . $this->message_separator . $approvalMessage,
                'data' => [
                    'id' => $inventoryRequest->id,
                    'driver_id' => $inventoryRequest->driver_id,
                    'trip_id' => $inventoryRequest->trip_id,
                    'items' => $itemsWithDetails,
                    'status' => $inventoryRequest->status,
                    'remarks' => $inventoryRequest->remarks,
                    'approved_by' => $inventoryRequest->approved_by,
                    'approved_by_name' => $user->name,
                    'approved_at' => $inventoryRequest->approved_at,
                    'created_at' => $inventoryRequest->created_at,
                    'updated_at' => $inventoryRequest->updated_at,
                    'item_count' => $inventoryRequest->item_count,
                    'total_quantity' => $inventoryRequest->total_quantity,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Stock Request Approval Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $data,
                'user_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'result' => false,
                'message' => '' . __LINE__ . $this->message_separator . 'Stock Request Failed to approve: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function rejectStockRequest(Request $request)
    {
        // Validate session
        $user = User::where('session', $request->header('session'))->first();
        if(empty($user)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:inventory_requests,id',
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 200);
        }
        
        $data = $request->all();
        $id = $data['id'];

        $inventoryRequest = InventoryRequest::find($id);

        if (!$inventoryRequest->canBeRejected()) {
            return response()->json([
                'result' => false,
                'message' => '' . __LINE__ . $this->message_separator . 'This request cannot be rejected, the status is not in pending.',
                'data' => ''
            ], 200);
        }
        
        try {

            $inventoryRequest->update([
                'status' => InventoryRequest::STATUS_REJECTED,
                'rejected_by' => $user->id,
                'rejection_reason' => $data['rejection_reason'],
                'rejected_at' => now(),
            ]);

            return response()->json([
                'result' => true,
                'message' => '' . __LINE__ . $this->message_separator . 'Stock Request rejected successfully',
                'data' => $inventoryRequest
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Stock Request Approval Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $data,
                'user_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'result' => false,
                'message' => '' . __LINE__ . $this->message_separator . 'Stock Request Failed to reject: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function getStockCount(Request $request)
    {
        // Validate session
        $user = User::where('session', $request->header('session'))->first();
        if(empty($user)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        
        $inventoryCounts = InventoryCount::all();
        
        // Get all unique product IDs from all inventory counts
        $allProductIds = [];
        foreach ($inventoryCounts as $count) {
            $items = $count->items;
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (isset($item['product_id'])) {
                        $allProductIds[] = $item['product_id'];
                    }
                }
            }
        }
        
        // Fetch all products in one query
        $allProductIds = array_unique($allProductIds);
        $products = Product::whereIn('id', $allProductIds)
            ->get()
            ->keyBy('id');
        
        // Format the response
        $formattedCounts = $inventoryCounts->map(function ($count) use ($products) {
            $items = $count->items;
            $formattedItems = [];
            
            if (is_array($items)) {
                $formattedItems = array_map(function ($item) use ($products) {
                    $productId = $item['product_id'];
                    $product = $products[$productId] ?? null;
                    
                    return [
                        'product_id' => $item['product_id'],
                        'product_name' => $product ? $product->name : null,
                        'product_code' => $product ? $product->code : null, // Add other product fields if needed
                        'counted_quantity' => $item['counted_quantity'],
                        'current_quantity' => $item['current_quantity']
                    ];
                }, $items);
            }
            
            // Include driver info if you have driver relationship
            $driver = null;
            if ($count->driver_id) {
                $driver = Driver::find($count->driver_id);
            }
            
            return [
                'id' => $count->id,
                'driver_id' => $count->driver_id,
                'driver_name' => $driver ? $driver->name : null,
                'items' => $formattedItems,
                'status' => $count->status,
                'remarks' => $count->remarks,
                'rejection_reason' => $count->rejection_reason,
                'approved_by' => $count->approved_by,
                'trip_id' => $count->trip_id,
                'rejected_by' => $count->rejected_by,
                'approved_at' => $count->approved_at,
                'rejected_at' => $count->rejected_at,
                'created_at' => $count->created_at,
                'updated_at' => $count->updated_at
            ];
        });

        return response()->json([
            'result' => true,
            'message' => __LINE__ . $this->message_separator . 'Stock Count list retrieved successfully',
            'data' => $formattedCounts
        ], 200);
    }

    public function approveStockCount(Request $request)
    {
        // Validate session
        $user = User::where('session', $request->header('session'))->first();
        if(empty($user)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:inventory_counts,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.counted_quantity' => 'required|numeric|min:0',
            'items.*.current_quantity' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 200);
        }
        
        $data = $request->all();
        $id = $data['id'];

        $inventoryCount = InventoryCount::find($id);

        if (!$inventoryCount) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Stock count not found',
                'data' => null
            ], 200);
        }
        
        if (!$inventoryCount->canBeApproved()) {
            return response()->json([
                'result' => false,
                'message' => '' . __LINE__ . $this->message_separator . 'This stock count request cannot be approved, the status is not in pending.',
                'data' => null
            ], 200);
        }

        try{
            // Validate that all items have counted_quantity filled
            $missingCountedItems = [];
            $formattedItems = [];
            
            foreach ($data['items'] as $item) {
                // Check if counted_quantity is provided and valid
                if (!isset($item['counted_quantity']) || 
                    (empty($item['counted_quantity']) && $item['counted_quantity'] !== '0' && $item['counted_quantity'] !== 0)) {
                    $product = Product::find($item['product_id']);
                    $missingCountedItems[] = $product ? $product->name : 'Product ID: ' . $item['product_id'];
                }
                
                // Format the item with proper data types
                $formattedItems[] = [
                    'product_id' => (string) $item['product_id'],
                    'counted_quantity' => (string) $item['counted_quantity'],
                    'current_quantity' => (int) $item['current_quantity']
                ];
            }
            
            // If there are items missing counted_quantity, return error
            if (!empty($missingCountedItems)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 
                        'Please provide counted quantity for: ' . implode(', ', $missingCountedItems),
                    'data' => null
                ], 200);
            }

            // Update the inventory count with new items and remarks
            $updateData = [
                'items' => $formattedItems,
                'status' => InventoryCount::STATUS_APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ];

            // Add remarks if provided
            if (!empty($data['remarks'])) {
                $updateData['remarks'] = $data['remarks'];
            }

            $inventoryCount->update($updateData);

            return response()->json([
                'result' => true,
                'message' => '' . __LINE__ . $this->message_separator . 'Stock Count approved successfully',
                'data' => $inventoryCount
            ], 200);

        }catch (\Exception $e){

            return response()->json([
                'result' => false,
                'message' => '' . __LINE__ . $this->message_separator . 'Stock Count Failed approved',
                'data' =>null
            ], 200);
        }
        


    }

    public function StockReturn(Request $request)
    {
        // Validate session
        $user = User::where('session', $request->header('session'))->first();
        if(empty($user)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        
        $driver = Driver::find($request->driver_id);
        if(!$driver){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver not found',
                'data' => null
            ], 200);
        }
        
        if($driver->trip_id == NULL){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Driver have to start trip before perform any Action',
                'data' => null
            ], 200);
        }

        // Updated validation for multiple items
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:drivers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 200);
        }

        try {
            // Validate items array
            $items = $request->items;
            if (!is_array($items) || empty($items)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Please add at least one item',
                    'data' => null
                ], 200);
            }

            // Check for duplicate products
            $productIds = array_column($items, 'product_id');
            if (count($productIds) !== count(array_unique($productIds))) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Duplicate products are not allowed in the same return',
                    'data' => null
                ], 200);
            }

            // Check if driver has enough stock for all items
            $errors = [];
            foreach ($items as $item) {
                $inventoryBalance = InventoryBalance::where([
                    'driver_id' => $request->driver_id,
                    'product_id' => $item['product_id']
                ])->first();

                $currentBalance = $inventoryBalance->quantity ?? 0;
                if ($currentBalance < $item['quantity']) {
                    $product = Product::find($item['product_id']);
                    $errors[] = $product->name . ': Available stock: ' . $currentBalance . ', Requested: ' . $item['quantity'];
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Insufficient stock for some items: ' . implode(', ', $errors),
                    'data' => null
                ], 200);
            }

            // Create inventory return with items array
            $inventoryReturn = InventoryReturn::create([
                'driver_id' => $request->driver_id,
                'items' => $items, // Store as JSON array
                'status' => InventoryReturn::STATUS_APPROVED, // Auto-approved
                'remarks' => $request->remarks ?? null,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'trip_id' => $driver->trip_id,
            ]);

            // Process each item
            foreach ($items as $item) {
                $inventoryBalance = InventoryBalance::firstOrNew([
                    'driver_id' => $inventoryReturn->driver_id,
                    'product_id' => $item['product_id']
                ]);

                // Subtract the returned quantity from existing balance
                $currentBalance = $inventoryBalance->quantity ?? 0;
                $inventoryBalance->quantity = $currentBalance - $item['quantity'];
                $inventoryBalance->save();

                // Create inventory transaction record for STOCK OUT
                InventoryTransaction::createTransaction(
                    $inventoryReturn->driver_id,
                    $item['product_id'],
                    $item['quantity'],
                    InventoryTransaction::TYPE_STOCK_OUT,
                    'Stock Return - Return ID: ' . $inventoryReturn->id . ' - Approved by: ' . $user->name,
                );
            }

            // Add product details to response
            $itemsWithDetails = collect($items)->map(function($item) {
                $product = Product::find($item['product_id']);
                return [
                    'product_id' => $item['product_id'],
                    'product_name' => $product ? $product->name : 'Unknown Product',
                    'quantity' => $item['quantity']
                ];
            });

            return response()->json([
                'result' => true,
                'message' => '' . __LINE__ . $this->message_separator . 'Stock Return approved successfully',
                'data' => [
                    'id' => $inventoryReturn->id,
                    'driver_id' => $inventoryReturn->driver_id,
                    'driver_name' => $driver->name,
                    'trip_id' => $inventoryReturn->trip_id,
                    'items' => $itemsWithDetails,
                    'status' => $inventoryReturn->status,
                    'remarks' => $inventoryReturn->remarks,
                    'approved_by' => $inventoryReturn->approved_by,
                    'approved_by_name' => $user->name,
                    'approved_at' => $inventoryReturn->approved_at,
                    'created_at' => $inventoryReturn->created_at,
                    'updated_at' => $inventoryReturn->updated_at,
                    'item_count' => $inventoryReturn->item_count,
                    'total_quantity' => $inventoryReturn->total_quantity,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Stock Return Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'result' => false,
                'message' => '' . __LINE__ . $this->message_separator . 'Stock Return Failed: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function getDriverProduct(Request $request)
    {
        // Validate session
        $user = User::where('session', $request->header('session'))->first();
        if(empty($user)){
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        try {
            // Get all drivers
            $drivers = Driver::where('status', 1) // Assuming you have a status field
                ->orderBy('name')
                ->get(['id', 'name']); // Select necessary fields
            
            if ($drivers->isEmpty()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'No drivers found',
                    'data' => null
                ], 200);
            }

            // Get inventory balances for all drivers
            $allDriverInventory = InventoryBalance::whereIn('driver_id', $drivers->pluck('id'))
                ->get()
                ->groupBy('driver_id')
                ->map(function($inventories) {
                    return $inventories->pluck('quantity', 'product_id')->toArray();
                })
                ->toArray();

            // Get all products with categories
            $categories = ProductCategory::with(['products' => function($query) {
                $query->select('id', 'name', 'category_id', 'price', 'status')
                    ->where('status', 1)
                    ->orderBy('name');
            }])
            ->where('status', 1)
            ->orderBy('name')
            ->get();

            // Format the response for all drivers
            $output = $drivers->map(function($driver) use ($categories, $allDriverInventory) {
                $driverInventory = $allDriverInventory[$driver->id] ?? [];
                
                $driverProducts = $categories->map(function($category) use ($driverInventory) {
                    return [
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                        'products' => $category->products->map(function($product) use ($driverInventory) {
                            // Get quantity from driver's inventory, default to 0 if not found
                            $quantity = $driverInventory[$product->id] ?? 0;
                            
                            return [
                                'id' => $product->id,
                                'name' => $product->name,
                                'price' => $product->price,
                                'quantity' => $quantity,
                                'status' => $product->getStatusTextAttribute()
                            ];
                        })
                    ];
                });

                return [
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->name,
                    'products' => $driverProducts
                ];
            });

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'Products for all drivers retrieved successfully',
                'data' => $output
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . 'Error getting driver products: ' . $e->getMessage(),
                'data' => null
            ], 200);
        }
    }
}