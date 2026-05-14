<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocalCacheController extends Controller
{
    public function bootstrap(Request $request)
    {
        // LOCAL CACHE SYSTEM
        // INDEXEDDB POS CACHE
        $products = Product::where('stock_quantity', '>', 0)
            ->select('id', 'product_id', 'product_name', 'model_no', 'selling_price', 'stock_quantity', 'updated_at')
            ->orderBy('product_name')
            ->get()
            ->map(fn ($product) => [
                'id' => (int) $product->id,
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'model_no' => $product->model_no,
                'selling_price' => round((float) $product->selling_price, 2),
                'stock_quantity' => (int) $product->stock_quantity,
                'updated_at' => optional($product->updated_at)->toISOString(),
            ]);

        $customers = Customer::withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name', 'mobile', 'address', 'previous_due', 'updated_at']))
            ->orderBy('customer_name', 'asc')
            ->get()
            ->map(fn ($customer) => [
                'id' => (int) $customer->id,
                'customer_id' => $customer->customer_id ?? ('#' . $customer->id),
                'customer_name' => $customer->customer_name,
                'hospital_name' => $customer->hospital_name,
                'mobile' => $customer->mobile,
                'address' => $customer->address,
                'current_due' => round((float) $customer->current_due, 2),
                'updated_at' => optional($customer->updated_at)->toISOString(),
            ]);

        $recentInvoices = Invoice::with(['customer' => function ($query) {
                $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name']));
            }])
            ->select('id', 'invoice_no', 'customer_id', 'user_id', 'net_payable', 'received_amount', 'due_amount', 'date', 'created_at')
            ->when(auth()->user()->role !== 'admin', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest('date')
            ->latest('created_at')
            ->limit(25)
            ->get()
            ->map(fn ($invoice) => [
                'id' => (int) $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'customer_id' => (int) $invoice->customer_id,
                'customer_label' => $invoice->customer
                    ? trim(($invoice->customer->customer_id ?? '') . ' ' . ($invoice->customer->hospital_name ?? $invoice->customer->customer_name ?? ''))
                    : 'Customer',
                'net_payable' => round((float) $invoice->net_payable, 2),
                'received_amount' => round((float) $invoice->received_amount, 2),
                'due_amount' => round((float) $invoice->due_amount, 2),
                'date' => optional($invoice->date)->toDateString(),
                'created_at' => optional($invoice->created_at)->toISOString(),
            ]);

        return response()->json([
            'synced_at' => now()->toISOString(),
            'products' => $products,
            'customers' => $customers,
            'pricing' => $this->pricingHistory(),
            'recent_invoices' => $recentInvoices,
            'dashboard_summary' => $this->dashboardSummary(),
        ]);
    }

    private function pricingHistory()
    {
        // LOCAL CACHE SYSTEM
        // Fast display cache only. Backend pricing remains authoritative on invoice save.
        $historyRows = $this->latestCustomerProductPrices();

        $approvedRows = Schema::hasTable('price_approval_requests')
            ? DB::table('price_approval_requests')
                ->where('status', 'approved')
                ->whereNotNull('reviewed_at')
                ->orderByDesc('reviewed_at')
                ->orderByDesc('id')
                ->limit(1000)
                ->get(['customer_id', 'product_id', 'requested_price', 'reviewed_at'])
            : collect();

        return $historyRows->map(fn ($row) => [
                'key' => ((int) $row->customer_id) . ':' . ((int) $row->product_id),
                'customer_id' => (int) $row->customer_id,
                'product_id' => (int) $row->product_id,
                'price' => round((float) $row->unit_price, 2),
                'source' => 'customer_history',
                'date' => $row->date,
            ])
            ->merge($approvedRows->map(fn ($row) => [
                'key' => (($row->customer_id === null) ? 'global' : (int) $row->customer_id) . ':' . ((int) $row->product_id),
                'customer_id' => $row->customer_id === null ? null : (int) $row->customer_id,
                'product_id' => (int) $row->product_id,
                'price' => round((float) $row->requested_price, 2),
                'source' => 'approved_special_price',
                'date' => $row->reviewed_at ? Carbon::parse($row->reviewed_at)->toISOString() : null,
            ]))
            ->values();
    }

    private function latestCustomerProductPrices()
    {
        if (DB::getDriverName() === 'pgsql') {
            return DB::table('invoice_items as ii')
                ->join('invoices as i', 'ii.invoice_id', '=', 'i.id')
                ->selectRaw('DISTINCT ON (i.customer_id, ii.product_id) i.customer_id, ii.product_id, ii.unit_price, i.date')
                ->orderBy('i.customer_id')
                ->orderBy('ii.product_id')
                ->orderByDesc('i.date')
                ->orderByDesc('i.id')
                ->orderByDesc('ii.id')
                ->limit(1500)
                ->get();
        }

        return DB::table('invoice_items as ii')
            ->join('invoices as i', 'ii.invoice_id', '=', 'i.id')
            ->select('i.customer_id', 'ii.product_id', 'ii.unit_price', 'i.date')
            ->orderByDesc('i.date')
            ->orderByDesc('i.id')
            ->orderByDesc('ii.id')
            ->limit(1500)
            ->get()
            ->unique(fn ($row) => $row->customer_id . ':' . $row->product_id)
            ->values();
    }

    private function dashboardSummary(): array
    {
        // LOCAL CACHE SYSTEM
        // Short-lived summary cache; financial screens still query backend live.
        $today = Carbon::today()->toDateString();

        return [
            'user_id' => auth()->id(),
            'role' => auth()->user()->role,
            'total_products' => Product::count(),
            'low_stock_count' => Product::where('stock_quantity', '<', 5)->count(),
            'recent_invoice_count' => Invoice::when(auth()->user()->role !== 'admin', fn ($query) => $query->where('user_id', auth()->id()))->count(),
            'today_invoice_count' => Invoice::whereDate('date', $today)
                ->when(auth()->user()->role !== 'admin', fn ($query) => $query->where('user_id', auth()->id()))
                ->count(),
            'synced_at' => now()->toISOString(),
        ];
    }
}
