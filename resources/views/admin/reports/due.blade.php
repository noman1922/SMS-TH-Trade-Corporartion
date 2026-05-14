@extends('layouts.admin')

@section('title', 'Due Report')

@section('content')
{{-- // STANDARD PRINT SYSTEM --}}
{{-- // REPORT PRINT FLOW --}}
{{-- // ERP REPORT PRINT LAYOUT --}}
<div class="container-fluid standard-print-page">
    <div class="row mb-4 d-print-none">
        <div class="col-md-6">
            <h3 class="fw-bold">Outstanding Due Report</h3>
        </div>
        <div class="col-md-6 text-end d-print-none">
            {{-- // SINGLE TAB PRINT FIX — no new tab, no duplicate pages --}}
            <button type="button" onclick="handlePrintClick(this)" class="btn btn-secondary">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>

    <div class="standard-print-title-row d-none">
        <div>
            <h1 class="standard-print-title">Outstanding Due Report</h1>
            <div>TH Trade Corporation</div>
        </div>
        <div class="standard-print-meta">
            <strong>Collection Period:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}<br>
            <strong>Printed:</strong> {{ now()->format('d M, Y h:i A') }}
        </div>
    </div>

    {{-- // PAYMENT FLOW IMPROVEMENT --}}
    {{-- // REPORT TIMELINE --}}
    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body">
            <form action="{{ route('reports.due') }}" method="GET" class="row align-items-end" data-loading-text="Generating...">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Collection From</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Collection To</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary px-4 w-100" data-loading-text="Generating...">
                        <i class="bi bi-filter me-1"></i> View Due Collections
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-danger">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Outstanding</h6>
                    <h2 class="mb-0 fw-bold text-danger">৳ {{ number_format($totalOutstanding, 2) }}</h2>
                    <small class="text-muted">Across {{ $customers->count() }} customers</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Due Collected</h6>
                    <h2 class="mb-0 fw-bold text-success">Tk. {{ number_format($collectionSummary['amount'], 2) }}</h2>
                    <small class="text-muted">{{ $collectionSummary['count'] }} collections in selected period</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Due Collection Report</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th class="text-end">Previous Due</th>
                            <th class="text-end">Paid Amount</th>
                            <th class="text-end">Remaining Due</th>
                            <th class="pe-3">Collected By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collections as $payment)
                            @php
                                $allocatedInvoices = $payment->allocations->pluck('invoice.invoice_no')->filter()->values();
                                $invoiceLabel = $payment->invoice
                                    ? $payment->invoice->invoice_no
                                    : ($allocatedInvoices->isNotEmpty() ? $allocatedInvoices->join(', ') : 'Full customer due');
                            @endphp
                            <tr>
                                <td class="ps-3">{{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}</td>
                                <td class="fw-bold">{{ $invoiceLabel }}</td>
                                <td>
                                    <div class="fw-bold">{{ $payment->customer->customer_id ?? '' }} - {{ $payment->customer->hospital_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $payment->customer->customer_name ?? '' }}</small>
                                </td>
                                <td class="text-end">Tk. {{ number_format($payment->previous_due ?? 0, 2) }}</td>
                                <td class="text-end text-success fw-bold">Tk. {{ number_format($payment->amount, 2) }}</td>
                                <td class="text-end text-danger">Tk. {{ number_format($payment->remaining_due ?? 0, 2) }}</td>
                                <td class="pe-3">{{ $payment->user->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No due collections found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Due Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Customers with Outstanding Dues</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Customer ID</th>
                            <th>Customer Name</th>
                            <th>Organization / Hospital</th>
                            <th>Mobile</th>
                            <th>Total Purchase</th>
                            <th>Total Paid</th>
                            <th>Outstanding Due</th>
                            <th class="text-end pe-3 d-print-none">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                            <tr>
                                <td class="ps-3">{{ $index + 1 }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $customer->customer_id }}</span></td>
                                <td class="fw-bold">{{ $customer->customer_name ?: '---' }}</td>
                                <td>{{ $customer->hospital_name }}</td>
                                <td>{{ $customer->mobile }}</td>
                                <td>৳ {{ number_format($customer->total_purchased, 2) }}</td>
                                <td class="text-success">৳ {{ number_format($customer->total_paid, 2) }}</td>
                                <td><span class="text-danger fw-bold">৳ {{ number_format($customer->current_due, 2) }}</span></td>
                                <td class="text-end pe-3 d-print-none">
                                    <a href="{{ route('payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">
                                        Collect
                                    </a>
                                    {{-- // ROW PRINT FIX --}}
                                    <a href="{{ route('receipt.print', $customer->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle text-success fs-3"></i>
                                    <p class="mb-0 mt-2">No outstanding dues! All customers are settled.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="standard-print-footer d-none">
        <div class="standard-print-signatures">
            <div class="standard-print-signature">Prepared By</div>
            <div class="standard-print-signature">Authorized Signature</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // SINGLE TAB PRINT FIX
    // ERP PRINT STANDARDIZATION
    function handlePrintClick(button) {
        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Processing...';
        window.setTimeout(function() {
            window.print();
            button.disabled = false;
            button.innerHTML = original;
        }, 120);
    }
</script>
@endsection
