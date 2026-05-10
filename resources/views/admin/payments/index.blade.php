@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Due Management</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="bi bi-cash-coin me-1"></i> Collect Due Payment
            </a>
        </div>
    </div>

    <!-- Due Summary Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Outstanding Due List</h5>
                </div>
                <div class="col-md-4">
                    {{-- // SEARCH INPUT OPTIMIZATION --}}
                    {{-- // PAYMENT FLOW IMPROVEMENT --}}
                    {{-- // REPORT TIMELINE --}}
                    <form action="{{ route('payments.index') }}" method="GET" class="row g-2 js-debounce-search" data-debounce="400" data-loading-text="Searching...">
                        <div class="col-md-12">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search customer or invoice..." value="{{ $search ?? '' }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-6">
                            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
                        </div>
                        <div class="col-6">
                            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Customer Name</th>
                            <th>Mobile</th>
                            <th>Total Purchase</th>
                            <th>Total Paid</th>
                            <th>Current Due</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-primary">{{ $customer->customer_id }} - {{ $customer->hospital_name }}</div>
                                    <small class="text-muted">{{ $customer->customer_name ?: 'No contact person' }}</small>
                                </td>
                                <td>{{ $customer->mobile }}</td>
                                <td>Tk. {{ number_format($customer->total_purchased, 2) }}</td>
                                <td><span class="text-success">Tk. {{ number_format($customer->total_paid, 2) }}</span></td>
                                <td><span class="text-danger fw-bold">Tk. {{ number_format($customer->current_due, 2) }}</span></td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">
                                        Collect Payment
                                    </a>
                                    {{-- // ROW PRINT FIX --}}
                                    <a href="{{ route('receipt.print', $customer->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No customers with outstanding due.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- // DUE COLLECTION IMPROVEMENT --}}
    {{-- // DUE HISTORY SYSTEM --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Due Collection Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Invoice No</th>
                            <th>Customer</th>
                            <th class="text-end">Previous Due</th>
                            <th class="text-end">Paid Amount</th>
                            <th class="text-end">Remaining Due</th>
                            <th>Date</th>
                            <th>Collected By</th>
                            <th class="text-end pe-3">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collections as $payment)
                            @php
                                $allocatedInvoices = $payment->allocations->pluck('invoice.invoice_no')->filter()->values();
                                $invoiceLabel = $payment->invoice
                                    ? $payment->invoice->invoice_no
                                    : ($allocatedInvoices->isNotEmpty() ? $allocatedInvoices->take(2)->join(', ') . ($allocatedInvoices->count() > 2 ? ' +' . ($allocatedInvoices->count() - 2) : '') : 'Full customer due');
                            @endphp
                            <tr>
                                <td class="ps-3 fw-bold">{{ $invoiceLabel }}</td>
                                <td>
                                    <div class="fw-bold">{{ $payment->customer->customer_id ?? '' }} - {{ $payment->customer->hospital_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $payment->customer->customer_name ?? '' }}</small>
                                </td>
                                <td class="text-end">Tk. {{ number_format($payment->previous_due ?? 0, 2) }}</td>
                                <td class="text-end text-success fw-bold">Tk. {{ number_format($payment->amount, 2) }}</td>
                                <td class="text-end text-danger">Tk. {{ number_format($payment->remaining_due ?? 0, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}</td>
                                <td>{{ $payment->user->name ?? 'System' }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('payment.receipt', $payment->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No due collection records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
