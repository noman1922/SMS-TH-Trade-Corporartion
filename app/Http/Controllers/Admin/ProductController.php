<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\Admin\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = in_array((int) $request->input('per_page', 20), [20, 50, 100], true)
            ? (int) $request->input('per_page', 20)
            : 20;

        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // PRODUCT PAGINATION IMPROVEMENT
        $products = Product::query()
            ->select('id', 'product_name', 'product_id', 'model_no', 'pack_size', 'cost_price', 'selling_price', 'stock_quantity', 'category', 'created_at')
            ->when($search, function ($query, $search) {
                // POS SEARCH IMPROVEMENT
                return $query->where('product_name', 'like', "%{$search}%")
                             ->orWhere('model_no', 'like', "%{$search}%")
                             ->orWhere('product_id', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                Product::create($request->validated());
            });

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully.');
        } catch (Exception $e) {
            Log::error('Product creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error creating product. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        try {
            DB::transaction(function () use ($request, $product) {
                $product->update($request->validated());
            });

            return redirect()->route('products.index')
                ->with('success', 'Product updated successfully.');
        } catch (Exception $e) {
            Log::error('Product update failed', ['product_id' => $product->id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error updating product. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (Exception $e) {
            Log::error('Product deletion failed', ['product_id' => $product->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting product. Please try again.');
        }
    }
}
