@extends('layouts.admin')

@section('content')
<div class="container-fluid d-print-block">
    <div class="row mb-4 d-print-none">
        <div class="col-md-6">
            <h3 class="fw-bold">Payment Receipt</h3>
        </div>
        <div class="col-md-6 text-end">
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="bi bi-printer me-1"></i> Print Receipt
            </button>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                Back to List
            </a>
        </div>
    </div>

    <div class="card shadow-sm mx-auto" style="max-width: 800px; border: 1px solid #ddd;">
        <div class="card-body p-5">
            <!-- Header -->
            <div class="row mb-5 justify-content-between align-items-center">
                <div class="col-md-6">
                    <h2 class="fw-bold text-primary mb-0">TH Trade Corporation</h2>
                    <p class="text-muted mb-0">Specialized Medical Equipment Supplier</p>
                </div>
                <div class="col-md-5 text-end">
                    <h4 class="fw-bold text-uppercase mb-1">Money Receipt</h4>
                    <p class="mb-0">Receipt #: <strong>REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</strong></p>
                    <p class="mb-0">Date: <strong>{{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}</strong></p>
                </div>
            </div>

            <hr class="mb-5">

            <!-- Body -->
            <div class="row mb-4">
                <div class="col-12">
                    <p class="fs-5">Received with thanks from <strong>{{ $payment->customer->customer_name }}</strong></p>
                    <p class="fs-5">the sum of <strong>${{ number_format($payment->amount, 2) }}</strong></p>
                    
                    <div class="bg-light p-3 border rounded my-4">
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1 text-muted small text-uppercase">Payment For</p>
                                <p class="mb-0 fw-bold">
                                    @if($payment->invoice_id)
                                        Invoice #{{ $payment->invoice->invoice_no }}
                                    @else
                                        Advance / Bulk Payment
                                    @endif
                                </p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1 text-muted small text-uppercase">Payment Method</p>
                                <p class="mb-0 fw-bold text-uppercase">{{ $payment->payment_method }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($payment->invoice_id)
            <div class="row mb-5">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Invoice Total:</td>
                            <td class="text-end fw-bold">${{ number_format($payment->invoice->net_payable, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Amount Paid Now:</td>
                            <td class="text-end fw-bold text-success">-${{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="text-muted">Remaining Balance:</td>
                            <td class="text-end fw-bold text-danger">${{ number_format($payment->invoice->remaining_due, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            <div class="row mt-5 pt-5">
                <div class="col-md-6">
                    <p class="mb-0 text-muted small">Issued by: {{ $payment->user->name }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <div style="border-top: 2px solid #333; display: inline-block; width: 200px; padding-top: 5px;" class="fw-bold">
                        Authorized Signature
                    </div>
                </div>
            </div>

            @if($payment->note)
                <div class="mt-5 pt-3 border-top">
                    <p class="text-muted small"><strong>Note:</strong> {{ $payment->note }}</p>
                </div>
            @endif
        </div>
    </div>
    
    <div class="text-center mt-4 d-print-none">
        <p class="text-muted small">Thank you for your business!</p>
    </div>
</div>

<style>
@media print {
    .btn, .d-print-none, .sidebar, .main-header {
        display: none !important;
    }
    body {
        background-color: white !important;
        margin: 0;
        padding: 0;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
}
</style>
@endsection
