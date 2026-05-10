@extends('layouts.admin')

@section('title', 'Approvals')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="fw-bold">Staff Approval Center</h3>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Product Requests</h5>
        </div>
        <div class="card-body p-0">
            {{-- // STAFF PRODUCT REQUEST --}}
            {{-- // PRODUCT APPROVAL FLOW --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Requested By</th>
                            <th>Product</th>
                            <th class="text-end">Requested Price</th>
                            <th>Status</th>
                            <th>Admin Notes</th>
                            <th>Request Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productRequests as $request)
                            <tr>
                                <td class="ps-3">{{ $request->requester->name ?? 'Staff' }}</td>
                                <td>
                                    <div class="fw-bold">{{ $request->requested_product_name }}</div>
                                    <small class="text-muted">{{ $request->model_no ?: 'No model' }} {{ $request->pack_size ? '| ' . $request->pack_size : '' }}</small>
                                </td>
                                <td class="text-end">Tk. {{ number_format($request->requested_price, 2) }}</td>
                                <td>
                                    <span class="badge {{ $request->status === 'approved' ? 'bg-success' : ($request->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td>{{ $request->admin_notes ?: '---' }}</td>
                                <td>{{ $request->created_at->format('d M, Y') }}</td>
                                <td class="text-end pe-3">
                                    @if($request->status === 'pending')
                                        <button class="btn btn-sm btn-success" data-bs-toggle="collapse" data-bs-target="#approveProduct{{ $request->id }}">Review</button>
                                    @else
                                        {{ $request->reviewer->name ?? 'Reviewed' }}
                                    @endif
                                </td>
                            </tr>
                            @if($request->status === 'pending')
                                <tr class="collapse bg-light" id="approveProduct{{ $request->id }}">
                                    <td colspan="7" class="p-3">
                                        <form method="POST" action="{{ route('admin.productRequests.approve', $request) }}" class="row g-2 align-items-end mb-3" data-loading-text="Approving...">
                                            @csrf
                                            <div class="col-md-3">
                                                <label class="form-label small">Product Name</label>
                                                <input name="approved_product_name" class="form-control" value="{{ $request->requested_product_name }}" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Product ID</label>
                                                <input name="generated_product_id" class="form-control" value="{{ old('generated_product_id', 'PRD-' . str_pad($request->id, 5, '0', STR_PAD_LEFT)) }}" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Cost</label>
                                                <input type="number" step="0.01" name="approved_cost_price" class="form-control" value="0" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Selling</label>
                                                <input type="number" step="0.01" name="approved_selling_price" class="form-control" value="{{ $request->requested_price }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Category</label>
                                                <input name="category" class="form-control" value="{{ $request->category ?: 'Medical Equipment' }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Model</label>
                                                <input name="model_no" class="form-control" value="{{ $request->model_no }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Pack Size</label>
                                                <input name="pack_size" class="form-control" value="{{ $request->pack_size }}">
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label small">Admin Notes</label>
                                                <input name="admin_notes" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-success w-100" type="submit">Approve Product</button>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ route('admin.productRequests.reject', $request) }}" class="row g-2" data-loading-text="Rejecting...">
                                            @csrf
                                            <div class="col-md-9">
                                                <input name="admin_notes" class="form-control" placeholder="Reason for rejection" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-outline-danger w-100" type="submit">Reject Request</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No product requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($productRequests->hasPages())
            <div class="card-footer bg-white">{{ $productRequests->links() }}</div>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Special Price Requests</h5>
        </div>
        <div class="card-body p-0">
            {{-- // STAFF PRICE RESTRICTION --}}
            {{-- // PRICE APPROVAL SYSTEM --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Requested By</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th class="text-end">Current</th>
                            <th class="text-end">Requested</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($priceRequests as $request)
                            <tr>
                                <td class="ps-3">{{ $request->requester->name ?? 'Staff' }}</td>
                                <td>{{ $request->product->product_name ?? 'Product' }}</td>
                                <td>{{ $request->customer ? (($request->customer->customer_id ?? '') . ' - ' . $request->customer->hospital_name) : 'General' }}</td>
                                <td class="text-end">Tk. {{ number_format($request->current_price, 2) }}</td>
                                <td class="text-end fw-bold">Tk. {{ number_format($request->requested_price, 2) }}</td>
                                <td>
                                    <span class="badge {{ $request->status === 'approved' ? 'bg-success' : ($request->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    @if($request->status === 'pending')
                                        <form method="POST" action="{{ route('admin.priceRequests.approve', $request) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                        </form>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#rejectPrice{{ $request->id }}">Reject</button>
                                    @else
                                        {{ $request->reviewer->name ?? 'Reviewed' }}
                                    @endif
                                </td>
                            </tr>
                            @if($request->status === 'pending')
                                <tr class="collapse bg-light" id="rejectPrice{{ $request->id }}">
                                    <td colspan="7" class="p-3">
                                        <form method="POST" action="{{ route('admin.priceRequests.reject', $request) }}" class="row g-2" data-loading-text="Rejecting...">
                                            @csrf
                                            <div class="col-md-9">
                                                <input name="admin_notes" class="form-control" placeholder="Reason for rejection" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-outline-danger w-100" type="submit">Reject Price</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No price requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($priceRequests->hasPages())
            <div class="card-footer bg-white">{{ $priceRequests->links() }}</div>
        @endif
    </div>
</div>
@endsection
