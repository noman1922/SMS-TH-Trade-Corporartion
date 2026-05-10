@extends('layouts.admin')

@section('title', 'Sales Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Sales Report</h3>
        </div>
        {{-- // ROW PRINT FIX --}}
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body">
            {{-- // RESPONSIVENESS ROLLBACK --}}
            <form action="{{ route('reports.sales') }}" method="GET" class="row align-items-end" data-loading-text="Generating...">
                <div class="col-md-4">
                    <label class="form-label small text-muted">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary px-4 w-100" data-loading-text="Generating...">
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
                    <h3 class="mb-0 fw-bold">৳ {{ number_format($summary['total_amount'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Received</h6>
                    <h3 class="mb-0 fw-bold text-success">৳ {{ number_format($summary['total_received'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-danger">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Due</h6>
                    <h3 class="mb-0 fw-bold text-danger">৳ {{ number_format($summary['total_due'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    {{-- // PAYMENT FLOW IMPROVEMENT --}}
    {{-- // REPORT TIMELINE --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Monthly Sales Timeline ({{ $reportYear }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Month</th>
                            <th class="text-end">Invoices</th>
                            <th class="text-end">Sales</th>
                            <th class="text-end">Received</th>
                            <th class="text-end pe-3">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $months = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ];
                        @endphp
                        @foreach($months as $monthNumber => $monthName)
                            @php $row = $monthlySales->get($monthNumber); @endphp
                            <tr>
                                <td class="ps-3 fw-bold">{{ $monthName }}</td>
                                <td class="text-end">{{ (int) ($row->total_invoices ?? 0) }}</td>
                                <td class="text-end">Tk. {{ number_format((float) ($row->total_sales ?? 0), 2) }}</td>
                                <td class="text-end text-success">Tk. {{ number_format((float) ($row->total_received ?? 0), 2) }}</td>
                                <td class="text-end pe-3 text-danger">Tk. {{ number_format((float) ($row->total_due ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $invoice->invoice_no }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</td>
                                <td>
                                    <div class="fw-bold">{{ $invoice->customer->customer_id ?? '---' }} - {{ $invoice->customer->hospital_name ?? '---' }}</div>
                                    <small class="text-muted">{{ $invoice->customer->customer_name ?? '' }}</small>
                                </td>
                                <td>৳ {{ number_format($invoice->net_payable, 2) }}</td>
                                <td class="text-success">৳ {{ number_format($invoice->received_amount, 2) }}</td>
                                <td class="text-danger fw-bold">৳ {{ number_format($invoice->due_amount, 2) }}</td>
                                <td class="text-end pe-3">
                                    {{-- // ROW PRINT FIX --}}
                                    <a href="{{ route('invoice.print', $invoice->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No sales records found for this period.</td>
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
