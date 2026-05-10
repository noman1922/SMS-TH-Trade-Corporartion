<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\PriceApprovalRequest;
use App\Models\Product;
use App\Models\StaffProductRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // Dashboard totals are loaded through compact aggregate queries to reduce Supabase round trips.
        // FINANCIAL CALCULATION FIX
        $salesSummary = Invoice::query()
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN date = ? THEN net_payable ELSE 0 END), 0) as today_sales,
                 COALESCE(SUM(CASE WHEN date BETWEEN ? AND ? THEN net_payable ELSE 0 END), 0) as monthly_sales',
                [$today, $startOfMonth, $endOfMonth]
            )
            ->first();

        $todaySales = round((float) $salesSummary->today_sales, 2);
        $monthlySales = round((float) $salesSummary->monthly_sales, 2);

        $invoiceTotals = DB::table('invoices')
            ->select('customer_id', DB::raw('COALESCE(SUM(net_payable), 0) as total_purchased'))
            ->groupBy('customer_id');

        $paymentTotals = DB::table('payments')
            ->select('customer_id', DB::raw('COALESCE(SUM(amount), 0) as total_paid'))
            ->groupBy('customer_id');

        $customerDueRows = DB::table('customers')
            ->leftJoinSub($invoiceTotals, 'invoice_totals', 'invoice_totals.customer_id', '=', 'customers.id')
            ->leftJoinSub($paymentTotals, 'payment_totals', 'payment_totals.customer_id', '=', 'customers.id')
            ->selectRaw(
                'CASE
                    WHEN customers.previous_due + COALESCE(invoice_totals.total_purchased, 0) - COALESCE(payment_totals.total_paid, 0) > 0
                    THEN customers.previous_due + COALESCE(invoice_totals.total_purchased, 0) - COALESCE(payment_totals.total_paid, 0)
                    ELSE 0
                 END as current_due'
            );

        $customerSummary = DB::query()
            ->fromSub($customerDueRows, 'customer_due_rows')
            ->selectRaw('COUNT(*) as total_customers, COALESCE(SUM(current_due), 0) as total_due')
            ->first();

        // FINANCIAL CALCULATION FIX
        $totalDue = round((float) $customerSummary->total_due, 2);
        $totalCustomers = (int) $customerSummary->total_customers;

        $productSummary = Product::query()
            ->selectRaw('COUNT(*) as total_products, COALESCE(SUM(CASE WHEN stock_quantity < 5 THEN 1 ELSE 0 END), 0) as low_stock_count')
            ->first();

        $totalProducts = (int) $productSummary->total_products;
        $lowStockCount = (int) $productSummary->low_stock_count;

        // Recent Invoices (last 10)
        // CUSTOMER_ID MIGRATION FIX
        // SAFE CUSTOMER QUERY
        $recentInvoices = Invoice::with(['customer' => function ($query) {
                $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name']));
            }])
            ->select('id', 'invoice_no', 'customer_id', 'net_payable', 'received_amount', 'due_amount', 'date', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Low Stock Products (for alerts section)
        $lowStockProducts = Product::where('stock_quantity', '<', 5)
            ->select('id', 'product_id', 'product_name', 'stock_quantity')
            ->orderBy('stock_quantity', 'asc')
            ->limit(5)
            ->get();

        // STAFF PRODUCT REQUEST
        // PRICE APPROVAL SYSTEM
        // SAFE DASHBOARD QUERY — guard against tables not yet migrated
        $pendingProductRequests = Schema::hasTable('staff_product_requests')
            ? StaffProductRequest::with('requester:id,name')
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $pendingPriceRequests = Schema::hasTable('price_approval_requests')
            ? PriceApprovalRequest::with(['requester:id,name', 'product:id,product_id,product_name'])
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        return view('admin.dashboard', compact(
            'todaySales',
            'totalDue',
            'totalCustomers',
            'totalProducts',
            'lowStockCount',
            'monthlySales',
            'recentInvoices',
            'lowStockProducts',
            'pendingProductRequests',
            'pendingPriceRequests'
        ));
    }
}
