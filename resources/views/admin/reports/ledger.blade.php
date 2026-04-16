@extends('layouts.admin')

@section('title', 'Customer Ledger')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Customer Ledger</h3>
        </div>
        <div class="col-md-6 text-end d-print-none">
            @if($customer)
                <button onclick="window.print()" class="btn btn-secondary me-2">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            @endif
        </div>
    </div>

    <!-- Customer Selector -->
    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body">
            <form action="{{ route('reports.ledger') }}" method="GET" class="row align-items-end">
                <div class="col-md-8">
                    <label class="form-label small text-muted">Select Customer</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ ($customer && $customer->id == $c->id) ? 'selected' : '' }}>
                                {{ $c->customer_name }} ({{ $c->mobile }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary px-4 w-100">
                        <i class="bi bi-journal-text me-1"></i> View Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($customer)
    <!-- Customer Info -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Debits</h6>
                    <h3 class="mb-0 fw-bold">৳ {{ number_format($customer->previous_due + $customer->total_purchased, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Credits</h6>
                    <h3 class="mb-0 fw-bold text-success">৳ {{ number_format($customer->total_paid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-danger">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Current Balance (Due)</h6>
                    <h3 class="mb-0 fw-bold text-danger">৳ {{ number_format($customer->current_due, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Header -->
    <div class="d-none d-print-block mb-4">
        <h4 class="fw-bold">TH Trade Corporation — Customer Ledger</h4>
        <p class="mb-0"><strong>Customer:</strong> {{ $customer->customer_name }} | <strong>Mobile:</strong> {{ $customer->mobile }}</p>
        <p><strong>Generated:</strong> {{ now()->format('d M, Y') }}</p>
        <hr>
    </div>

    <!-- Ledger Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Ledger Statement — {{ $customer->customer_name }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th class="text-end">Debit (৳)</th>
                            <th class="text-end">Credit (৳)</th>
                            <th class="text-end pe-3">Balance (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningBalance = $customer->previous_due; @endphp

                        @if($customer->previous_due > 0)
                        <tr class="table-warning">
                            <td class="ps-3">—</td>
                            <td><span class="badge bg-secondary">Opening</span></td>
                            <td>Previous Due</td>
                            <td class="text-end">{{ number_format($customer->previous_due, 2) }}</td>
                            <td class="text-end">—</td>
                            <td class="text-end pe-3 fw-bold">{{ number_format($runningBalance, 2) }}</td>
                        </tr>
                        @endif

                        @forelse($ledger as $entry)
                            @php
                                if ($entry->type === 'Invoice') {
                                    $runningBalance += $entry->debit;
                                } else {
                                    $runningBalance -= $entry->credit;
                                }
                            @endphp
                            <tr>
                                <td class="ps-3">{{ \Carbon\Carbon::parse($entry->date)->format('d M, Y') }}</td>
                                <td>
                                    @if($entry->type === 'Invoice')
                                        <span class="badge bg-danger-subtle text-danger">Invoice</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Payment</span>
                                    @endif
                                </td>
                                <td>{{ $entry->reference }}</td>
                                <td class="text-end {{ $entry->debit > 0 ? 'text-danger' : '' }}">
                                    {{ $entry->debit > 0 ? number_format($entry->debit, 2) : '—' }}
                                </td>
                                <td class="text-end {{ $entry->credit > 0 ? 'text-success' : '' }}">
                                    {{ $entry->credit > 0 ? number_format($entry->credit, 2) : '—' }}
                                </td>
                                <td class="text-end pe-3 fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($runningBalance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="5" class="ps-3 fw-bold">Closing Balance</td>
                            <td class="text-end pe-3 fw-bold">৳ {{ number_format($customer->current_due, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-journal-text fs-1"></i>
            <p class="mb-0 mt-3">Select a customer above to view their ledger statement.</p>
        </div>
    </div>
    @endif
</div>
@endsection
