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
        // PAYMENT FLOW IMPROVEMENT
        // REPORT TIMELINE
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // CUSTOMER_ID MIGRATION FIX
        // SAFE CUSTOMER QUERY
        $customers = Customer::query()
            ->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name', 'mobile', 'previous_due']))
            ->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    if (Customer::hasCustomerIdColumn()) {
                        $query->where('customer_id', 'like', "%{$search}%");
                    }

                    $query->orWhere('hospital_name', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->withCount('invoices')
            ->withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->get()
            ->filter(function ($customer) {
                return $customer->current_due > 0;
            });

        // DUE COLLECTION IMPROVEMENT
        // DUE HISTORY SYSTEM
        $collections = Payment::query()
            ->with([
                'customer' => function ($query) {
                    $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name']));
                },
                'invoice:id,invoice_no',
                'user:id,name',
                'allocations.invoice:id,invoice_no',
            ])
            ->when($fromDate, fn ($query) => $query->whereDate('date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('date', '<=', $toDate))
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('customer', function ($query) use ($search) {
                        if (Customer::hasCustomerIdColumn()) {
                            $query->where('customer_id', 'like', "%{$search}%");
                        }

                        $query->orWhere('hospital_name', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%");
                    })->orWhereHas('invoice', function ($query) use ($search) {
                        $query->where('invoice_no', 'like', "%{$search}%");
                    });
                });
            })
            ->latest('date')
            ->latest('id')
            ->limit(30)
            ->get();

        return view('admin.payments.index', compact('customers', 'collections', 'search', 'fromDate', 'toDate'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customerId = $request->input('customer_id');
        $invoiceId = $request->input('invoice_id');
        $invoiceNo = $request->input('invoice_no');

        // INVOICE PAYMENT SYSTEM
        if (! $invoiceId && $invoiceNo) {
            $invoice = Invoice::select('id', 'customer_id')
                ->where('invoice_no', $invoiceNo)
                ->where('due_amount', '>', 0)
                ->first();

            if ($invoice) {
                $invoiceId = $invoice->id;
                $customerId = $invoice->customer_id;
            }
        }

        // QUERY OPTIMIZATION
        // CUSTOMER_ID MIGRATION FIX
        // SAFE CUSTOMER QUERY
        $customers = Customer::select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name', 'previous_due']))
            ->withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->orderBy(Customer::displayOrderColumn(), 'asc')
            ->get();

        $selectedCustomer = $customerId
            ? Customer::select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name', 'previous_due']))
                ->withSum('invoices', 'net_payable')
                ->withSum('payments', 'amount')
                ->with([
                    'invoices' => function ($query) {
                    $query->select('id', 'customer_id', 'invoice_no', 'net_payable', 'received_amount', 'due_amount', 'date', 'created_at')
                        ->orderBy('date')
                        ->orderBy('id');
                    },
                    'payments' => function ($query) {
                        // DUE HISTORY SYSTEM
                        $query->with(['invoice:id,invoice_no', 'allocations.invoice:id,invoice_no', 'user:id,name'])
                            ->select('id', 'customer_id', 'invoice_id', 'amount', 'previous_due', 'remaining_due', 'payment_type', 'payment_method', 'date', 'note', 'created_by', 'created_at')
                            ->latest('date')
                            ->latest('id')
                            ->limit(10);
                    },
                ])
                ->find($customerId)
            : null;

        $selectedInvoice = $invoiceId ? Invoice::select('id', 'invoice_no', 'due_amount')->find($invoiceId) : null;

        return view('admin.payments.create', compact('customers', 'selectedCustomer', 'selectedInvoice'));
    }

    // PAYMENT FLOW IMPROVEMENT
    // INVOICE PAYMENT SYSTEM
    public function searchInvoices(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        if (strlen($search) < 2) {
            return response()->json(['invoices' => []]);
        }

        $invoices = Invoice::query()
            ->with(['customer' => function ($query) {
                $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name']));
            }])
            ->select('id', 'customer_id', 'invoice_no', 'net_payable', 'received_amount', 'due_amount', 'date')
            ->where('due_amount', '>', 0)
            ->where('invoice_no', 'like', "%{$search}%")
            ->orderBy('invoice_no')
            ->limit(10)
            ->get();

        return response()->json([
            'invoices' => $invoices->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'customer_id' => $invoice->customer_id,
                'customer_label' => trim(($invoice->customer->customer_id ?? '') . ' - ' . ($invoice->customer->hospital_name ?? '') . (($invoice->customer->customer_name ?? null) ? ' (' . $invoice->customer->customer_name . ')' : '')),
                'date' => optional($invoice->date)->format('Y-m-d'),
                'net_payable' => round((float) $invoice->net_payable, 2),
                'received_amount' => round((float) $invoice->received_amount, 2),
                'due_amount' => round((float) $invoice->remaining_due, 2),
            ])->values(),
        ]);
    }

    // DUE COLLECTION IMPROVEMENT
    // DUE HISTORY SYSTEM
    public function customerDueData(Customer $customer)
    {
        $customer->loadSum('invoices', 'net_payable')
            ->loadSum('payments', 'amount')
            ->load([
                'invoices' => function ($query) {
                    $query->select('id', 'customer_id', 'invoice_no', 'net_payable', 'received_amount', 'due_amount', 'date')
                        ->orderBy('date')
                        ->orderBy('id');
                },
                'payments' => function ($query) {
                    $query->with(['invoice:id,invoice_no', 'allocations.invoice:id,invoice_no', 'user:id,name'])
                        ->select('id', 'customer_id', 'invoice_id', 'amount', 'previous_due', 'remaining_due', 'payment_method', 'date', 'created_by')
                        ->latest('date')
                        ->latest('id')
                        ->limit(10);
                },
            ]);

        return response()->json([
            'current_due' => $customer->current_due,
            'invoices' => $customer->invoices->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'date' => optional($invoice->date)->format('Y-m-d'),
                'net_payable' => round((float) $invoice->net_payable, 2),
                'received_amount' => round((float) $invoice->received_amount, 2),
                'due_amount' => round((float) $invoice->remaining_due, 2),
            ])->values(),
            'due_invoices' => $customer->invoices
                ->filter(fn ($invoice) => round((float) $invoice->remaining_due, 2) > 0)
                ->map(fn ($invoice) => [
                    'id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'due_amount' => round((float) $invoice->remaining_due, 2),
                ])
                ->values(),
            'payments' => $customer->payments->map(function ($payment) {
                $allocatedInvoices = $payment->allocations->pluck('invoice.invoice_no')->filter()->values();

                return [
                    'id' => $payment->id,
                    'receipt_no' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'invoice_no' => optional($payment->invoice)->invoice_no
                        ?: ($allocatedInvoices->isNotEmpty() ? $allocatedInvoices->join(', ') : null),
                    'amount' => round((float) $payment->amount, 2),
                    'previous_due' => $payment->previous_due !== null ? round((float) $payment->previous_due, 2) : null,
                    'remaining_due' => $payment->remaining_due !== null ? round((float) $payment->remaining_due, 2) : null,
                    'payment_method' => ucfirst($payment->payment_method),
                    'date' => optional($payment->date)->format('Y-m-d'),
                    'collected_by' => optional($payment->user)->name,
                ];
            })->values(),
        ]);
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
                // INVOICE PAYMENT SYSTEM
                if (! $invoiceId && $request->filled('invoice_no')) {
                    $invoiceId = Invoice::where('invoice_no', $request->invoice_no)
                        ->where('customer_id', $customerId)
                        ->where('due_amount', '>', 0)
                        ->value('id');
                }
                // FINANCIAL CALCULATION FIX
                $amount = round((float) $request->amount, 2);

                if ($invoiceId) {
                    // DUE COLLECTION IMPROVEMENT
                    // INVOICE PAYMENT FLOW
                    // Specific Invoice Payment
                    $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);
                    if ((int) $invoice->customer_id !== (int) $customerId) {
                        throw new Exception('Selected invoice does not belong to this customer.');
                    }

                    // FINANCIAL CALCULATION FIX
                    $remainingDue = round((float) $invoice->remaining_due, 2);
                    
                    if ($amount > $remainingDue) {
                        throw new Exception("Overpayment detected! Remaining due is only ৳" . number_format($remainingDue, 2));
                    }

                    $payment = Payment::create([
                        'customer_id' => $customerId,
                        'invoice_id' => $invoiceId,
                        'amount' => $amount,
                        // DUE HISTORY SYSTEM
                        'previous_due' => $remainingDue,
                        'remaining_due' => round(max(0, $remainingDue - $amount), 2),
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
                    // DUE COLLECTION IMPROVEMENT
                    // INVOICE PAYMENT FLOW
                    // Full customer due payment: allocate across due invoices oldest-first.
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
                        // DUE HISTORY SYSTEM
                        'previous_due' => $currentDue,
                        'remaining_due' => round(max(0, $currentDue - $amount), 2),
                        'payment_type' => 'invoice',
                        'payment_method' => $request->payment_method,
                        'date' => $request->date,
                        'note' => $request->note,
                        'created_by' => Auth::id(),
                    ]);

                    $remainingPayment = $amount;
                    $dueInvoices = Invoice::where('customer_id', $customerId)
                        ->where('due_amount', '>', 0)
                        ->lockForUpdate()
                        ->orderBy('date')
                        ->orderBy('id')
                        ->get();

                    foreach ($dueInvoices as $dueInvoice) {
                        if ($remainingPayment <= 0) {
                            break;
                        }

                        $invoiceDue = round((float) $dueInvoice->remaining_due, 2);
                        $allocated = round(min($remainingPayment, $invoiceDue), 2);

                        if ($allocated <= 0) {
                            continue;
                        }

                        PaymentAllocation::create([
                            'payment_id' => $payment->id,
                            'invoice_id' => $dueInvoice->id,
                            'amount' => $allocated,
                        ]);

                        $dueInvoice->update([
                            'received_amount' => number_format(round((float) $dueInvoice->received_amount + $allocated, 2), 2, '.', ''),
                            'due_amount' => number_format(round(max(0, (float) $dueInvoice->due_amount - $allocated), 2), 2, '.', ''),
                        ]);

                        $remainingPayment = round($remainingPayment - $allocated, 2);
                    }
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
                // CUSTOMER_ID MIGRATION FIX
                // SAFE CUSTOMER QUERY
                $query->select(Customer::safeSelectColumns(['id', 'customer_id', 'customer_name', 'hospital_name', 'mobile', 'address', 'previous_due']))
                    ->withSum('invoices', 'net_payable')
                    ->withSum('payments', 'amount');
            },
            'invoice.allocations',
            'allocations.invoice:id,invoice_no,net_payable,due_amount',
            'user:id,name',
        ]);
        // FINANCIAL CALCULATION FIX
        $remainingDue = round((float) $payment->customer->current_due, 2);
        $previousDue = round((float) ($payment->previous_due ?? ($remainingDue + (float) $payment->amount)), 2);
        $receiptRemainingDue = round((float) ($payment->remaining_due ?? $remainingDue), 2);
        $receiptNo = 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        return view('admin.payments.receipt', compact('payment', 'previousDue', 'remainingDue', 'receiptRemainingDue', 'receiptNo'));
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
                // DUE COLLECTION IMPROVEMENT
                // INVOICE PAYMENT FLOW
                $payment->load('allocations');

                foreach ($payment->allocations as $allocation) {
                    $invoice = Invoice::lockForUpdate()->find($allocation->invoice_id);
                    if (! $invoice) {
                        continue;
                    }

                    // FINANCIAL CALCULATION FIX
                    // CRITICAL ACCOUNTING FIX
                    // DUE CALCULATION FIX
                    $amount = round((float) $allocation->amount, 2);
                    $newReceived = round(max(0, (float) $invoice->received_amount - $amount), 2);
                    $newDue = round((float) $invoice->due_amount + $amount, 2);
                    $invoice->update([
                        'received_amount' => number_format($newReceived, 2, '.', ''),
                        'due_amount' => number_format($newDue, 2, '.', ''),
                    ]);
                }

                // Delete allocations after reversing invoice balances.
                $payment->allocations()->delete();

                Log::info('Payment deleted', ['payment_id' => $payment->id, 'amount' => $payment->amount, 'user_id' => Auth::id()]);

                $payment->delete();
            });

            return back()->with('success', 'Payment record deleted.');
        } catch (Exception $e) {
            Log::error('Payment deletion failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting payment. Please try again.');
        }
    }
}
