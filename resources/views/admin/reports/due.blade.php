@extends('layouts.admin')

@section('title', 'Due Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Outstanding Due Report</h3>
        </div>
        <div class="col-md-6 text-end d-print-none">
            <button onclick="window.print()" class="btn btn-secondary me-2">
                <i class="bi bi-printer me-1"></i> Print
            </button>
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
                            <th>Customer Name</th>
                            <th>Hospital</th>
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
                                <td class="fw-bold">{{ $customer->customer_name }}</td>
                                <td>{{ $customer->hospital_name ?? '---' }}</td>
                                <td>{{ $customer->mobile }}</td>
                                <td>৳ {{ number_format($customer->total_purchased, 2) }}</td>
                                <td class="text-success">৳ {{ number_format($customer->total_paid, 2) }}</td>
                                <td><span class="text-danger fw-bold">৳ {{ number_format($customer->current_due, 2) }}</span></td>
                                <td class="text-end pe-3 d-print-none">
                                    <a href="{{ route('payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">
                                        Collect
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
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
</div>
@endsection
