@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold">Staff Dashboard</h2>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">My Bill Entries</h6>
                    <p class="display-6 mb-0 fw-bold">{{ $myBillEntries }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">My Collections</h6>
                    <p class="display-6 mb-0 fw-bold">{{ $myCollections }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Pending Requests</h6>
                    {{-- // STAFF PRODUCT REQUEST --}}
                    {{-- // PRICE APPROVAL SYSTEM --}}
                    <p class="display-6 mb-0 fw-bold">{{ $pendingProductRequests + $pendingPriceRequests }}</p>
                    <small class="text-muted">{{ $pendingProductRequests }} product, {{ $pendingPriceRequests }} price</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">Staff Operations</div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('pos.index') }}" class="list-group-item list-group-item-action">New Billing Entry</a>
                        <a href="{{ route('stock.index') }}" class="list-group-item list-group-item-action">Check Stock Availability</a>
                        <a href="{{ route('staff.sales') }}" class="list-group-item list-group-item-action">View My Sales</a>
                        <a href="{{ route('payments.index') }}" class="list-group-item list-group-item-action">View Due Collections</a>
                        <a href="{{ route('staff.productRequests.index') }}" class="list-group-item list-group-item-action">Request New Product</a>
                        <a href="{{ route('staff.priceRequests.index') }}" class="list-group-item list-group-item-action">Request Special Price</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Recent Invoices</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Invoice</th>
                                    <th>Customer</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Due</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $invoice)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $invoice->invoice_no }}</td>
                                        <td>{{ $invoice->customer->customer_id ?? '---' }} - {{ $invoice->customer->hospital_name ?? '---' }}</td>
                                        <td class="text-end">Tk. {{ number_format($invoice->net_payable, 2) }}</td>
                                        <td class="text-end text-danger">Tk. {{ number_format($invoice->due_amount, 2) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No invoices yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Recent Collections</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th class="text-end pe-3">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCollections as $payment)
                                    <tr>
                                        <td class="ps-3">{{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}</td>
                                        <td>{{ optional($payment->invoice)->invoice_no ?: 'Full customer due' }}</td>
                                        <td>{{ $payment->customer->customer_id ?? '---' }} - {{ $payment->customer->hospital_name ?? '---' }}</td>
                                        <td class="text-end pe-3 text-success fw-bold">Tk. {{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No collections yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
