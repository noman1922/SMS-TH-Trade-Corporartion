@extends('layouts.admin')

@section('title', 'Profit Report')

@section('content')
{{-- // STANDARD PRINT SYSTEM --}}
{{-- // REPORT PRINT FLOW --}}
{{-- // ERP REPORT PRINT LAYOUT --}}
<div class="container-fluid standard-print-page">
    <div class="row mb-4 d-print-none">
        <div class="col-md-6">
            <h3 class="fw-bold">Profit & Margin Report</h3>
        </div>
        <div class="col-md-6 text-end d-print-none">
            {{-- // SINGLE TAB PRINT FIX — no new tab, no duplicate pages --}}
            <button type="button" onclick="handlePrintClick(this)" class="btn btn-secondary me-2">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>

    <div class="standard-print-title-row d-none">
        <div>
            <h1 class="standard-print-title">Profit & Margin Report</h1>
            <div>TH Trade Corporation</div>
        </div>
        <div class="standard-print-meta">
            <strong>Period:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}<br>
            <strong>Printed:</strong> {{ now()->format('d M, Y h:i A') }}
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body">
            {{-- // RESPONSIVENESS ROLLBACK --}}
            <form action="{{ route('reports.profit') }}" method="GET" class="row align-items-end" data-loading-text="Generating...">
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
                        <i class="bi bi-filter me-1"></i> Analyze Profit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-info">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Gross Revenue</h6>
                    <h3 class="mb-0 fw-bold">৳ {{ number_format($profitData->total_sales, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-secondary">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Cost of Goods</h6>
                    <h3 class="mb-0 fw-bold">৳ {{ number_format($profitData->total_cost, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Gross Profit</h6>
                    <h3 class="mb-0 fw-bold text-success">৳ {{ number_format($profitData->gross_profit, 2) }}</h3>
                    @if($profitData->total_sales > 0)
                        <small class="text-muted">Margin: {{ number_format(($profitData->gross_profit / $profitData->total_sales) * 100, 1) }}%</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Profit Breakdown by Month ({{ Carbon\Carbon::parse($fromDate)->year }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Month</th>
                            <th class="text-end">Sales</th>
                            <th class="text-end">Cost</th>
                            <th class="text-end">Profit</th>
                            <th class="text-end pe-3">Margin</th>
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
                        {{-- // PAYMENT FLOW IMPROVEMENT --}}
                        {{-- // REPORT TIMELINE --}}
                        @foreach($monthlyProfit as $item)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $months[$item->month] }}</td>
                                <td class="text-end">Tk. {{ number_format($item->sales, 2) }}</td>
                                <td class="text-end">Tk. {{ number_format($item->cost, 2) }}</td>
                                <td class="text-end text-success fw-bold">Tk. {{ number_format($item->profit, 2) }}</td>
                                <td class="text-end pe-3">{{ $item->sales > 0 ? number_format(($item->profit / $item->sales) * 100, 1) . '%' : '---' }}</td>
                            </tr>
                        @endforeach
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