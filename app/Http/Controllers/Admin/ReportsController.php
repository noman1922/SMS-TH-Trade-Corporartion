<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Sales Report (Daily, Monthly, Yearly)
     */
    public function salesReport(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::today()->toDateString());
        $toDate = $request->input('to_date', Carbon::today()->toDateString());

        $query = Invoice::whereBetween('date', [$fromDate, $toDate]);

        $summary = [
            'total_invoices' => (clone $query)->count(),
            'total_amount' => (clone $query)->sum('net_payable'),
            'total_received' => (clone $query)->sum('received_amount'),
            'total_due' => (clone $query)->sum('due_amount'),
        ];

        $invoices = Invoice::whereBetween('date', [$fromDate, $toDate])
            ->with('customer')
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('admin.reports.sales', compact('invoices', 'summary', 'fromDate', 'toDate'));
    }

    /**
     * Profit Report
     */
    public function profitReport(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', Carbon::now()->toDateString());

        // Calculate profit per item using eager loaded joins
        $profitData = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereBetween('invoices.date', [$fromDate, $toDate])
            ->select(
                DB::raw('COALESCE(SUM(invoice_items.quantity * invoice_items.unit_price), 0) as total_sales'),
                DB::raw('COALESCE(SUM(invoice_items.quantity * invoice_items.cost_price), 0) as total_cost'),
                DB::raw('COALESCE(SUM(invoice_items.quantity * (invoice_items.unit_price - invoice_items.cost_price)), 0) as gross_profit')
            )
            ->first();

        // Monthly breakdown
        $monthlyProfit = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereYear('invoices.date', Carbon::parse($fromDate)->year)
            ->select(
                DB::raw('EXTRACT(MONTH FROM invoices.date) as month'),
                DB::raw('COALESCE(SUM(invoice_items.quantity * (invoice_items.unit_price - invoice_items.cost_price)), 0) as profit')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.reports.profit', compact('profitData', 'monthlyProfit', 'fromDate', 'toDate'));
    }

    /**
     * Stock Valuation Report
     */
    public function stockReport()
    {
        $products = Product::orderBy('stock_quantity', 'asc')->paginate(25);
        
        $totalValuation = Product::select(
            DB::raw('COALESCE(SUM(stock_quantity * cost_price), 0) as total')
        )->value('total');

        $lowStockCount = Product::where('stock_quantity', '<', 5)->count();

        return view('admin.reports.stock', compact('products', 'totalValuation', 'lowStockCount'));
    }

    /**
     * Due Report
     */
    public function dueReport()
    {
        $customers = Customer::withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->get()
            ->filter(function($customer) {
                return $customer->current_due > 0;
            })->sortByDesc('current_due');

        $totalOutstanding = $customers->sum('current_due');

        return view('admin.reports.due', compact('customers', 'totalOutstanding'));
    }

    /**
     * Customer Ledger Statement
     */
    public function customerLedger(Request $request)
    {
        $customers = Customer::orderBy('customer_name', 'asc')->get();
        $customerId = $request->input('customer_id');
        
        $ledger = collect();
        $customer = null;

        if ($customerId) {
            $customer = Customer::findOrFail($customerId);
            
            // Get Invoices (Debits)
            $invoices = $customer->invoices()
                ->select('date', 'invoice_no as reference', 'net_payable as debit', DB::raw('0 as credit'), DB::raw("'Invoice' as type"))
                ->get();
            
            // Get Payments (Credits)
            $payments = $customer->payments()
                ->select('date', DB::raw("CONCAT('Payment #', id) as reference"), DB::raw('0 as debit'), 'amount as credit', DB::raw("'Payment' as type"))
                ->get();

            // Merge and Sort
            $ledger = $invoices->concat($payments)->sortBy('date');
        }

        return view('admin.reports.ledger', compact('customers', 'customer', 'ledger'));
    }
}
