<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PriceApprovalRequest;
use App\Models\Product;
use App\Services\CustomerPricingService;
use Illuminate\Http\Request;

class PriceApprovalRequestController extends Controller
{
    // PRICE APPROVAL SYSTEM
    // STAFF PRICE RESTRICTION
    public function index()
    {
        $requests = PriceApprovalRequest::with([
                'customer' => fn ($query) => $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name'])),
                'product:id,product_id,product_name',
                'reviewer:id,name',
            ])
            ->where('requested_by', auth()->id())
            ->latest()
            ->paginate(15);

        $customers = Customer::select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name']))
            ->orderBy(Customer::displayOrderColumn())
            ->get();

        $products = Product::select('id', 'product_id', 'product_name', 'selling_price')
            ->where('stock_quantity', '>', 0)
            ->orderBy('product_name')
            ->get();

        return view('staff.price-requests.index', compact('requests', 'customers', 'products'));
    }

    public function store(Request $request, CustomerPricingService $pricingService)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'requested_price' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $current = $data['customer_id']
            ? $pricingService->resolvePrice((int) $data['customer_id'], (int) $data['product_id'])
            : ['price' => Product::whereKey($data['product_id'])->value('selling_price') ?? 0];

        PriceApprovalRequest::create([
            ...$data,
            'requested_by' => auth()->id(),
            'current_price' => round((float) $current['price'], 2),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Special price request submitted for admin approval.');
    }
}
