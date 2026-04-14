@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Customer Management</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i> Add New Customer
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Customer List</h5>
                </div>
                <div class="col-md-4">
                    <form action="{{ route('customers.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or mobile..." value="{{ $search ?? '' }}">
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
                            <th class="ps-3">Name</th>
                            <th>Hospital</th>
                            <th>Mobile</th>
                            <th>Address</th>
                            <th>Invoices</th>
                            <th>Current Due</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">{{ $customer->customer_name }}</div>
                                </td>
                                <td>{{ $customer->hospital_name ?? '---' }}</td>
                                <td>{{ $customer->mobile }}</td>
                                <td><small class="text-muted">{{ Str::limit($customer->address, 30) }}</small></td>
                                <td><span class="badge bg-secondary">{{ $customer->invoices_count }}</span></td>
                                <td>
                                    @if($customer->current_due > 0)
                                        <span class="text-danger fw-bold">${{ number_format($customer->current_due, 2) }}</span>
                                    @else
                                        <span class="text-success fw-bold">$0.00</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-outline-info" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if(auth()->user()->role === 'admin')
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $customer->id }}" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @if(auth()->user()->role === 'admin')
                                        <!-- Delete Modal -->
                                        <div class="modal fade text-start" id="deleteModal{{ $customer->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete customer <strong>{{ $customer->customer_name }}</strong>?
                                                        <p class="text-danger mt-2 small"><i class="bi bi-exclamation-triangle-fill"></i> This action cannot be undone and may fail if the customer has invoices.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
