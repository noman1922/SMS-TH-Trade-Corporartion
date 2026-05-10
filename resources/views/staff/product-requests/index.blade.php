@extends('layouts.admin')

@section('title', 'Product Requests')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Request New Product</h5>
                </div>
                <div class="card-body">
                    {{-- // STAFF PRODUCT REQUEST --}}
                    <form method="POST" action="{{ route('staff.productRequests.store') }}" data-loading-text="Submitting...">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="requested_product_name" class="form-control" value="{{ old('requested_product_name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Requested Price</label>
                            <div class="input-group">
                                <span class="input-group-text">Tk.</span>
                                <input type="number" step="0.01" name="requested_price" class="form-control" value="{{ old('requested_price') }}" required>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="model_no" class="form-control" value="{{ old('model_no') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pack Size</label>
                                <input type="text" name="pack_size" class="form-control" value="{{ old('pack_size') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" value="{{ old('category', 'Medical Equipment') }}">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Request</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Product Requests</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Product Name</th>
                                    <th>Requested Price</th>
                                    <th>Status</th>
                                    <th>Admin Notes</th>
                                    <th>Request Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $request->requested_product_name }}</td>
                                        <td>Tk. {{ number_format($request->requested_price, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $request->status === 'approved' ? 'bg-success' : ($request->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->admin_notes ?: '---' }}</td>
                                        <td>{{ $request->created_at->format('d M, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No product requests yet.</td></tr>
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
