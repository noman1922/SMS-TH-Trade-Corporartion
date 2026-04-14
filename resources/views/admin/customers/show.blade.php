@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Customer Details</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('customers.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Customer
            </a>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Purchase</h6>
                    <h3 class="mb-0 fw-bold">${{ number_format($customer->total_purchased, 2) }}</h3>
                    <small class="text-muted">Across {{ $customer->invoices_count }} invoices</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Paid</h6>
                    <h3 class="mb-0 fw-bold text-success">${{ number_format($customer->total_paid, 2) }}</h3>
                    <small class="text-muted">Lifetime payments</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-danger">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Outstanding Due</h6>
                    <h3 class="mb-0 fw-bold text-danger">${{ number_format($customer->current_due, 2) }}</h3>
                    <small class="text-muted">Including ${{ number_format($customer->previous_due, 2) }} previous due</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Customer Info -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase">Customer Name</label>
                        <p class="mb-0 fw-bold">{{ $customer->customer_name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase">Hospital/Clinic</label>
                        <p class="mb-0 fw-bold">{{ $customer->hospital_name ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase">Mobile Number</label>
                        <p class="mb-0 fw-bold">{{ $customer->mobile }}</p>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small text-uppercase">Billing Address</label>
                        <p class="mb-0 fw-bold">{{ $customer->address }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice History -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Invoice History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Invoice No</th>
                                    <th>Date</th>
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
                                        <td>${{ number_format($invoice->net_payable, 2) }}</td>
                                        <td><span class="text-success">${{ number_format($invoice->received_amount, 2) }}</span></td>
                                        <td>
                                            @if($invoice->due_amount > 0)
                                                <span class="text-danger fw-bold">${{ number_format($invoice->due_amount, 2) }}</span>
                                            @else
                                                <span class="text-success">$0.00</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('pos.print', $invoice->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No transaction records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($invoices->hasPages())
                    <div class="card-footer bg-white">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
