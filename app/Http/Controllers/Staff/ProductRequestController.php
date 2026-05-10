<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StaffProductRequest;
use Illuminate\Http\Request;

class ProductRequestController extends Controller
{
    // STAFF PRODUCT REQUEST
    public function index()
    {
        $requests = StaffProductRequest::with(['reviewer:id,name', 'product:id,product_id,product_name'])
            ->where('requested_by', auth()->id())
            ->latest()
            ->paginate(15);

        return view('staff.product-requests.index', compact('requests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'requested_product_name' => ['required', 'string', 'max:255'],
            'requested_price' => ['required', 'numeric', 'min:0'],
            'model_no' => ['nullable', 'string', 'max:100'],
            'pack_size' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        StaffProductRequest::create([
            ...$data,
            'requested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Product request submitted for admin approval.');
    }
}
