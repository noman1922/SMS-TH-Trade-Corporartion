<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PriceApprovalRequest;
use App\Models\Product;
use App\Models\StaffProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StaffDashboardController extends Controller
{
    public function index()
    {
        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // STAFF DASHBOARD FIX
        $myBillEntries = Invoice::where('user_id', auth()->id())->count();
        $itemsInPossession = Product::sum('stock_quantity');
        // STAFF PRODUCT REQUEST
        // PRICE APPROVAL SYSTEM
        // SAFE DASHBOARD QUERY — guard against tables not yet migrated
        $pendingProductRequests = Schema::hasTable('staff_product_requests')
            ? StaffProductRequest::where('requested_by', auth()->id())->where('status', 'pending')->count()
            : 0;
        $pendingPriceRequests = Schema::hasTable('price_approval_requests')
            ? PriceApprovalRequest::where('requested_by', auth()->id())->where('status', 'pending')->count()
            : 0;
        $myCollections = Payment::where('created_by', auth()->id())->count();

        $recentInvoices = Invoice::with(['customer' => function ($query) {
                $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name']));
            }])
            ->select('id', 'invoice_no', 'customer_id', 'net_payable', 'received_amount', 'due_amount', 'date', 'created_at')
            ->where('user_id', auth()->id())
            ->latest('date')
            ->latest('created_at')
            ->limit(8)
            ->get();

        $recentCollections = Payment::with([
                'customer' => fn ($query) => $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name'])),
                'invoice:id,invoice_no',
            ])
            ->select('id', 'customer_id', 'invoice_id', 'amount', 'date', 'created_by')
            ->where('created_by', auth()->id())
            ->latest('date')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('staff.dashboard', compact(
            'myBillEntries',
            'itemsInPossession',
            'pendingProductRequests',
            'pendingPriceRequests',
            'myCollections',
            'recentInvoices',
            'recentCollections'
        ));
    }

    public function sales(Request $request)
    {
        // STAFF DASHBOARD FIX
        // CUSTOMER_ID MIGRATION FIX
        // SAFE CUSTOMER QUERY
        $invoices = Invoice::with(['customer' => function ($query) {
                $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name']));
            }])
            ->select('id', 'invoice_no', 'customer_id', 'user_id', 'net_payable', 'received_amount', 'due_amount', 'date', 'created_at')
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('staff.sales', compact('invoices'));
    }
}
