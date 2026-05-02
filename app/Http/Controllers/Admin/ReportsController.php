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

        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        $summaryRow = Invoice::whereBetween('date', [$fromDate, $toDate])
            ->selectRaw(
                'COUNT(*) as total_invoices,
                 COALESCE(SUM(net_payable), 0) as total_amount,
                 COALESCE(SUM(received_amount), 0) as total_received,
                 COALESCE(SUM(due_amount), 0) as total_due'
            )
            ->first();

        $summary = [
            'total_invoices' => (int) $summaryRow->total_invoices,
            // FINANCIAL CALCULATION FIX
            'total_amount' => round((float) $summaryRow->total_amount, 2),
            'total_received' => round((float) $summaryRow->total_received, 2),
            'total_due' => round((float) $summaryRow->total_due, 2),
        ];

        $invoices = Invoice::whereBetween('date', [$fromDate, $toDate])
            ->with('customer:id,customer_name')
            ->select('id', 'invoice_no', 'customer_id', 'net_payable', 'received_amount', 'due_amount', 'date')
            ->orderBy('date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.sales', compact('invoices', 'summary', 'fromDate', 'toDate'));
    }

    /**
     * Profit Report
     */
    public function profitReport(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', Carbon::now()->toDateString());
        $reportYear = Carbon::parse($fromDate)->year;
        $startOfReportYear = Carbon::create($reportYear)->startOfYear()->toDateString();
        $endOfReportYear = Carbon::create($reportYear)->endOfYear()->toDateString();

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
            // QUERY OPTIMIZATION
            ->whereBetween('invoices.date', [$startOfReportYear, $endOfReportYear])
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
        // QUERY OPTIMIZATION
        $products = Product::select('id', 'product_id', 'product_name', 'cost_price', 'selling_price', 'stock_quantity', 'category')
            ->orderBy('stock_quantity', 'asc')
            ->paginate(25);
        
        $totalValuation = round((float) Product::select(
            DB::raw('COALESCE(SUM(stock_quantity * cost_price), 0) as total')
        )->value('total'), 2);

        $lowStockCount = Product::where('stock_quantity', '<', 5)->count();

        return view('admin.reports.stock', compact('products', 'totalValuation', 'lowStockCount'));
    }

    /**
     * Due Report
     */
    public function dueReport()
    {
        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        $customers = Customer::select('id', 'customer_name', 'hospital_name', 'mobile', 'previous_due')
            ->withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->get()
            ->filter(function($customer) {
                return $customer->current_due > 0;
            })->sortByDesc('current_due');

        // FINANCIAL CALCULATION FIX
        $totalOutstanding = round((float) $customers->sum('current_due'), 2);

        return view('admin.reports.due', compact('customers', 'totalOutstanding'));
    }

    /**
     * Customer Ledger Statement
     */
    public function customerLedger(Request $request)
    {
        // QUERY OPTIMIZATION
        $customers = Customer::select('id', 'customer_name')->orderBy('customer_name', 'asc')->get();
        $customerId = $request->input('customer_id');
        
        $ledger = collect();
        $customer = null;

        if ($customerId) {
            $customer = Customer::select('id', 'customer_name', 'mobile', 'address', 'previous_due')->findOrFail($customerId);
            
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

    public function customerDueReceipt(Customer $customer)
    {
        // PAYMENT RECEIPT SYSTEM
        $customer->loadSum('invoices', 'net_payable')
            ->loadSum('payments', 'amount');

        return view('admin.reports.customer-due-receipt', compact('customer'));
    }
}
