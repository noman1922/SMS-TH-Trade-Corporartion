@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Collect Due Payment</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Payment Entry</h5>
                </div>
                <div class="card-body">
                    {{-- // DUE COLLECTION IMPROVEMENT --}}
                    <form action="{{ route('payments.store') }}" method="POST" data-loading-text="Processing...">
                        @csrf

                        <div class="mb-3">
                            <label for="customer_id" class="form-label text-muted small text-uppercase">Customer</label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                <option value="" selected disabled>Select Customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ (old('customer_id') == $customer->id || ($selectedCustomer && $selectedCustomer->id == $customer->id)) ? 'selected' : '' }}
                                        data-total-due="{{ $customer->current_due }}">
                                        {{ $customer->customer_id }} - {{ $customer->hospital_name }} @if($customer->customer_name) ({{ $customer->customer_name }}) @endif (Due: Tk. {{ number_format($customer->current_due, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 position-relative">
                            <label for="invoice_search" class="form-label text-muted small text-uppercase">Search Invoice No</label>
                            {{-- // PAYMENT FLOW IMPROVEMENT --}}
                            {{-- // INVOICE PAYMENT SYSTEM --}}
                            <input type="text" class="form-control" id="invoice_search" name="invoice_no" value="{{ old('invoice_no', $selectedInvoice->invoice_no ?? '') }}" autocomplete="off" placeholder="Type invoice number to collect payment directly">
                            <div id="invoice_search_results" class="list-group position-absolute start-0 end-0 d-none" style="z-index: 1050; max-height: 260px; overflow-y: auto;"></div>
                            <div class="form-text">Selecting an invoice will also select its customer automatically.</div>
                        </div>

                        <div class="mb-3">
                            <label for="invoice_id" class="form-label text-muted small text-uppercase">Payment Scope</label>
                            {{-- // INVOICE PAYMENT FLOW --}}
                            <select class="form-select @error('invoice_id') is-invalid @enderror" id="invoice_id" name="invoice_id">
                                <option value="">Full Customer Due Payment</option>
                                @if($selectedCustomer)
                                    @foreach($selectedCustomer->invoices->where('due_amount', '>', 0) as $invoice)
                                        <option value="{{ $invoice->id }}"
                                            {{ (old('invoice_id') == $invoice->id || ($selectedInvoice && $selectedInvoice->id == $invoice->id)) ? 'selected' : '' }}
                                            data-invoice-due="{{ $invoice->remaining_due }}">
                                            {{ $invoice->invoice_no }} (Due: Tk. {{ number_format($invoice->remaining_due, 2) }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('invoice_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="bg-light p-3 rounded mb-4 border">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h6 class="text-muted small text-uppercase mb-1">Applicable Due</h6>
                                    <h3 class="mb-0 text-danger fw-bold" id="display_due">
                                        @if($selectedInvoice)
                                            Tk. {{ number_format($selectedInvoice->remaining_due, 2) }}
                                        @elseif($selectedCustomer)
                                            Tk. {{ number_format($selectedCustomer->current_due, 2) }}
                                        @else
                                            Tk. 0.00
                                        @endif
                                    </h3>
                                </div>
                                <small class="text-muted text-end" id="due_type_label">
                                    {{ $selectedInvoice ? 'Specific invoice' : ($selectedCustomer ? 'Full customer due' : 'Select a customer') }}
                                </small>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Paid Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Tk.</span>
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
                            <i class="bi bi-info-circle me-1"></i> Full customer due payments are allocated to oldest outstanding invoices first.
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" data-loading-text="Processing...">Record Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Customer Due History</h5>
                    <span class="badge bg-light text-dark border" id="history_customer_label">
                        {{ $selectedCustomer ? (($selectedCustomer->customer_id ?? '') . ' - ' . $selectedCustomer->hospital_name) : 'No customer selected' }}
                    </span>
                </div>
                <div class="card-body p-0">
                    {{-- // DUE HISTORY SYSTEM --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Invoice No</th>
                                    <th>Date</th>
                                    <th class="text-end">Invoice Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end pe-3">Due</th>
                                </tr>
                            </thead>
                            <tbody id="invoice_history_rows">
                                @forelse(($selectedCustomer->invoices ?? collect()) as $invoice)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $invoice->invoice_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M, Y') }}</td>
                                        <td class="text-end">Tk. {{ number_format($invoice->net_payable, 2) }}</td>
                                        <td class="text-end text-success">Tk. {{ number_format($invoice->received_amount, 2) }}</td>
                                        <td class="text-end pe-3 text-danger fw-bold">Tk. {{ number_format($invoice->remaining_due, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Select a customer to view invoice dues.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Recent Due Collections</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Receipt</th>
                                    <th>Invoice No</th>
                                    <th class="text-end">Previous Due</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end pe-3">Remaining</th>
                                </tr>
                            </thead>
                            <tbody id="payment_history_rows">
                                @forelse(($selectedCustomer->payments ?? collect()) as $payment)
                                    <tr>
                                        <td class="ps-3">{{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}</td>
                                        <td>REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ optional($payment->invoice)->invoice_no ?: 'Full customer due' }}</td>
                                        <td class="text-end">Tk. {{ number_format($payment->previous_due ?? 0, 2) }}</td>
                                        <td class="text-end text-success">Tk. {{ number_format($payment->amount, 2) }}</td>
                                        <td class="text-end pe-3 text-danger">Tk. {{ number_format($payment->remaining_due ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No due collection history found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
    const invoiceRows = document.getElementById('invoice_history_rows');
    const paymentRows = document.getElementById('payment_history_rows');
    const historyLabel = document.getElementById('history_customer_label');
    const invoiceSearch = document.getElementById('invoice_search');
    const invoiceSearchResults = document.getElementById('invoice_search_results');
    let invoiceSearchTimer = null;
    let pendingInvoiceSelection = null;

    function money(value) {
        return 'Tk. ' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(char) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char];
        });
    }

    function setDue(amount, label) {
        displayDue.innerText = money(amount);
        dueLabel.innerText = label;
    }

    function selectCustomer(customerId) {
        customerSelect.value = customerId;
        customerSelect.dispatchEvent(new Event('change'));
    }

    function renderInvoices(invoices, dueInvoices) {
        invoiceSelect.innerHTML = '<option value="">Full Customer Due Payment</option>';

        if (!invoices.length) {
            invoiceRows.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No previous invoice found.</td></tr>';
            return;
        }

        dueInvoices.forEach(function(invoice) {
            invoiceSelect.insertAdjacentHTML('beforeend', `<option value="${invoice.id}" data-invoice-due="${invoice.due_amount}">${escapeHtml(invoice.invoice_no)} (Due: ${money(invoice.due_amount)})</option>`);
        });

        if (pendingInvoiceSelection) {
            invoiceSelect.value = pendingInvoiceSelection.id;
            setDue(pendingInvoiceSelection.due, 'Specific invoice');
            pendingInvoiceSelection = null;
        }

        invoiceRows.innerHTML = invoices.map(function(invoice) {
            const date = invoice.date ? new Date(invoice.date + 'T00:00:00').toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' }) : '';

            return `
                <tr>
                    <td class="ps-3 fw-bold">${escapeHtml(invoice.invoice_no)}</td>
                    <td>${date}</td>
                    <td class="text-end">${money(invoice.net_payable)}</td>
                    <td class="text-end text-success">${money(invoice.received_amount)}</td>
                    <td class="text-end pe-3 text-danger fw-bold">${money(invoice.due_amount)}</td>
                </tr>
            `;
        }).join('');
    }

    function renderPayments(payments) {
        if (!payments.length) {
            paymentRows.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No due collection history found.</td></tr>';
            return;
        }

        paymentRows.innerHTML = payments.map(function(payment) {
            const date = payment.date ? new Date(payment.date + 'T00:00:00').toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' }) : '';
            return `
                <tr>
                    <td class="ps-3">${date}</td>
                    <td>${escapeHtml(payment.receipt_no)}</td>
                    <td>${escapeHtml(payment.invoice_no || 'Full customer due')}</td>
                    <td class="text-end">${money(payment.previous_due)}</td>
                    <td class="text-end text-success">${money(payment.amount)}</td>
                    <td class="text-end pe-3 text-danger">${money(payment.remaining_due)}</td>
                </tr>
            `;
        }).join('');
    }

    customerSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const customerId = this.value;
        const totalDue = Number(option.dataset.totalDue || 0);

        setDue(totalDue, 'Full customer due');
        historyLabel.innerText = option.text.replace(/\s*\(Due:.*\)$/, '');

        fetch(`{{ url('/admin/payments/customer-due-data') }}/${customerId}`)
            .then(response => response.json())
            .then(data => {
                option.dataset.totalDue = data.current_due;
                setDue(data.current_due, 'Full customer due');
                renderInvoices(data.invoices, data.due_invoices);
                renderPayments(data.payments);
            })
            .catch(() => {
                invoiceSelect.innerHTML = '<option value="">Full Customer Due Payment</option>';
                invoiceRows.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Could not load invoice history.</td></tr>';
            });
    });

    // PAYMENT FLOW IMPROVEMENT
    // INVOICE PAYMENT SYSTEM
    invoiceSearch.addEventListener('input', function() {
        clearTimeout(invoiceSearchTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            invoiceSearchResults.classList.add('d-none');
            invoiceSearchResults.innerHTML = '';
            return;
        }

        invoiceSearchTimer = setTimeout(function() {
            fetch(`{{ route('payments.searchInvoices') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.invoices.length) {
                        invoiceSearchResults.innerHTML = '<div class="list-group-item text-muted">No due invoice found</div>';
                        invoiceSearchResults.classList.remove('d-none');
                        return;
                    }

                    invoiceSearchResults.innerHTML = data.invoices.map(function(invoice) {
                        return `
                            <button type="button" class="list-group-item list-group-item-action invoice-search-item"
                                data-invoice-id="${invoice.id}"
                                data-invoice-no="${escapeHtml(invoice.invoice_no)}"
                                data-customer-id="${invoice.customer_id}"
                                data-due="${invoice.due_amount}">
                                <div class="d-flex justify-content-between gap-3">
                                    <strong>${escapeHtml(invoice.invoice_no)}</strong>
                                    <span class="text-danger">${money(invoice.due_amount)}</span>
                                </div>
                                <small class="text-muted">${escapeHtml(invoice.customer_label)}</small>
                            </button>
                        `;
                    }).join('');
                    invoiceSearchResults.classList.remove('d-none');
                });
        }, 250);
    });

    invoiceSearchResults.addEventListener('click', function(event) {
        const item = event.target.closest('.invoice-search-item');
        if (!item) {
            return;
        }

        invoiceSearch.value = item.dataset.invoiceNo;
        pendingInvoiceSelection = {
            id: item.dataset.invoiceId,
            due: item.dataset.due
        };
        selectCustomer(item.dataset.customerId);
        invoiceSearchResults.classList.add('d-none');
    });

    document.addEventListener('click', function(event) {
        if (!event.target.closest('#invoice_search') && !event.target.closest('#invoice_search_results')) {
            invoiceSearchResults.classList.add('d-none');
        }
    });

    invoiceSelect.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            setDue(option.dataset.invoiceDue || 0, 'Specific invoice');
            return;
        }

        const customerOption = customerSelect.options[customerSelect.selectedIndex];
        setDue(customerOption?.dataset?.totalDue || 0, 'Full customer due');
    });
});
</script>
@endsection
