@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Sales Report</h3>
        </div>
        <div class="col-md-6 text-end">
            <button onclick="window.print()" class="btn btn-secondary me-2">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body">
            <form action="{{ route('reports.sales') }}" method="GET" class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary px-4 w-100">
                        <i class="bi bi-filter me-1"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Invoices</h6>
                    <h3 class="mb-0 fw-bold">{{ $summary['total_invoices'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-info">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Sales</h6>
                    <h3 class="mb-0 fw-bold">${{ number_format($summary['total_amount'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Received</h6>
                    <h3 class="mb-0 fw-bold text-success">${{ number_format($summary['total_received'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-danger">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Due</h6>
                    <h3 class="mb-0 fw-bold text-danger">${{ number_format($summary['total_due'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $invoice->invoice_no }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</td>
                                <td>{{ $invoice->customer->customer_name }}</td>
                                <td>${{ number_format($invoice->net_payable, 2) }}</td>
                                <td class="text-success">${{ number_format($invoice->received_amount, 2) }}</td>
                                <td class="text-danger fw-bold">${{ number_format($invoice->due_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No sales records found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer bg-white d-print-none">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
