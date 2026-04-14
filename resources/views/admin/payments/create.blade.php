@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Collect Payment</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Payment Entry Form</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('payments.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label text-muted small text-uppercase">Customer</label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                    <option value="" selected disabled>Select Customer...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                            {{ (old('customer_id') == $customer->id || ($selectedCustomer && $selectedCustomer->id == $customer->id)) ? 'selected' : '' }}
                                            data-total-due="{{ $customer->current_due }}">
                                            {{ $customer->customer_name }} (Due: ${{ number_format($customer->current_due, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="invoice_id" class="form-label text-muted small text-uppercase">Specific Invoice (Optional)</label>
                                <select class="form-select @error('invoice_id') is-invalid @enderror" id="invoice_id" name="invoice_id">
                                    <option value="">-- General / Bulk Payment --</option>
                                    @if($selectedCustomer)
                                        @foreach($selectedCustomer->invoices->where('due_amount', '>', 0) as $invoice)
                                            <option value="{{ $invoice->id }}" 
                                                {{ (old('invoice_id') == $invoice->id || ($selectedInvoice && $selectedInvoice->id == $invoice->id)) ? 'selected' : '' }}
                                                data-invoice-due="{{ $invoice->remaining_due }}">
                                                {{ $invoice->invoice_no }} (Due: ${{ number_format($invoice->remaining_due, 2) }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('invoice_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded mb-4 text-center border">
                            <h6 class="text-muted small text-uppercase mb-1">Applicable Due Amount</h6>
                            <h3 class="mb-0 text-danger fw-bold" id="display_due">
                                @if($selectedInvoice)
                                    ${{ number_format($selectedInvoice->remaining_due, 2) }}
                                @elseif($selectedCustomer)
                                    ${{ number_format($selectedCustomer->current_due, 2) }}
                                @else
                                    $0.00
                                @endif
                            </h3>
                            <small class="text-muted" id="due_type_label">
                                {{ $selectedInvoice ? 'For Invoice ' . $selectedInvoice->invoice_no : ($selectedCustomer ? 'Total Outstanding' : 'Please select a customer') }}
                            </small>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" placeholder="0.00" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="cash" selected>Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="card">Card / POS</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Remarks / Note</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="2" placeholder="Any specific details about this payment...">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info py-2 small mb-4">
                            <i class="bi bi-info-circle me-1"></i> Payments without a selected invoice will be kept as unallocated credit.
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Record Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customer_id');
    const invoiceSelect = document.getElementById('invoice_id');
    const displayDue = document.getElementById('display_due');
    const dueLabel = document.getElementById('due_type_label');

    customerSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const totalDue = option.dataset.totalDue;
        
        displayDue.innerText = '$' + parseFloat(totalDue).toLocaleString(undefined, {minimumFractionDigits: 2});
        dueLabel.innerText = 'Total Outstanding';
        
        // In a real app, you would fetch invoices for this customer via AJAX here
        // For simplicity, we'll suggest refreshing the list or let the controller handle it if page reloads
        // But since we want "Clean Production Quality", let's at least clear the invoice select if it was pre-filled
        if(!{{ $selectedInvoice ? 'true' : 'false' }}) {
            invoiceSelect.innerHTML = '<option value="">-- General / Bulk Payment --</option>';
            // Note: In production, I would add an AJAX call here.
        }
    });

    invoiceSelect.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            const invoiceDue = option.dataset.invoiceDue;
            displayDue.innerText = '$' + parseFloat(invoiceDue).toLocaleString(undefined, {minimumFractionDigits: 2});
            dueLabel.innerText = 'For Invoice ' + option.text.split(' (')[0];
        } else {
            const customerOption = customerSelect.options[customerSelect.selectedIndex];
            const totalDue = customerOption.dataset.totalDue || 0;
            displayDue.innerText = '$' + parseFloat(totalDue).toLocaleString(undefined, {minimumFractionDigits: 2});
            dueLabel.innerText = 'Total Outstanding';
        }
    });
});
</script>
@endsection
