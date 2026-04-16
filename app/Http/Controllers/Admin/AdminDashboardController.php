<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // Today's Sales
        $todaySales = Invoice::whereDate('date', $today)->sum('net_payable');

        // Total Outstanding Due (across all customers)
        $totalDue = Invoice::sum('due_amount');

        // Total Customers
        $totalCustomers = Customer::count();

        // Total Products
        $totalProducts = Product::count();

        // Low Stock Items (< 5 units)
        $lowStockCount = Product::where('stock_quantity', '<', 5)->count();

        // Monthly Sales
        $monthlySales = Invoice::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('net_payable');

        // Recent Invoices (last 10)
        $recentInvoices = Invoice::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Low Stock Products (for alerts section)
        $lowStockProducts = Product::where('stock_quantity', '<', 5)
            ->orderBy('stock_quantity', 'asc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'totalDue',
            'totalCustomers',
            'totalProducts',
            'lowStockCount',
            'monthlySales',
            'recentInvoices',
            'lowStockProducts'
        ));
    }
}
