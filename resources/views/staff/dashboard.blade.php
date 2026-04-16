@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Staff Dashboard</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <h5>My Bill Entries</h5>
                        <p class="display-6">45</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-secondary text-white mb-4">
                    <div class="card-body">
                        <h5>Items in Possession</h5>
                        <p class="display-6">5</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-dark text-white">
                Staff Operations
            </div>
            <div class="card-body">
                <p>Welcome to the staff portal. You can manage day-to-day billing and check stock availability here.</p>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action">New Billing Entry</a>
                    <a href="#" class="list-group-item list-group-item-action">Check Stock Availability</a>
                    <a href="#" class="list-group-item list-group-item-action">View My Sales</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
