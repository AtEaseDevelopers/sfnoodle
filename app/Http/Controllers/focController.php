<?php

namespace App\Http\Controllers;

use App\DataTables\focDataTable;
use App\Http\Requests;
use App\Http\Requests\CreatefocRequest;
use App\Http\Requests\UpdatefocRequest;
use App\Repositories\focRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use App\Models\foc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class focController extends AppBaseController
{
    /** @var focRepository $focRepository*/
    private $focRepository;

    public function __construct(focRepository $focRepo)
    {
        $this->focRepository = $focRepo;
    }

    /**
     * Display a listing of the foc.
     *
     * @param focDataTable $focDataTable
     *
     * @return Response
     */
    public function index(focDataTable $focDataTable)
    {
        return $focDataTable->render('focs.index');
    }

    /**
     * Show the form for creating a new foc.
     *
     * @return Response
     */
    public function create()
    {
        // Get all customers for the multi-select dropdown
        $customerItems = \App\Models\Customer::pluck('company', 'id')->toArray();
        $productData = \App\Models\Product::all(); // Assuming you have this
        
        return view('focs.create', compact('customerItems', 'productData'));
    }

    /**
     * Store a newly created foc in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:customers,id',
            'quantity' => 'required|numeric|min:0',
            'free_product_id' => 'required|exists:products,id',
            'free_quantity' => 'required|numeric|min:0',
            'startdate' => 'required|date',
            'enddate' => 'required|date|after_or_equal:startdate',
            'status' => 'nullable|in:0,1'
        ], [
            'customer_ids.required' => 'Please select at least one customer.',
            'customer_ids.min' => 'Please select at least one customer.',
            'product_id.required' => 'Please select a product.',
            'free_product_id.required' => 'Please select a free product.',
            'enddate.after_or_equal' => 'End date must be after or equal to start date.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        $input = $request->all();
        
        // Handle multiple customers
        $customerIds = $input['customer_ids'] ?? [];
        
        // If only one customer is selected (backward compatibility)
        if (isset($input['customer_id']) && empty($customerIds)) {
            $customerIds = [$input['customer_id']];
        }
        
        // Remove customer_ids from input to avoid issues with repository
        unset($input['customer_ids']);
        
        $createdCount = 0;
        $errors = [];
        
        foreach ($customerIds as $customerId) {
            try {
                $focData = $input;
                $focData['customer_id'] = $customerId;
                $focData['startdate'] = date_create($focData['startdate']);
                $focData['enddate'] = date_create($focData['enddate'] . ' 23:59:59');
                
                $foc = $this->focRepository->create($focData);
                $createdCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to create FOC for customer ID: $customerId";
                \Log::error("FOC creation failed for customer $customerId: " . $e->getMessage());
            }
        }
        
        if ($createdCount > 0) {
            if ($createdCount > 1) {
                Flash::success("$createdCount FOC entries saved successfully.");
            } else {
                Flash::success(__('focs.foc_saved_successfully'));
            }
        }
        
        if (!empty($errors)) {
            Flash::warning("Some entries failed: " . implode(", ", $errors));
        }

        return redirect(route('focs.index'));
    }

    /**
     * Display the specified foc.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $foc = $this->focRepository->find($id);

        if (empty($foc)) {
            Flash::error(__('focs.foc_not_found'));

            return redirect(route('focs.index'));
        }

        return view('focs.show')->with('foc', $foc);
    }

    /**
     * Show the form for editing the specified foc.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $foc = $this->focRepository->find($id);
        
        // Get all customers for the dropdown (single select for edit)
        $customerItems = \App\Models\Customer::pluck('company', 'id')->toArray();
        $productData = \App\Models\Product::all(); // Assuming you have this

        if (empty($foc)) {
            Flash::error(__('focs.foc_not_found'));
            return redirect(route('focs.index'));
        }

        return view('focs.edit', compact('foc', 'customerItems', 'productData'));
    }

    /**
     * Update the specified foc in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $foc = $this->focRepository->find($id);

        if (empty($foc)) {
            Flash::error(__('focs.foc_not_found'));

            return redirect(route('focs.index'));
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'quantity' => 'required|numeric|min:0',
            'free_product_id' => 'required|exists:products,id',
            'free_quantity' => 'required|numeric|min:0',
            'startdate' => 'required|date',
            'enddate' => 'required|date|after_or_equal:startdate',
            'status' => 'nullable|in:0,1'
        ], [
            'customer_id.required' => 'Please select a customer.',
            'product_id.required' => 'Please select a product.',
            'free_product_id.required' => 'Please select a free product.',
            'enddate.after_or_equal' => 'End date must be after or equal to start date.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        $input = $request->all();
        
        // For update, we only handle single customer (maintain existing functionality)
        $input['startdate'] = date_create($input['startdate']);
        $input['enddate'] = date_create($input['enddate'] . ' 23:59:59');

        $foc = $this->focRepository->update($input, $id);

        Flash::success(__('focs.foc_updated_successfully'));

        return redirect(route('focs.index'));
    }

    /**
     * Remove the specified foc from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $foc = $this->focRepository->find($id);

        if (empty($foc)) {
            Flash::error(__('focs.foc_not_found'));

            return redirect(route('focs.index'));
        }

        $this->focRepository->delete($id);

        Flash::success(__('focs.foc_deleted_successfully'));

        return redirect(route('focs.index'));
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];

        $count = 0;

        foreach ($ids as $id) {

            $foc = $this->focRepository->find($id);

            $count = $count + foc::destroy($id);
        }

        return $count;
    }

    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];

        $count = foc::whereIn('id',$ids)->update(['status'=>$status]);

        return $count;
    }
}