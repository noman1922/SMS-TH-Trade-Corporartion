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

        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        $customers = Customer::query()
            ->select('id', 'customer_name', 'hospital_name', 'mobile', 'previous_due')
            ->when($search, function ($query, $search) {
                return $query->where('customer_name', 'like', "%{$search}%")
                             ->orWhere('mobile', 'like', "%{$search}%");
            })
            ->withCount('invoices')
            ->withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
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

        // QUERY OPTIMIZATION
        $customers = Customer::select('id', 'customer_name', 'previous_due')
            ->withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->orderBy('customer_name', 'asc')
            ->get();

        $selectedCustomer = $customerId
            ? Customer::select('id', 'customer_name', 'previous_due')
                ->withSum('invoices', 'net_payable')
                ->withSum('payments', 'amount')
                ->with(['invoices' => function ($query) {
                    $query->select('id', 'customer_id', 'invoice_no', 'due_amount')
                        ->where('due_amount', '>', 0)
                        ->orderBy('date', 'desc');
                }])
                ->find($customerId)
            : null;

        $selectedInvoice = $invoiceId ? Invoice::select('id', 'invoice_no', 'due_amount')->find($invoiceId) : null;

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
                // FINANCIAL CALCULATION FIX
                $amount = round((float) $request->amount, 2);

                if ($invoiceId) {
                    // Specific Invoice Payment
                    $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);
                    // FINANCIAL CALCULATION FIX
                    $remainingDue = round((float) $invoice->remaining_due, 2);
                    
                    if ($amount > $remainingDue) {
                        throw new Exception("Overpayment detected! Remaining due is only ৳" . number_format($remainingDue, 2));
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
                    // FINANCIAL CALCULATION FIX
                    // CRITICAL ACCOUNTING FIX
                    // DUE CALCULATION FIX
                    $newReceived = round((float) $invoice->received_amount + $amount, 2);
                    $newDue = round(max(0, (float) $invoice->due_amount - $amount), 2);
                    $invoice->update([
                        'received_amount' => number_format($newReceived, 2, '.', ''),
                        'due_amount' => number_format($newDue, 2, '.', ''),
                    ]);

                } else {
                    // Advance / Unallocated Payment
                    // QUERY OPTIMIZATION
                    $customer = Customer::query()
                        ->select('id', 'previous_due')
                        ->withSum('invoices', 'net_payable')
                        ->withSum('payments', 'amount')
                        ->lockForUpdate()
                        ->findOrFail($customerId);
                    // FINANCIAL CALCULATION FIX
                    $currentDue = round((float) $customer->current_due, 2);
                    
                    if ($amount > $currentDue) {
                         throw new Exception("Payment exceeds total customer due! Total due is ৳" . number_format($currentDue, 2));
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

                // PAYMENT RECEIPT SYSTEM
                return redirect()->route('payment.receipt', $payment)
                    ->with('success', 'Payment of ৳' . number_format($amount, 2) . ' recorded successfully. Print the receipt below.');
            });
        } catch (Exception $e) {
            Log::error('Payment creation failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Generate printable receipt.
     */
    public function receipt(Payment $payment)
    {
        // PAYMENT RECEIPT SYSTEM
        if (auth()->user()->role !== 'admin' && $payment->created_by !== auth()->id()) {
            abort(403);
        }

        // QUERY OPTIMIZATION
        $payment->load([
            'customer' => function ($query) {
                $query->select('id', 'customer_name', 'mobile', 'address', 'previous_due')
                    ->withSum('invoices', 'net_payable')
                    ->withSum('payments', 'amount');
            },
            'invoice.allocations',
            'user:id,name',
        ]);
        // FINANCIAL CALCULATION FIX
        $remainingDue = round((float) $payment->customer->current_due, 2);
        $previousDue = round($remainingDue + (float) $payment->amount, 2);
        $receiptNo = 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        return view('admin.payments.receipt', compact('payment', 'previousDue', 'remainingDue', 'receiptNo'));
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
                        // FINANCIAL CALCULATION FIX
                        // CRITICAL ACCOUNTING FIX
                        // DUE CALCULATION FIX
                        $newReceived = round(max(0, (float) $invoice->received_amount - (float) $payment->amount), 2);
                        $newDue = round((float) $invoice->due_amount + (float) $payment->amount, 2);
                        $invoice->update([
                            'received_amount' => number_format($newReceived, 2, '.', ''),
                            'due_amount' => number_format($newDue, 2, '.', ''),
                        ]);
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
