@extends('layouts.admin')

@section('title', 'Edit Staff')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 g-3 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold">Edit Staff Account</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('staff-management.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Update Staff: {{ $staff->name }}</h5>
        </div>
        <div class="card-body">
            {{-- // STAFF MANAGEMENT --}}
            <form action="{{ route('staff-management.update', $staff) }}" method="POST" data-loading-text="Saving...">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $staff->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $staff->email) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('staff-management.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4" data-loading-text="Saving...">Update Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
