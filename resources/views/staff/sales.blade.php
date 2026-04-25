@extends('layouts.admin')

@section('title', 'My Sales')

@section('content')
{{-- // STAFF DASHBOARD FIX --}}
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="fw-bold">My Sales</h3>
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
                                <td>{{ $invoice->customer->customer_name }}</td>
                                <td>à§³ {{ number_format($invoice->net_payable, 2) }}</td>
                                <td class="text-success">à§³ {{ number_format($invoice->received_amount, 2) }}</td>
                                <td class="text-danger fw-bold">à§³ {{ number_format($invoice->due_amount, 2) }}</td>
                                <td class="text-end pe-3">
                                    {{-- // ROW PRINT FIX --}}
                                    <a href="{{ route('invoice.print', $invoice->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No sales records found.</td>
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
@endsection
