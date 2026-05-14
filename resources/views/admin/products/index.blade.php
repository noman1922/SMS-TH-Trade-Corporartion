@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 g-3 align-items-center">
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
            <div class="row align-items-center g-3">
                <div class="col-lg">
                    <h5 class="mb-0">Product List</h5>
                </div>
                <div class="col-lg-6">
                    {{-- // SEARCH INPUT OPTIMIZATION --}}
                    {{-- // PRODUCT PAGINATION IMPROVEMENT --}}
                    <form action="{{ route('products.index') }}" method="GET" class="js-debounce-search" data-debounce="400" data-loading-text="Searching...">
                        <div class="row g-2 justify-content-lg-end">
                            <div class="col-sm-7">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="{{ $search ?? '' }}">
                                    <button class="btn btn-outline-secondary" type="submit" aria-label="Search products">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <select name="per_page" class="form-select" onchange="this.form.submit()" aria-label="Products per page">
                                    @foreach([20, 50, 100] as $size)
                                        <option value="{{ $size }}" @selected($perPage === $size)>Show {{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            {{-- // CLEAN PRODUCT TABLE UI --}}
            <div class="table-responsive product-table-wrap">
                <table class="table table-hover align-middle mb-0 product-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Product ID</th>
                            <th class="product-name-col">Product Name</th>
                            <th>Category</th>
                            <th>Model No</th>
                            <th>Pack Size</th>
                            <th class="text-end">Cost Price</th>
                            <th class="text-end">Selling Price</th>
                            <th class="text-center">Stock</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-4 fw-semibold text-nowrap">{{ $product->product_id }}</td>
                                <td class="product-name-col">
                                    <div class="fw-semibold text-dark">{{ $product->product_name }}</div>
                                </td>
                                <td><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $product->category }}</span></td>
                                <td>{{ $product->model_no ?? 'N/A' }}</td>
                                <td>{{ $product->pack_size ?? 'N/A' }}</td>
                                <td class="text-end text-nowrap">${{ number_format($product->cost_price, 2) }}</td>
                                <td class="text-end text-nowrap">${{ number_format($product->selling_price, 2) }}</td>
                                <td class="text-center">
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
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-primary" aria-label="Edit {{ $product->product_name }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}" aria-label="Delete {{ $product->product_name }}">
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
                                                        <button type="submit" class="btn btn-danger" data-loading-text="Processing...">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white py-3">
                {{-- // PRODUCT PAGINATION IMPROVEMENT --}}
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 product-pagination">
                    <div class="text-muted small">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                    </div>
                    <div class="product-pagination-links">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    /* // CLEAN PRODUCT TABLE UI */
    .product-table-wrap {
        overflow-x: auto;
    }

    .product-table {
        min-width: 980px;
    }

    .product-table thead th {
        white-space: nowrap;
    }

    .product-table tbody td {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .product-table .product-name-col {
        min-width: 220px;
        max-width: 360px;
    }

    .product-table .btn-group .btn {
        width: 2.25rem;
        height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* // PRODUCT PAGINATION IMPROVEMENT */
    .product-pagination .pagination {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.25rem;
    }

    .product-pagination .page-link {
        min-width: 2.25rem;
        min-height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .product-pagination .page-item.active .page-link {
        color: #fff;
        box-shadow: 0 0.35rem 0.75rem rgba(59, 130, 246, 0.25);
    }

    @media (max-width: 767.98px) {
        .product-pagination-links {
            width: 100%;
        }

        .product-pagination .pagination {
            justify-content: flex-start;
        }
    }
</style>
@endsection
