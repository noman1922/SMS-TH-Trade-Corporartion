@extends('layouts.admin')

@section('title', 'Staff Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 g-3 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold">Staff Management</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('staff-management.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i> Add Staff
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center g-3">
                <div class="col-lg">
                    <h5 class="mb-0">Staff Accounts</h5>
                </div>
                <div class="col-lg-5">
                    {{-- // STAFF MANAGEMENT --}}
                    <form action="{{ route('staff-management.index') }}" method="GET" class="js-debounce-search" data-debounce="400" data-loading-text="Searching...">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search staff name or email..." value="{{ $search ?? '' }}">
                            <button class="btn btn-outline-secondary" type="submit" aria-label="Search staff">
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
                            <th class="ps-4">Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $member)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Staff</span></td>
                                <td>{{ $member->created_at->format('d M, Y') }}</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('staff-management.edit', $member) }}" class="btn btn-outline-primary" aria-label="Edit {{ $member->name }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStaff{{ $member->id }}" aria-label="Delete {{ $member->name }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <div class="modal fade" id="deleteStaff{{ $member->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    Delete staff account <strong>{{ $member->name }}</strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('staff-management.destroy', $member) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger" data-loading-text="Deleting...">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No staff accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($staff->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $staff->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
