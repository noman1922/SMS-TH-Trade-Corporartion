@extends('layouts.admin')

@section('title', 'Customer Ledger')

@section('styles')
<style>
    /* // CUSTOMER LEDGER SYSTEM */
    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #111 !important;
        }

        .table th,
        .table td {
            color: #111 !important;
            border-color: #555 !important;
            font-size: 10px;
            padding: 4px 5px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Customer Ledger</h3>
        </div>
        <div class="col-md-6 text-end d-print-none">
            @if($customer)
                <a href="{{ route('receipt.print', $customer->id) }}" class="btn btn-secondary me-2" target="_blank">
                    <i class="bi bi-printer me-1"></i> Print Statement
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Print Ledger
                </button>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body">
            {{-- // LEDGER IMPROVEMENT --}}
            {{-- // REPORT TIMELINE --}}
            <form action="{{ route('reports.ledger') }}" method="GET" class="row align-items-end g-3" data-loading-text="Generating...">
                <div class="col-md-5">
                    <label class="form-label small text-muted">Select Customer</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ ($customer && $customer->id == $c->id) ? 'selected' : '' }}>
                                {{ $c->customer_id }} - {{ $c->hospital_name }} @if($c->customer_name) ({{ $c->customer_name }}) @endif - {{ $c->mobile }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary px-4 w-100" data-loading-text="Generating...">
                        <i class="bi bi-journal-text me-1"></i> View Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($customer)
        <div class="d-none d-print-block mb-3">
            <h4 class="fw-bold mb-1">TH Trade Corporation - Customer Ledger</h4>
            <div><strong>Customer ID:</strong> {{ $customer->customer_id }} | <strong>Organization:</strong> {{ $customer->hospital_name }}</div>
            <div><strong>Contact:</strong> {{ $customer->customer_name ?: 'N/A' }} | <strong>Mobile:</strong> {{ $customer->mobile }}</div>
            <div><strong>Generated:</strong> {{ now()->format('d M, Y h:i A') }}</div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 border-start border-4 border-secondary">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Opening Balance</h6>
                        <h4 class="mb-0 fw-bold">Tk. {{ number_format($openingBalance, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 border-start border-4 border-primary">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Sales Amount</h6>
                        <h4 class="mb-0 fw-bold">Tk. {{ number_format($customer->total_purchased, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 border-start border-4 border-success">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Paid Amount</h6>
                        <h4 class="mb-0 fw-bold text-success">Tk. {{ number_format($customer->total_paid, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 border-start border-4 border-danger">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Closing Balance</h6>
                        <h4 class="mb-0 fw-bold text-danger">Tk. {{ number_format($closingBalance, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    Ledger Statement - {{ $customer->customer_id }} - {{ $customer->hospital_name }}
                    @if($fromDate || $toDate)
                        <small class="text-muted d-block mt-1">
                            Timeline: {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : 'Beginning' }}
                            to {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M, Y') : 'Today' }}
                        </small>
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Transaction Type</th>
                                <th>Invoice No</th>
                                <th>Reference</th>
                                <th class="text-end">Sales Amount</th>
                                <th class="text-end">Paid Amount</th>
                                <th class="text-end">Due Amount</th>
                                <th class="text-end">Payment Collection</th>
                                <th class="text-end pe-3">Balance After Transaction</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-warning">
                                <td class="ps-3">Opening</td>
                                <td><span class="badge bg-secondary">Opening</span></td>
                                <td>---</td>
                                <td>Previous Due</td>
                                <td class="text-end">Tk. {{ number_format($openingBalance, 2) }}</td>
                                <td class="text-end">---</td>
                                <td class="text-end">Tk. {{ number_format($openingBalance, 2) }}</td>
                                <td class="text-end">---</td>
                                <td class="text-end pe-3 fw-bold">Tk. {{ number_format($openingBalance, 2) }}</td>
                            </tr>
                            @forelse($ledger as $entry)
                                <tr>
                                    <td class="ps-3">{{ \Carbon\Carbon::parse($entry->invoice_date)->format('d M, Y') }}</td>
                                    <td>
                                        @if($entry->type === 'Invoice')
                                            <span class="badge bg-danger-subtle text-danger">Invoice</span>
                                        @elseif($entry->type === 'Due Collection')
                                            <span class="badge bg-success-subtle text-success">Due Collection</span>
                                        @else
                                            <span class="badge bg-info-subtle text-info">Payment</span>
                                        @endif
                                    </td>
                                    <td>{{ $entry->invoice_no ?: '---' }}</td>
                                    <td>{{ $entry->reference }}</td>
                                    <td class="text-end">{{ $entry->sales_amount > 0 ? 'Tk. ' . number_format($entry->sales_amount, 2) : '---' }}</td>
                                    <td class="text-end">{{ $entry->paid_amount > 0 ? 'Tk. ' . number_format($entry->paid_amount, 2) : '---' }}</td>
                                    <td class="text-end">{{ $entry->due_amount > 0 ? 'Tk. ' . number_format($entry->due_amount, 2) : '---' }}</td>
                                    <td class="text-end">{{ $entry->payment_collection > 0 ? 'Tk. ' . number_format($entry->payment_collection, 2) : '---' }}</td>
                                    <td class="text-end pe-3 fw-bold {{ $entry->balance > 0 ? 'text-danger' : 'text-success' }}">
                                        Tk. {{ number_format($entry->balance, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">No transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <td colspan="8" class="ps-3 fw-bold">Closing Balance</td>
                                <td class="text-end pe-3 fw-bold">Tk. {{ number_format($closingBalance, 2) }}</td>
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
