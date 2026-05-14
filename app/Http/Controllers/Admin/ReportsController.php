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
        $reportYear = Carbon::parse($fromDate)->year;

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

        // CUSTOMER_ID MIGRATION FIX
        // SAFE CUSTOMER QUERY
        $invoices = Invoice::whereBetween('date', [$fromDate, $toDate])
            ->with(['customer' => function ($query) {
                $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name']));
            }])
            ->select('id', 'invoice_no', 'customer_id', 'net_payable', 'received_amount', 'due_amount', 'date')
            ->orderBy('date', 'desc');

        // REPORT PRINT FLOW
        $isPrint = $request->boolean('print');
        $invoices = $isPrint
            ? $invoices->get()
            : $invoices->paginate(20)->withQueryString();

        // PAYMENT FLOW IMPROVEMENT
        // REPORT TIMELINE
        $monthlySales = Invoice::whereYear('date', $reportYear)
            ->selectRaw('EXTRACT(MONTH FROM date) as month, COUNT(*) as total_invoices, COALESCE(SUM(net_payable), 0) as total_sales, COALESCE(SUM(received_amount), 0) as total_received, COALESCE(SUM(due_amount), 0) as total_due')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy(fn ($row) => (int) $row->month);

        return view('admin.reports.sales', compact('invoices', 'summary', 'fromDate', 'toDate', 'monthlySales', 'reportYear', 'isPrint'));
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
                DB::raw('COALESCE(SUM(invoice_items.quantity * invoice_items.unit_price), 0) as sales'),
                DB::raw('COALESCE(SUM(invoice_items.quantity * invoice_items.cost_price), 0) as cost'),
                DB::raw('COALESCE(SUM(invoice_items.quantity * (invoice_items.unit_price - invoice_items.cost_price)), 0) as profit')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // REPORT PRINT FLOW
        $isPrint = $request->boolean('print');

        return view('admin.reports.profit', compact('profitData', 'monthlyProfit', 'fromDate', 'toDate', 'isPrint'));
    }

    /**
     * Stock Valuation Report
     */
    public function stockReport(Request $request)
    {
        // QUERY OPTIMIZATION
        $productsQuery = Product::select('id', 'product_id', 'product_name', 'cost_price', 'selling_price', 'stock_quantity', 'category')
            ->orderBy('stock_quantity', 'asc');

        // REPORT PRINT FLOW
        $isPrint = $request->boolean('print');
        $products = $isPrint
            ? $productsQuery->get()
            : $productsQuery->paginate(25)->withQueryString();
        
        $totalValuation = round((float) Product::select(
            DB::raw('COALESCE(SUM(stock_quantity * cost_price), 0) as total')
        )->value('total'), 2);

        $lowStockCount = Product::where('stock_quantity', '<', 5)->count();

        return view('admin.reports.stock', compact('products', 'totalValuation', 'lowStockCount', 'isPrint'));
    }

    /**
     * Due Report
     */
    public function dueReport(Request $request)
    {
        // REPORT TIMELINE
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', Carbon::now()->toDateString());
        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // CUSTOMER_ID MIGRATION FIX
        // SAFE CUSTOMER QUERY
        $customers = Customer::select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name', 'mobile', 'previous_due']))
            ->withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->get()
            ->filter(function($customer) {
                return $customer->current_due > 0;
            })->sortByDesc('current_due');

        // FINANCIAL CALCULATION FIX
        $totalOutstanding = round((float) $customers->sum('current_due'), 2);

        // PAYMENT FLOW IMPROVEMENT
        // DUE HISTORY SYSTEM
        $collections = Payment::query()
            ->with([
                'customer' => function ($query) {
                    $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name']));
                },
                'invoice:id,invoice_no',
                'allocations.invoice:id,invoice_no',
                'user:id,name',
            ])
            ->whereBetween('date', [$fromDate, $toDate])
            ->latest('date')
            ->latest('id')
            ->get();

        $collectionSummary = [
            'count' => $collections->count(),
            'amount' => round((float) $collections->sum('amount'), 2),
        ];

        // REPORT PRINT FLOW
        $isPrint = $request->boolean('print');

        return view('admin.reports.due', compact('customers', 'totalOutstanding', 'collections', 'collectionSummary', 'fromDate', 'toDate', 'isPrint'));
    }

    /**
     * Customer Ledger Statement
     */
    public function customerLedger(Request $request)
    {
        // QUERY OPTIMIZATION
        // CUSTOMER LEDGER SYSTEM
        // CUSTOMER_ID MIGRATION FIX
        // SAFE CUSTOMER QUERY
        $customers = Customer::select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name', 'mobile']))
            ->orderBy(Customer::displayOrderColumn(), 'asc')
            ->get();
        $customerId = $request->input('customer_id');
        // REPORT TIMELINE
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        $ledger = collect();
        $customer = null;
        $openingBalance = 0;
        $closingBalance = 0;

        if ($customerId) {
            $customer = Customer::select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name', 'mobile', 'address', 'previous_due']))
                ->withSum('invoices', 'net_payable')
                ->withSum('payments', 'amount')
                ->findOrFail($customerId);
            
            $openingBalance = round((float) $customer->previous_due, 2);

            if ($fromDate) {
                // LEDGER IMPROVEMENT
                $salesBefore = round((float) $customer->invoices()
                    ->whereDate('date', '<', $fromDate)
                    ->sum('net_payable'), 2);
                $paymentsBefore = round((float) $customer->payments()
                    ->whereDate('date', '<', $fromDate)
                    ->sum('amount'), 2);
                $openingBalance = round($openingBalance + $salesBefore - $paymentsBefore, 2);
            }

            $runningBalance = $openingBalance;

            // CUSTOMER LEDGER SYSTEM
            $invoices = $customer->invoices()
                // LEDGER IMPROVEMENT
                ->select('id', 'invoice_no', 'net_payable', 'received_amount', 'due_amount', 'date', 'created_at')
                ->when($fromDate, fn ($query) => $query->whereDate('date', '>=', $fromDate))
                ->when($toDate, fn ($query) => $query->whereDate('date', '<=', $toDate))
                ->get()
                ->map(function ($invoice) {
                    return [
                        'sort_at' => Carbon::parse($invoice->date)->startOfDay()->addSeconds($invoice->id),
                        'invoice_no' => $invoice->invoice_no,
                        'invoice_date' => $invoice->date,
                        'sales_amount' => round((float) $invoice->net_payable, 2),
                        'paid_amount' => round((float) $invoice->received_amount, 2),
                        'due_amount' => round((float) $invoice->due_amount, 2),
                        'payment_collection' => 0,
                        'type' => 'Invoice',
                        'reference' => $invoice->invoice_no,
                    ];
                });
            
            $payments = $customer->payments()
                // PAYMENT FLOW IMPROVEMENT
                // LEDGER IMPROVEMENT
                // DUE COLLECTION IMPROVEMENT
                // DUE HISTORY SYSTEM
                ->with(['invoice:id,invoice_no', 'allocations.invoice:id,invoice_no'])
                ->select('id', 'invoice_id', 'amount', 'previous_due', 'remaining_due', 'payment_type', 'payment_method', 'date', 'note', 'created_at')
                ->when($fromDate, fn ($query) => $query->whereDate('date', '>=', $fromDate))
                ->when($toDate, fn ($query) => $query->whereDate('date', '<=', $toDate))
                ->get()
                ->map(function ($payment) {
                    $allocatedInvoices = $payment->allocations->pluck('invoice.invoice_no')->filter()->values();
                    $invoiceNo = optional($payment->invoice)->invoice_no
                        ?: ($allocatedInvoices->isNotEmpty() ? $allocatedInvoices->join(', ') : null);

                    return [
                        'sort_at' => Carbon::parse($payment->date)->endOfDay()->addSeconds($payment->id),
                        'invoice_no' => $invoiceNo,
                        'invoice_date' => $payment->date,
                        'sales_amount' => 0,
                        'paid_amount' => 0,
                        'due_amount' => 0,
                        'payment_collection' => round((float) $payment->amount, 2),
                        'type' => 'Due Collection',
                        'reference' => 'Payment #' . $payment->id . ' - ' . ucfirst($payment->payment_method) . ($payment->note ? ' - ' . $payment->note : ''),
                    ];
                });

            $ledger = $invoices
                ->concat($payments)
                ->sortBy('sort_at')
                ->values()
                ->map(function ($entry) use (&$runningBalance) {
                    $runningBalance = round($runningBalance + $entry['sales_amount'] - $entry['payment_collection'], 2);
                    $entry['balance'] = $runningBalance;

                    return (object) $entry;
                });

            $closingBalance = $runningBalance;
        }

        // REPORT PRINT FLOW
        $isPrint = $request->boolean('print');

        return view('admin.reports.ledger', compact('customers', 'customer', 'ledger', 'openingBalance', 'closingBalance', 'fromDate', 'toDate', 'isPrint'));
    }

    public function customerDueReceipt(Customer $customer)
    {
        // PAYMENT RECEIPT SYSTEM
        // CUSTOMER MODULE IMPROVEMENT
        // CUSTOMER LEDGER SYSTEM
        $customer->loadSum('invoices', 'net_payable')
            ->loadSum('payments', 'amount')
            ->load([
                'invoices' => function ($query) {
                    $query->select('id', 'customer_id', 'invoice_no', 'net_payable', 'received_amount', 'due_amount', 'date', 'created_at')
                        ->orderBy('date')
                        ->orderBy('created_at');
                },
                'payments' => function ($query) {
                    // DUE COLLECTION IMPROVEMENT
                    // DUE HISTORY SYSTEM
                    $query->with(['invoice:id,invoice_no', 'allocations.invoice:id,invoice_no'])
                        ->select('id', 'customer_id', 'invoice_id', 'amount', 'previous_due', 'remaining_due', 'payment_type', 'payment_method', 'date', 'note', 'created_at')
                        ->orderBy('date')
                        ->orderBy('created_at');
                },
            ]);

        return view('admin.reports.customer-due-receipt', compact('customer'));
    }
}
