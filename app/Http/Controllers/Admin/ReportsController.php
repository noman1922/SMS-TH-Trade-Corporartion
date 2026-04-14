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
            'total_invoices' => $query->count(),
            'total_amount' => $query->sum('net_payable'),
            'total_received' => $query->sum('received_amount'),
            'total_due' => $query->sum('due_amount'),
        ];

        $invoices = $query->with('customer')->orderBy('date', 'desc')->paginate(20);

        return view('admin.reports.sales', compact('invoices', 'summary', 'fromDate', 'toDate'));
    }

    /**
     * Profit Report
     */
    public function profitReport(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', Carbon::now()->toDateString());

        // Calculate profit per item
        $profitData = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereBetween('invoices.date', [$fromDate, $toDate])
            ->select(
                DB::raw('SUM(invoice_items.quantity * invoice_items.unit_price) as total_sales'),
                DB::raw('SUM(invoice_items.quantity * invoice_items.cost_price) as total_cost'),
                DB::raw('SUM(invoice_items.quantity * (invoice_items.unit_price - invoice_items.cost_price)) as gross_profit')
            )
            ->first();

        // Monthly breakdown
        $monthlyProfit = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereYear('invoices.date', Carbon::parse($fromDate)->year)
            ->select(
                DB::raw('EXTRACT(MONTH FROM invoices.date) as month'),
                DB::raw('SUM(invoice_items.quantity * (invoice_items.unit_price - invoice_items.cost_price)) as profit')
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
        $products = Product::all();
        
        $totalValuation = $products->sum(function($product) {
            return $product->stock_quantity * $product->cost_price;
        });

        $lowStockProducts = Product::where('stock_quantity', '<', 5)->get();

        return view('admin.reports.stock', compact('products', 'totalValuation', 'lowStockProducts'));
    }

    /**
     * Due Report
     */
    public function dueReport()
    {
        $customers = Customer::all()->filter(function($customer) {
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
        
        $ledger = [];
        $customer = null;

        if ($customerId) {
            $customer = Customer::findOrFail($customerId);
            
            // Get Invoices (Debits)
            $invoices = $customer->invoices()->select('date', 'invoice_no as reference', 'net_payable as debit', DB::raw('0 as credit'), DB::raw("'Invoice' as type"))->get();
            
            // Get Payments (Credits)
            $payments = $customer->payments()->select('date', DB::raw("CONCAT('Payment #', id) as reference"), DB::raw('0 as debit'), 'amount as credit', DB::raw("'Payment' as type"))->get();

            // Merge and Sort
            $ledger = $invoices->concat($payments)->sortBy('date');
        }

        return view('admin.reports.ledger', compact('customers', 'customer', 'ledger'));
    }
}
