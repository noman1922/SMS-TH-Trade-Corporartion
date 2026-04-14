@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Due Management</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="bi bi-cash-coin me-1"></i> Collect Bulk Payment
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
                    <form action="{{ route('payments.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search customer..." value="{{ $search ?? '' }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
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
                                    <div class="fw-bold text-primary">{{ $customer->customer_name }}</div>
                                    <small class="text-muted">{{ $customer->hospital_name ?? '' }}</small>
                                </td>
                                <td>{{ $customer->mobile }}</td>
                                <td>${{ number_format($customer->total_purchased, 2) }}</td>
                                <td><span class="text-success">${{ number_format($customer->total_paid, 2) }}</span></td>
                                <td><span class="text-danger fw-bold">${{ number_format($customer->current_due, 2) }}</span></td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">
                                        Collect Payment
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
</div>
@endsection
