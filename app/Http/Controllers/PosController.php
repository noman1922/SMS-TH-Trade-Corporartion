<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('stock', '>', 0)->get();
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

        return DB::transaction(function () use ($request) {
            $sub_total = 0;
            $items_to_save = [];

            foreach ($request->items as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                
                if ($product->stock < $itemData['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for product: {$product->name}"
                    ], 422);
                }

                $item_total = $product->base_price * $itemData['quantity'];
                $sub_total += $item_total;

                $items_to_save[] = [
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $product->base_price,
                    'total_price' => $item_total,
                ];

                // Reduce stock
                $product->decrement('stock', $itemData['quantity']);
            }

            $discount = ($sub_total * ($request->discount_percent ?? 0)) / 100;
            $vat = ($sub_total * ($request->vat_percent ?? 0)) / 100;
            $ait = ($sub_total * ($request->ait_percent ?? 0)) / 100;
            $extra = $request->extra_charge ?? 0;

            $net_payable = ($sub_total - $discount) + $vat + $ait + $extra;
            $received = $request->received_amount;
            $due = max(0, $net_payable - $received);

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

            foreach ($items_to_save as $item) {
                $invoice->items()->create($item);
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice_id' => $invoice->id
            ]);
        });
    }

    public function print($id)
    {
        $invoice = Invoice::with(['customer', 'items.product', 'user'])->findOrFail($id);
        return view('pos.print', compact('invoice'));
    }
}
