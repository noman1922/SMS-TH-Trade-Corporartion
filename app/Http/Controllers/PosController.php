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
use Carbon\Carbon;
use Exception;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('stock_quantity', '>', 0)->get();
        $customers = Customer::all();
        $invoice_no = 'INV-' . strtoupper(uniqid());
        
        return view('pos.index', compact('products', 'customers', 'invoice_no'));
    }

    public function getProductDetails($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_percent' => 'nullable|numeric|min:0|max:50',
            'vat_percent' => 'nullable|numeric|min:0',
            'ait_percent' => 'nullable|numeric|min:0',
            'extra_charge' => 'nullable|numeric|min:0',
            'received_amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $customer = \App\Models\Customer::findOrFail($request->customer_id);
                $sub_total = 0;
                $items_to_save = [];
                $stock_history_records = [];

                foreach ($request->items as $itemData) {
                    // 1. Lock and find product
                    $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                    
                    // 2. Strict stock check
                    if ($product->stock_quantity < $itemData['quantity']) {
                        throw new Exception("Insufficient stock for product: {$product->product_name} (Available: {$product->stock_quantity})");
                    }

                    $item_total = $product->selling_price * $itemData['quantity'];
                    $sub_total += $item_total;

                    $items_to_save[] = [
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'cost_price' => $product->cost_price,
                        'unit_price' => $product->selling_price,
                        'total_price' => $item_total,
                    ];

                    // 3. Prepare stock history data
                    $stock_history_records[] = [
                        'product' => $product,
                        'quantity' => $itemData['quantity']
                    ];
                }

                $discount = ($sub_total * ($request->discount_percent ?? 0)) / 100;
                $vat = ($sub_total * ($request->vat_percent ?? 0)) / 100;
                $ait = ($sub_total * ($request->ait_percent ?? 0)) / 100;
                $extra = $request->extra_charge ?? 0;

                $net_payable = ($sub_total - $discount) + $vat + $ait + $extra;
                $received = $request->received_amount;
                $due = max(0, $net_payable - $received);

                // 4. Create Invoice
                $invoice = Invoice::create([
                    'invoice_no' => 'INV-' . strtoupper(uniqid()),
                    'customer_id' => $request->customer_id,
                    'user_id' => Auth::id(),
                    'sub_total' => $sub_total,
                    'discount_percent' => $request->discount_percent ?? 0,
                    'vat_percent' => $request->vat_percent ?? 0,
                    'ait_percent' => $request->ait_percent ?? 0,
                    'extra_charge' => $extra,
                    'net_payable' => $net_payable,
                    'received_amount' => $received,
                    'due_amount' => $due,
                    'date' => $request->date,
                ]);

                // 4.1. Record Initial Payment
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

                // 5. Save Items, Reduce Stock, and Record History
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

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice created successfully',
                    'invoice_id' => $invoice->id
                ]);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function print($id)
    {
        $invoice = Invoice::with(['customer', 'items.product', 'user'])->findOrFail($id);
        return view('pos.print', compact('invoice'));
    }
}
