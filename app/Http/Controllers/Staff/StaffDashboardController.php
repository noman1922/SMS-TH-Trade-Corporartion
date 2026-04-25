<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    public function index()
    {
        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // STAFF DASHBOARD FIX
        $myBillEntries = Invoice::where('user_id', auth()->id())->count();
        $itemsInPossession = Product::sum('stock_quantity');

        return view('staff.dashboard', compact('myBillEntries', 'itemsInPossession'));
    }

    public function sales(Request $request)
    {
        // STAFF DASHBOARD FIX
        $invoices = Invoice::with('customer:id,customer_name')
            ->select('id', 'invoice_no', 'customer_id', 'user_id', 'net_payable', 'received_amount', 'due_amount', 'date', 'created_at')
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('staff.sales', compact('invoices'));
    }
}
