<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockHistory;
use App\Http\Requests\Admin\StockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // Current Stock Levels
        $products = Product::query()
            ->select('id', 'product_name', 'product_id', 'model_no', 'cost_price', 'selling_price', 'stock_quantity', 'category')
            ->when($search, function ($query, $search) {
                return $query->where('product_name', 'like', "%{$search}%")
                             ->orWhere('product_id', 'like', "%{$search}%");
            })
            ->orderBy('stock_quantity', 'asc')
            ->paginate(10, ['*'], 'products_page')
            ->withQueryString();

        // Recent History Logs
        $histories = StockHistory::with(['product:id,product_id,product_name', 'user:id,name'])
            ->select('id', 'product_id', 'type', 'quantity', 'reference_type', 'reference_id', 'note', 'date', 'created_by', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'history_page')
            ->withQueryString();

        return view('admin.stock.index', compact('products', 'histories', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // QUERY OPTIMIZATION
        $products = Product::select('id', 'product_id', 'product_name', 'stock_quantity')
            ->orderBy('product_name', 'asc')
            ->get();
        return view('admin.stock.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $product = Product::lockForUpdate()->findOrFail($request->product_id);

                // 1. Update Product Stock
                $product->increment('stock_quantity', $request->quantity);

                // 2. Record in Stock History
                StockHistory::create([
                    'product_id' => $product->id,
                    'type' => 'IN',
                    'quantity' => $request->quantity,
                    'reference_type' => 'manual',
                    'reference_id' => null,
                    'note' => $request->note ?? 'Manual Stock Entry',
                    'date' => $request->date,
                    'created_by' => Auth::id(),
                ]);
            });

            return redirect()->route('stock.index')
                ->with('success', 'Stock updated successfully.');
        } catch (Exception $e) {
            Log::error('Stock update failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return back()->withInput()->with('error', 'Error updating stock. Please try again.');
        }
    }
}
