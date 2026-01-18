<?php

namespace App\Http\Controllers;

use App\DataTables\ProductCategoryDataTable;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductCategoryDataTable $dataTable, Request $request)
    {
        // Get status options for filter
        $statuses = ProductCategory::getStatusOptions();

        // Pass filter parameters to DataTable
        $dataTable = $dataTable
            ->with([
                'status' => $request->get('status', 'all'),
            ]);

        // Return DataTable for AJAX requests
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        // Return view with DataTable for regular requests
        return $dataTable->render('product_categories.index', compact('statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ProductCategory::$rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productCategory = ProductCategory::create([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product category created successfully.',
                'data' => $productCategory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = ProductCategory::withCount('products')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $productCategory = ProductCategory::findOrFail($id);

        // Update validation rules to ignore current record for unique name
        $rules = ProductCategory::$rules;
        $rules['name'] = 'required|string|max:255|unique:product_categories,name,' . $id;

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productCategory->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product category updated successfully.',
                'data' => $productCategory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $productCategory = ProductCategory::findOrFail($id);

        // Check if category has products
        if ($productCategory->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category because it has products assigned.'
            ], 403);
        }

        try {
            $productCategory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product category deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active categories for dropdown
     */
    public function getActiveCategories()
    {
        $categories = ProductCategory::active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}