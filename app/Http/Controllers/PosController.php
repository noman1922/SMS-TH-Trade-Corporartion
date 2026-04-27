<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class PosController extends Controller
{
    public function index()
    {
        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // SUPABASE SPEED FIX
        $products = Product::where('stock_quantity', '>', 0)
            ->select('id', 'product_id', 'product_name', 'stock_quantity')
            ->orderBy('product_name')
            ->get();

        $customers = Customer::withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->select('id', 'customer_name', 'mobile', 'address', 'previous_due')
            ->orderBy('customer_name', 'asc')
            ->get();
        $invoice_no = 'INV-' . strtoupper(uniqid());
        
        return view('pos.index', compact('products', 'customers', 'invoice_no'));
    }

    /**
     * Return only safe fields — cost_price is never exposed to staff.
     */
    public function getProductDetails($id)
    {
        // QUERY OPTIMIZATION
        $product = Product::select('id', 'product_name', 'product_id', 'selling_price', 'stock_quantity')->findOrFail($id);
        return response()->json([
            'id' => $product->id,
            'product_name' => $product->product_name,
            'product_id' => $product->product_id,
            'selling_price' => $product->selling_price,
            'stock_quantity' => $product->stock_quantity,
        ]);
    }

    public function store(Request $request)
    {
        // POS INPUT UX FIX
        foreach (['discount_percent', 'vat_percent', 'ait_percent', 'extra_charge'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_percent' => 'nullable|numeric|min:0|max:50',
            'vat_percent' => 'nullable|numeric|min:0|max:100',
            'ait_percent' => 'nullable|numeric|min:0|max:100',
            'extra_charge' => 'nullable|numeric|min:0',
            'received_amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // QUERY OPTIMIZATION
                $customer = Customer::query()
                    ->select('id', 'customer_name', 'previous_due')
                    ->withSum('invoices', 'net_payable')
                    ->withSum('payments', 'amount')
                    ->lockForUpdate()
                    ->findOrFail($request->customer_id);
                $sub_total = 0;
                $items_to_save = [];
                $stock_history_records = [];
                $requestedProductIds = collect($request->items)->pluck('product_id')->unique()->values();

                // QUERY OPTIMIZATION
                // Lock all requested product rows in one Supabase round trip.
                $products = Product::whereIn('id', $requestedProductIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($request->items as $itemData) {
                    // 1. Use the already locked product row
                    $product = $products->get((int) $itemData['product_id']);

                    if (! $product) {
                        throw new Exception('Selected product was not found.');
                    }
                    
                    // 2. Strict stock check
                    if ($product->stock_quantity < $itemData['quantity']) {
                        throw new Exception("Insufficient stock for product: {$product->product_name} (Available: {$product->stock_quantity})");
                    }

                    // 3. Use server-side price (never trust frontend price)
                    // FINANCIAL CALCULATION FIX
                    $unitPrice = round((float) $product->selling_price, 2);
                    $costPrice = round((float) $product->cost_price, 2);
                    $item_total = round($unitPrice * (int) $itemData['quantity'], 2);
                    $sub_total = round($sub_total + $item_total, 2);

                    $items_to_save[] = [
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'cost_price' => $costPrice,
                        'unit_price' => $unitPrice,
                        'total_price' => $item_total,
                    ];

                    // 4. Prepare stock history data
                    $stock_history_records[] = [
                        'product' => $product,
                        'quantity' => $itemData['quantity']
                    ];
                }

                // 5. Recalculate all totals server-side (never trust frontend)
                // POS INPUT UX FIX
                // FINANCIAL CALCULATION FIX
                $discountPercent = round((float) ($request->input('discount_percent') ?? 0), 2);
                $vatPercent = round((float) ($request->input('vat_percent') ?? 0), 2);
                $aitPercent = round((float) ($request->input('ait_percent') ?? 0), 2);
                $extra = round((float) ($request->input('extra_charge') ?? 0), 2);

                $discount = round(($sub_total * $discountPercent) / 100, 2);
                $vat = round(($sub_total * $vatPercent) / 100, 2);
                $ait = round(($sub_total * $aitPercent) / 100, 2);

                // CRITICAL ACCOUNTING FIX
                // DUE CALCULATION FIX
                $net_payable = round(($sub_total - $discount) + $vat + $ait + $extra, 2);
                $previousDue = round((float) $customer->current_due, 2);
                $totalPayable = round($previousDue + $net_payable, 2);
                $receivedAmount = round((float) $request->received_amount, 2);
                $received = round(min($receivedAmount, $totalPayable), 2); // Prevent unintended advance/overpayment
                $due = round(max(0, $totalPayable - $received), 2);

                // 6. Create Invoice
                $invoice = Invoice::create([
                    'invoice_no' => 'INV-' . strtoupper(uniqid()),
                    'customer_id' => $request->customer_id,
                    'user_id' => Auth::id(),
                    'sub_total' => $sub_total,
                    'discount_percent' => $discountPercent,
                    'vat_percent' => $vatPercent,
                    'ait_percent' => $aitPercent,
                    'extra_charge' => $extra,
                    'net_payable' => $net_payable,
                    'received_amount' => $received,
                    'due_amount' => $due,
                    'date' => $request->date,
                ]);

                // 6.1. Record Initial Payment
                if ($received > 0) {
                    $payment = Payment::create([
                        'customer_id' => $request->customer_id,
                        'invoice_id' => $invoice->id,
                        'amount' => $received,
                        'payment_type' => 'invoice',
                        'payment_method' => 'cash',
                        'date' => $request->date,
                        'note' => 'Down payment for POS Sale',
                        'created_by' => Auth::id(),
                    ]);

                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $received,
                    ]);
                }

                // 7. Save Items, Reduce Stock, and Record History
                foreach ($items_to_save as $index => $item) {
                    $invoice->items()->create($item);
                    
                    $product = $stock_history_records[$index]['product'];
                    $qty = $stock_history_records[$index]['quantity'];

                    // Reduce Stock
                    $product->decrement('stock_quantity', $qty);

                    // Record History
                    \App\Models\StockHistory::create([
                        'product_id' => $product->id,
                        'type' => 'OUT',
                        'quantity' => $qty,
                        'reference_type' => 'invoice',
                        'reference_id' => $invoice->id,
                        'note' => "Sold to: " . ($customer->customer_name ?? 'Walk-in Customer'),
                        'date' => $request->date,
                        'created_by' => Auth::id(),
                    ]);
                }

                Log::info('Invoice created', ['invoice_id' => $invoice->id, 'user_id' => Auth::id(), 'total' => $net_payable]);

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice created successfully',
                    'invoice_id' => $invoice->id
                ]);
            });
        } catch (Exception $e) {
            Log::error('POS Invoice creation failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function print(Invoice $invoice)
    {
        // ROW PRINT FIX
        if (auth()->user()->role !== 'admin' && $invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $invoice->load(['customer', 'items.product', 'user']);
        return view('pos.print', compact('invoice'));
    }
}
