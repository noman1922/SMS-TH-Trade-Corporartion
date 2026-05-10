<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Requests\Admin\CustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        // CUSTOMER_ID MIGRATION FIX
        // SAFE CUSTOMER QUERY
        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                // CUSTOMER MODULE IMPROVEMENT
                return $query->where(function ($query) use ($search) {
                    if (Customer::hasCustomerIdColumn()) {
                        $query->where('customer_id', 'like', "%{$search}%");
                    }

                    $query->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('hospital_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->withCount('invoices')
            ->withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->orderBy(Customer::displayOrderColumn(), 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // CUSTOMER MODULE IMPROVEMENT
        // CUSTOMER ID GENERATOR
        $nextCustomerId = Customer::generateNextCustomerId();

        return view('admin.customers.create', compact('nextCustomerId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                // CUSTOMER MODULE IMPROVEMENT
                // CUSTOMER ID GENERATOR
                $data = $request->validated();
                // CUSTOMER_ID MIGRATION FIX
                if (Customer::hasCustomerIdColumn()) {
                    $data['customer_id'] = Customer::generateNextCustomerId();
                } else {
                    unset($data['customer_id']);
                }

                Customer::create($data);
            });

            return redirect()->route('customers.index')
                ->with('success', 'Customer created successfully.');
        } catch (Exception $e) {
            Log::error('Customer creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error creating customer. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        $customer->loadCount('invoices')
            ->loadSum('invoices', 'net_payable')
            ->loadSum('payments', 'amount');

        // Calculate financial summaries (handled by model attributes)
        $invoices = $customer->invoices()
            ->with('user:id,name')
            ->select('id', 'invoice_no', 'customer_id', 'user_id', 'net_payable', 'received_amount', 'due_amount', 'date', 'created_at')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // CUSTOMER TIMELINE
        $invoiceTimeline = $customer->invoices()
            ->with(['user:id,name', 'items.product:id,product_id,product_name'])
            ->select('id', 'invoice_no', 'customer_id', 'user_id', 'net_payable', 'date', 'created_at')
            ->get()
            ->flatMap(function ($invoice) {
                $items = $invoice->items->pluck('product.product_name')->filter()->take(3)->implode(', ');

                return [
                    [
                        'datetime' => $invoice->created_at,
                        'date' => $invoice->date,
                        'type' => 'Invoice',
                        'title' => 'Invoice ' . $invoice->invoice_no,
                        'description' => 'Sales amount ৳' . number_format($invoice->net_payable, 2) . ($items ? ' for ' . $items : ''),
                        'user' => $invoice->user->name ?? 'System',
                    ],
                    [
                        'datetime' => $invoice->created_at,
                        'date' => $invoice->date,
                        'type' => 'Stock Sale',
                        'title' => 'Stock OUT via ' . $invoice->invoice_no,
                        'description' => $items ?: 'Invoice stock movement recorded',
                        'user' => $invoice->user->name ?? 'System',
                    ],
                ];
            });

        $paymentTimeline = $customer->payments()
            ->with('user:id,name')
            ->select('id', 'customer_id', 'invoice_id', 'amount', 'payment_type', 'payment_method', 'date', 'note', 'created_by', 'created_at')
            ->get()
            ->map(function ($payment) {
                return [
                    'datetime' => $payment->created_at,
                    'date' => $payment->date,
                    'type' => $payment->invoice_id ? 'Due Collection' : 'Payment',
                    'title' => ($payment->invoice_id ? 'Due Collection' : 'Payment') . ' #' . $payment->id,
                    'description' => 'Received ৳' . number_format($payment->amount, 2) . ' by ' . ucfirst($payment->payment_method) . ($payment->note ? ' - ' . $payment->note : ''),
                    'user' => $payment->user->name ?? 'System',
                ];
            });

        $timeline = collect([[
                'datetime' => $customer->created_at,
                'date' => $customer->created_at,
                'type' => 'Customer Created',
                'title' => 'Customer profile created',
                'description' => 'Opening balance ৳' . number_format($customer->previous_due, 2),
                'user' => 'System',
            ]])
            ->concat($invoiceTimeline)
            ->concat($paymentTimeline)
            ->sortByDesc('datetime')
            ->values();

        return view('admin.customers.show', compact('customer', 'invoices', 'timeline'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        try {
            DB::transaction(function () use ($request, $customer) {
                // CUSTOMER MODULE IMPROVEMENT
                $data = $request->validated();
                unset($data['customer_id']);

                $customer->update($data);
            });

            return redirect()->route('customers.index')
                ->with('success', 'Customer updated successfully.');
        } catch (Exception $e) {
            Log::error('Customer update failed', ['customer_id' => $customer->id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error updating customer. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Security: Only admin can delete
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized! Only Admins can delete customers.');
        }

        try {
            // Check if customer has invoices before deleting (optional but safer)
            if ($customer->invoices()->exists()) {
                return back()->with('error', 'Cannot delete customer with existing invoices.');
            }

            $customer->delete();
            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (Exception $e) {
            Log::error('Customer deletion failed', ['customer_id' => $customer->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting customer. Please try again.');
        }
    }
}
