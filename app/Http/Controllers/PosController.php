<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
// CUSTOMER PRICE MEMORY
use App\Services\CustomerPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class PosController extends Controller
{
    // CUSTOMER PRICE MEMORY
    protected CustomerPricingService $pricingService;

    public function __construct(CustomerPricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    public function index()
    {
        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // SUPABASE SPEED FIX
        $products = Product::where('stock_quantity', '>', 0)
            ->select('id', 'product_id', 'product_name', 'selling_price', 'stock_quantity')
            ->orderBy('product_name')
            ->get();

        // CUSTOMER_ID MIGRATION FIX
        // SAFE CUSTOMER QUERY
        $customers = Customer::withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name', 'mobile', 'address', 'previous_due']))
            ->orderBy('customer_name', 'asc')
            ->get();
        $invoice_no = 'INV-' . strtoupper(uniqid());

        // DYNAMIC CUSTOMER PRICING
        $userRole = auth()->user()->role;
        
        return view('pos.index', compact('products', 'customers', 'invoice_no', 'userRole'));
    }

    /**
     * Return only safe fields — cost_price is never exposed to staff.
     * DYNAMIC CUSTOMER PRICING — accepts optional customer_id to resolve customer-specific price.
     */
    public function getProductDetails(Request $request, $id)
    {
        // QUERY OPTIMIZATION
        $product = Product::select('id', 'product_name', 'product_id', 'selling_price', 'stock_quantity')->findOrFail($id);

        $response = [
            'id' => $product->id,
            'product_name' => $product->product_name,
            'product_id' => $product->product_id,
            'selling_price' => $product->selling_price,
            'stock_quantity' => $product->stock_quantity,
        ];

        // CUSTOMER PRICE MEMORY
        // DYNAMIC CUSTOMER PRICING
        // If customer_id is provided, also return the customer-specific last price.
        $customerId = $request->query('customer_id');
        if ($customerId) {
            $resolved = $this->pricingService->resolvePrice((int) $customerId, $product->id);
            $response['customer_price'] = $resolved['price'];
            $response['price_source'] = $resolved['source'];
        }

        return response()->json($response);
    }

    // CUSTOMER PRICE MEMORY
    // API endpoint: get last price for a customer+product pair.
    public function getCustomerProductPrice($customerId, $productId)
    {
        $resolved = $this->pricingService->resolvePrice((int) $customerId, (int) $productId);

        return response()->json([
            'price' => $resolved['price'],
            'source' => $resolved['source'],
        ]);
    }

    // CUSTOMER PRICE MEMORY
    // Batch endpoint: refresh selected POS item prices when the customer changes.
    public function getCustomerProductPrices(Request $request, $customerId)
    {
        $data = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        return response()->json([
            'prices' => $this->pricingService->resolvePricesForProducts((int) $customerId, $data['product_ids']),
        ]);
    }

    public function store(Request $request)
    {
        // POS INPUT UX FIX
        foreach (['discount_percent', 'discount_value', 'vat_percent', 'ait_percent', 'extra_charge'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            // DYNAMIC CUSTOMER PRICING
            'items.*.price' => 'nullable|numeric|min:0',
            // DISCOUNT TYPE SYSTEM
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_value' => 'nullable|numeric|min:0',
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

                // DYNAMIC CUSTOMER PRICING
                // Determine user role for price validation
                $isAdmin = auth()->user()->role === 'admin';

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

                    // 3. DYNAMIC CUSTOMER PRICING — Determine unit price
                    // FINANCIAL CALCULATION FIX
                    if ($isAdmin && array_key_exists('price', $itemData) && $itemData['price'] !== null && $itemData['price'] !== '') {
                        // Admin can set any price
                        $unitPrice = round((float) $itemData['price'], 2);
                    } else {
                        // Staff: use customer-specific last price or product default
                        // CUSTOMER PRICE MEMORY
                        $resolved = $this->pricingService->resolvePrice(
                            (int) $request->customer_id,
                            $product->id
                        );
                        $unitPrice = round((float) $resolved['price'], 2);
                    }

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
                // DISCOUNT TYPE SYSTEM
                $discountType = $request->input('discount_type', 'percentage');
                $discountPercent = round((float) ($request->input('discount_percent') ?? 0), 2);
                $vatPercent = round((float) ($request->input('vat_percent') ?? 0), 2);
                $aitPercent = round((float) ($request->input('ait_percent') ?? 0), 2);
                $extra = round((float) ($request->input('extra_charge') ?? 0), 2);

                // DISCOUNT TYPE SYSTEM — Calculate discount based on type
                if ($discountType === 'fixed') {
                    $discountValue = round((float) ($request->input('discount_value') ?? 0), 2);
                    // Cap fixed discount at subtotal to prevent negative bill
                    $discount = round(min($discountValue, $sub_total), 2);
                    // Store percent as 0 for fixed type, actual amount is in discount_amount
                    $discountPercent = 0;
                } else {
                    // Percentage discount (existing behavior)
                    $discount = round(($sub_total * $discountPercent) / 100, 2);
                }

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
                    // DISCOUNT TYPE SYSTEM
                    'discount_type' => $discountType,
                    'discount_amount' => $discount,
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
