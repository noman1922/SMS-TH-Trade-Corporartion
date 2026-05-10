@extends('layouts.admin')

@section('title', 'Price Requests')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Request Special Price</h5>
                </div>
                <div class="card-body">
                    {{-- // STAFF PRICE RESTRICTION --}}
                    {{-- // PRICE APPROVAL SYSTEM --}}
                    <form method="POST" action="{{ route('staff.priceRequests.store') }}" data-loading-text="Submitting...">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-select">
                                <option value="">General / No customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->customer_id }} - {{ $customer->hospital_name }} @if($customer->customer_name) ({{ $customer->customer_name }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_id }} - {{ $product->product_name }} (Default: Tk. {{ number_format($product->selling_price, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Requested Price</label>
                            <div class="input-group">
                                <span class="input-group-text">Tk.</span>
                                <input type="number" step="0.01" min="0" name="requested_price" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" rows="2" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Price Request</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Price Approval History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Product</th>
                                    <th>Customer</th>
                                    <th class="text-end">Current</th>
                                    <th class="text-end">Requested</th>
                                    <th>Status</th>
                                    <th>Admin Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $request->product->product_name ?? 'Product' }}</td>
                                        <td>{{ $request->customer ? (($request->customer->customer_id ?? '') . ' - ' . $request->customer->hospital_name) : 'General' }}</td>
                                        <td class="text-end">Tk. {{ number_format($request->current_price, 2) }}</td>
                                        <td class="text-end fw-bold">Tk. {{ number_format($request->requested_price, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $request->status === 'approved' ? 'bg-success' : ($request->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->admin_notes ?: '---' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No price requests yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($requests->hasPages())
                    <div class="card-footer bg-white">{{ $requests->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
