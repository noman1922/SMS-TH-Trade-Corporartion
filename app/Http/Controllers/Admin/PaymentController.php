<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Http\Requests\Admin\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                return $query->where('customer_name', 'like', "%{$search}%")
                             ->orWhere('mobile', 'like', "%{$search}%");
            })
            ->withCount('invoices')
            ->get()
            ->filter(function ($customer) {
                return $customer->current_due > 0;
            });

        return view('admin.payments.index', compact('customers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customerId = $request->input('customer_id');
        $invoiceId = $request->input('invoice_id');

        $customers = Customer::orderBy('customer_name', 'asc')->get();
        $selectedCustomer = $customerId ? Customer::find($customerId) : null;
        $selectedInvoice = $invoiceId ? Invoice::find($invoiceId) : null;

        return view('admin.payments.create', compact('customers', 'selectedCustomer', 'selectedInvoice'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $customerId = $request->customer_id;
                $invoiceId = $request->invoice_id;
                $amount = $request->amount;

                if ($invoiceId) {
                    // Specific Invoice Payment
                    $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);
                    
                    if ($amount > $invoice->remaining_due) {
                        throw new Exception("Overpayment detected! Remaining due is only ৳" . number_format($invoice->remaining_due, 2));
                    }

                    $payment = Payment::create([
                        'customer_id' => $customerId,
                        'invoice_id' => $invoiceId,
                        'amount' => $amount,
                        'payment_type' => 'invoice',
                        'payment_method' => $request->payment_method,
                        'date' => $request->date,
                        'note' => $request->note,
                        'created_by' => Auth::id(),
                    ]);

                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'invoice_id' => $invoiceId,
                        'amount' => $amount,
                    ]);

                    // Sync legacy fields
                    $invoice->increment('received_amount', $amount);
                    $invoice->decrement('due_amount', $amount);

                } else {
                    // Advance / Unallocated Payment
                    $customer = Customer::lockForUpdate()->findOrFail($customerId);
                    
                    if ($amount > $customer->current_due) {
                         throw new Exception("Payment exceeds total customer due! Total due is ৳" . number_format($customer->current_due, 2));
                    }

                    $payment = Payment::create([
                        'customer_id' => $customerId,
                        'invoice_id' => null,
                        'amount' => $amount,
                        'payment_type' => 'advance',
                        'payment_method' => $request->payment_method,
                        'date' => $request->date,
                        'note' => $request->note,
                        'created_by' => Auth::id(),
                    ]);
                }

                Log::info('Payment recorded', ['payment_id' => $payment->id, 'amount' => $amount, 'user_id' => Auth::id()]);

                return redirect()->route('payments.index')
                    ->with('success', 'Payment of ৳' . number_format($amount, 2) . ' recorded successfully.');
            });
        } catch (Exception $e) {
            Log::error('Payment creation failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Generate printable receipt.
     */
    public function receipt($id)
    {
        $payment = Payment::with(['customer', 'invoice', 'user'])->findOrFail($id);
        return view('admin.payments.receipt', compact('payment'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized! Only admins can delete payments.');
        }

        try {
            DB::transaction(function () use ($payment) {
                // 1. Delete allocations first (fix orphan records)
                $payment->allocations()->delete();

                // 2. Reverse invoice amounts if linked
                if ($payment->invoice_id) {
                    $invoice = Invoice::lockForUpdate()->find($payment->invoice_id);
                    if ($invoice) {
                        $invoice->decrement('received_amount', $payment->amount);
                        $invoice->increment('due_amount', $payment->amount);
                    }
                }

                Log::info('Payment deleted', ['payment_id' => $payment->id, 'amount' => $payment->amount, 'user_id' => Auth::id()]);

                // 3. Delete the payment
                $payment->delete();
            });

            return back()->with('success', 'Payment record deleted.');
        } catch (Exception $e) {
            Log::error('Payment deletion failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting payment. Please try again.');
        }
    }
}
