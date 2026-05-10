<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PriceApprovalRequest;
use App\Models\Product;
use App\Models\StaffProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    // PRODUCT APPROVAL FLOW
    // PRICE APPROVAL SYSTEM
    public function index()
    {
        $productRequests = StaffProductRequest::with(['requester:id,name', 'reviewer:id,name', 'product:id,product_id,product_name'])
            ->latest()
            ->paginate(15, ['*'], 'products_page');

        $priceRequests = PriceApprovalRequest::with([
                'requester:id,name',
                'reviewer:id,name',
                'customer' => fn ($query) => $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name'])),
                'product:id,product_id,product_name,selling_price',
            ])
            ->latest()
            ->paginate(15, ['*'], 'prices_page');

        return view('admin.approvals.index', compact('productRequests', 'priceRequests'));
    }

    public function approveProduct(Request $request, StaffProductRequest $productRequest)
    {
        if ($productRequest->status !== 'pending') {
            return back()->with('error', 'This product request has already been reviewed.');
        }

        $data = $request->validate([
            'approved_product_name' => ['required', 'string', 'max:255'],
            'generated_product_id' => ['required', 'string', 'max:50', 'unique:products,product_id'],
            'model_no' => ['nullable', 'string', 'max:100'],
            'pack_size' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'approved_cost_price' => ['required', 'numeric', 'min:0'],
            'approved_selling_price' => ['required', 'numeric', 'min:0', 'gte:approved_cost_price'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($productRequest, $data) {
            // STAFF PRODUCT REQUEST
            // PRODUCT APPROVAL FLOW
            $product = Product::create([
                'product_name' => $data['approved_product_name'],
                'product_id' => $data['generated_product_id'],
                'model_no' => $data['model_no'] ?? null,
                'pack_size' => $data['pack_size'] ?? null,
                'category' => $data['category'] ?: 'Medical Equipment',
                'cost_price' => $data['approved_cost_price'],
                'selling_price' => $data['approved_selling_price'],
                'stock_quantity' => 0,
            ]);

            $productRequest->update([
                ...$data,
                'product_id' => $product->id,
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'Product request approved. Product is active with zero stock.');
    }

    public function rejectProduct(Request $request, StaffProductRequest $productRequest)
    {
        if ($productRequest->status !== 'pending') {
            return back()->with('error', 'This product request has already been reviewed.');
        }

        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $productRequest->update([
            'status' => 'rejected',
            'admin_notes' => $data['admin_notes'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Product request rejected.');
    }

    public function approvePrice(Request $request, PriceApprovalRequest $priceRequest)
    {
        if ($priceRequest->status !== 'pending') {
            return back()->with('error', 'This price request has already been reviewed.');
        }

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // PRICE APPROVAL SYSTEM
        $priceRequest->update([
            'status' => 'approved',
            'admin_notes' => $data['admin_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Special price request approved.');
    }

    public function rejectPrice(Request $request, PriceApprovalRequest $priceRequest)
    {
        if ($priceRequest->status !== 'pending') {
            return back()->with('error', 'This price request has already been reviewed.');
        }

        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $priceRequest->update([
            'status' => 'rejected',
            'admin_notes' => $data['admin_notes'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Special price request rejected.');
    }
}
