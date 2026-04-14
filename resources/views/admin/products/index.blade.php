@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Product Management</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add New Product
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Product List</h5>
                </div>
                <div class="col-md-4">
                    <form action="{{ route('products.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="{{ $search ?? '' }}">
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
                            <th class="ps-3">Product ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Model No</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Stock</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-3 fw-medium">{{ $product->product_id }}</td>
                                <td>{{ $product->product_name }}</td>
                                <td><span class="badge bg-info text-dark">{{ $product->category }}</span></td>
                                <td>{{ $product->model_no ?? 'N/A' }}</td>
                                <td>${{ number_format($product->cost_price, 2) }}</td>
                                <td>${{ number_format($product->selling_price, 2) }}</td>
                                <td>
                                    @if($product->isLowStock())
                                        <span class="badge bg-danger" title="Low Stock">
                                            {{ $product->stock_quantity }} <i class="bi bi-exclamation-triangle-fill ms-1"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            {{ $product->stock_quantity }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    Are you sure you want to delete product <strong>{{ $product->product_name }}</strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
