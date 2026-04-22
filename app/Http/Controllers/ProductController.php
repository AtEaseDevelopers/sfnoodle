<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDataTable;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\ProductRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\SpecialPrice;
use App\Models\Foc;
use App\Models\Driver;
use Illuminate\Support\Facades\Session;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends AppBaseController
{
    /** @var ProductRepository $productRepository*/
    private $productRepository;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepository = $productRepo;
    }

    /**
     * Display a listing of the Product.
     *
     * @param ProductDataTable $productDataTable
     *
     * @return Response
     */
    public function index(ProductDataTable $productDataTable)
    {
        return $productDataTable->render('products.index');
    }

    /**
     * Show the form for creating a new Product.
     *
     * @return Response
     */
    public function create()
    {
        // Get unique categories from existing products
        $existingCategories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();
        
        // Sort categories alphabetically
        sort($existingCategories);
        $drivers = Driver::orderBy('name')->get();

        return view('products.create', compact('existingCategories', 'drivers'));
    }

    /**
     * Store a newly created Product in storage.
     *
     * @param CreateProductRequest $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $rules = [
            'code' => 'required|string|max:255|unique:products,code', 
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'uom' => 'required|string|max:50',
            'status' => 'required|integer|in:0,1',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',  // Changed to file
            'tiered_pricing' => 'nullable|array',
            'tiered_pricing.*.quantity' => 'required_with:tiered_pricing.*.price|integer|min:1|distinct',
            'tiered_pricing.*.price' => 'required_with:tiered_pricing.*.quantity|numeric|min:0',
            'blocked_drivers' => 'nullable|array', 
            'blocked_drivers.*' => 'exists:drivers,id'
        ];

        $messages = [
            'code.required' => 'Product code is required',
            'code.unique' => 'This product code already exists',
            'name.required' => 'Product name is required',
            'price.required' => 'Product price is required',
            'category.required' => 'Category is required',
            'uom.required' => 'Unit of measurement is required',
            'image.file' => 'The uploaded file must be a valid file',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif',
            'image.max' => 'The image size must not exceed 2MB',
            'tiered_pricing.*.quantity.required_with' => 'Please enter quantity for each tier',
            'tiered_pricing.*.price.required_with' => 'Please enter price for each tier',
            'tiered_pricing.*.quantity.distinct' => 'Quantity values must be unique',
            'tiered_pricing.*.quantity.min' => 'Quantity must be at least 1',
            'tiered_pricing.*.price.min' => 'Price must be at least 0',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if(str_contains($input['name'], '"')){
            return Redirect::back()->withInput($input)->withErrors('The name cannot contain double quote');
        }

        if(str_contains($input['name'], "'")){
            return Redirect::back()->withInput($input)->withErrors('The name cannot contain single quote');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($input['code']) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('product-images', $imageName, 'public');
            $input['image_path'] = '/' . $imagePath;
        }
        $input['blocked_drivers'] = $request->input('blocked_drivers', []);

        $product = $this->productRepository->create($input);

        Flash::success($input['code'] . __('products.saved_successfully'));

        return redirect(route('products.index'));
    }

    /**
     * Display the specified Product.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error(__('products.product_not_found'));

            return redirect(route('products.index'));
        }

        return view('products.show')->with('product', $product);
    }

    /**
     * Show the form for editing the specified Product.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error(__('products.product_not_found'));
            return redirect(route('products.index'));
        }

        // Get unique categories from existing products
        $existingCategories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();
        
        // Sort categories alphabetically
        sort($existingCategories);
        $drivers = Driver::orderBy('name')->get();

        return view('products.edit', compact('product', 'existingCategories', 'drivers'));
    }

    /**
     * Update the specified Product in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error(__('products.product_not_found'));
            return redirect(route('products.index'));
        }

        $rules = [
            'code' => 'required|string|max:255|unique:products,code,' . $id, 
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'uom' => 'required|string|max:50',
            'status' => 'required|integer|in:0,1',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048'  // Changed to file
        ];

        $messages = [
            'code.required' => 'Product code is required',
            'code.unique' => 'This product code already exists',
            'name.required' => 'Product name is required',
            'price.required' => 'Product price is required',
            'category.required' => 'Category is required',
            'uom.required' => 'Unit of measurement is required',
            'image.file' => 'The uploaded file must be a valid file',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif',
            'image.max' => 'The image size must not exceed 2MB'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();

        if(str_contains($input['name'], '"')){
            return Redirect::back()->withInput($input)->withErrors('The name cannot contain double quote');
        }

        if(str_contains($input['name'], "'")){
            return Redirect::back()->withInput($input)->withErrors('The name cannot contain single quote');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_path && file_exists(public_path($product->image_path))) {
                $oldImagePath = str_replace('/', '', $product->image_path);
                Storage::disk('public')->delete($oldImagePath);
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($input['code']) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('product-images', $imageName, 'public');
            $input['image_path'] = '/' . $imagePath;
        }
        $input['blocked_drivers'] = $request->input('blocked_drivers', []);

        $product = $this->productRepository->update($input, $id);

        Flash::success($product->code . __('products.updated_successfully'));

        return redirect(route('products.index'));
    }
    
    /**
     * Remove image from product
     */
    public function removeImage($id)
    {
        $id = Crypt::decrypt($id);
        $product = $this->productRepository->find($id);
        
        if (empty($product)) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }
        
        // Delete image file
        if ($product->image_path && file_exists(public_path($product->image_path))) {
            $oldImagePath = str_replace('storage/', '', $product->image_path);
            Storage::disk('public')->delete($oldImagePath);
        }
        
        // Update database
        $product->image_path = null;
        $product->save();
        
        return response()->json(['success' => true, 'message' => 'Image removed successfully']);
    }
    
    /**
     * Remove the specified Product from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error(__('products.product_not_found'));

            return redirect(route('products.index'));
        }
        
        // Delete product image if exists
        if ($product->image_path && file_exists(public_path($product->image_path))) {
            $imagePath = str_replace('storage/', '', $product->image_path);
            Storage::disk('public')->delete($imagePath);
        }

        $SpecialPrice = SpecialPrice::where('product_id',$id)->get()->toArray();
        if(count($SpecialPrice)>0){
            Flash::error('Unable to delete '.$product->name.', '.$product->name.' is being used in Special Price');
            return redirect(route('products.index'));
        }

        $foc = Foc::where('product_id',$id)->get()->toArray();
        if(count($foc)>0){
            Flash::error('Unable to delete '.$product->name.', '.$product->name.' is being used in Foc');
            return redirect(route('products.index'));
        }

        $this->productRepository->delete($id);

        Flash::success($product->code.__('products.deleted_successfully'));

        return redirect(route('products.index'));
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];

        $count = 0;

        foreach ($ids as $id) {
            $Invoice = InvoiceDetail::where('product_id',$id)->get()->toArray();
            if(count($Invoice)>0){
                continue;
            }

            $SpecialPrice = SpecialPrice::where('product_id',$id)->get()->toArray();
            if(count($SpecialPrice)>0){
                continue;
            }

            $foc = Foc::where('product_id',$id)->get()->toArray();
            if(count($foc)>0){
                continue;
            }
            
            // Delete product image if exists
            $product = Product::find($id);
            if ($product && $product->image_path && file_exists(public_path($product->image_path))) {
                $imagePath = str_replace('storage/', '', $product->image_path);
                Storage::disk('public')->delete($imagePath);
            }

            $count = $count + Product::destroy($id);
        }

        return $count;
    }

    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];

        $count = Product::whereIn('id',$ids)->update(['status'=>$status]);

        return $count;
    }
    
    public function syncXero(Request $req)
    {
        try {
            $redirect_uri = config('app.url') . '/products/sync-xero';
            $xero = new XeroController($redirect_uri);

            if ($req->has('ids')) {
                $ids = explode(',', $req->ids);
                Session::put('ids_to_sync_xero', $ids);
            }
            // Get Xero's access token
            if ($req->has('code')) {
                $res = $xero->getToken($req->code);
                if (!$res->ok()) {
                    throw new Exception('Failed to get xero access token.');
                }
            }
            // Xero auth
            $res = $xero->auth();
            if ($res !== true) {
                return $res;
            }
            // Sync products
            $ids = Session::get('ids_to_sync_xero');
            $products = Product::whereIn('id',$ids)->get();
            
            for ($i = 0; $i < count($products) ;$i++) {
                $res = $xero->createItem($products[$i]->code, $products[$i]->name, $products[$i]->price);

                if (!$res->ok()) {  
                    throw new Exception('Failed to sync product.');
                }
            }
            
            Flash::success('Products synced to Xero.');
            return redirect(route('products.index'));
        } catch (\Throwable $th) {
            report($th);
            
            Flash::error('Something went wrong. Please contact administator.');
            return redirect(route('products.index'));
        }
    }
}