@extends('layouts.admin')

@section('title', 'Profit Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Profit & Margin Report</h3>
        </div>
        <div class="col-md-6 text-end d-print-none">
            <button onclick="window.print()" class="btn btn-secondary me-2">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body">
            <form action="{{ route('reports.profit') }}" method="GET" class="row align-items-end">
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
                            <th class="text-end pe-3">Profit</th>
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
                        @foreach($monthlyProfit as $item)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $months[$item->month] }}</td>
                                <td class="text-end pe-3 text-success fw-bold">৳ {{ number_format($item->profit, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
