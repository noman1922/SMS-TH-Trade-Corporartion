@extends('layouts.admin')

@section('content')
<div class="row">
    <!-- POS Section -->
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice Generation</h5>
                <span class="badge bg-light text-dark"># {{ $invoice_no }}</span>
            </div>
            <div class="card-body">
                <!-- Customer Selection -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Select Customer</label>
                        <select id="customer_select" class="form-select">
                            <option value="">-- Choose Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-mobile="{{ $customer->mobile }}" data-address="{{ $customer->address }}" data-due="{{ $customer->current_due }}">{{ $customer->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Customer Details</label>
                        <div id="customer_details" class="small text-muted">
                            Select a customer to see details...
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Product Selection -->
                <div class="row mb-3 bg-light p-3 border rounded">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Search Product</label>
                        <select id="product_select" class="form-select">
                            <option value="">-- Search Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">[{{ $product->product_id }}] {{ $product->product_name }} (Stock: {{ $product->stock_quantity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Quantity</label>
                        <input type="number" id="product_qty" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="add_item_btn" class="btn btn-primary w-100" data-loading-text="Adding...">Add Item</button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mt-3" id="invoice_table">
                        <thead class="table-dark">
                            <tr>
                                <th>SL</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Items will be added here via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Summary Section -->
    <div class="col-md-4">
        <div class="card shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Billing Summary</h5>
            </div>
            <div class="card-body bg-light">
                <div class="mb-2 d-flex justify-content-between">
                    <span>Subtotal:</span>
                    <strong id="summary_subtotal">৳ 0.00</strong>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Discount (%)</label>
                    {{-- // POS INPUT UX FIX --}}
                    <input type="number" id="discount_percent" class="form-control form-control-sm" min="0" max="50">
                </div>
                <div class="mb-3">
                    <label class="form-label small">VAT (%)</label>
                    {{-- // POS INPUT UX FIX --}}
                    <input type="number" id="vat_percent" class="form-control form-control-sm" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label small">AIT (%)</label>
                    {{-- // POS INPUT UX FIX --}}
                    <input type="number" id="ait_percent" class="form-control form-control-sm" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Extra Charge (৳)</label>
                    {{-- // POS INPUT UX FIX --}}
                    <input type="number" id="extra_charge" class="form-control form-control-sm" min="0">
                </div>
                <hr>
                <div class="mb-3 d-flex justify-content-between text-secondary fw-bold">
                    <span>Current Bill:</span>
                    <strong id="summary_current_bill">৳ 0.00</strong>
                </div>
                
                <div class="mb-3 d-flex justify-content-between text-warning fw-bold small border p-2 rounded bg-white">
                    <span>Previous Due:</span>
                    <strong id="summary_prev_due">৳ 0.00</strong>
                </div>
                
                <div class="h4 mb-3 d-flex justify-content-between text-primary border-top pt-2">
                    <span>Total Payable:</span>
                    <strong id="summary_total_payable">৳ 0.00</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label text-success fw-bold">Received Amount (৳)</label>
                    <input type="number" id="received_amount" class="form-control border-success" value="0" min="0">
                </div>
                <div class="mb-4 d-flex justify-content-between text-danger h5">
                    <span>Due:</span>
                    <strong id="summary_due">৳ 0.00</strong>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" id="save_invoice_btn" class="btn btn-success btn-lg" data-loading-text="Processing...">Save Invoice</button>
                    <button type="button" id="clear_all_btn" class="btn btn-outline-danger shadow-sm">Clear All</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        let items = [];

        // Customer Info Update
        $('#customer_select').change(function() {
            let option = $(this).find('option:selected');
            if (option.val()) {
                $('#customer_details').html(`
                    <strong>Mobile:</strong> ${option.data('mobile')}<br>
                    <strong>Address:</strong> ${option.data('address')}
                `);
                let due = parseFloat(option.data('due')) || 0;
                $('#summary_prev_due').text(`৳ ${due.toFixed(2)}`);
            } else {
                $('#customer_details').text('Select a customer to see details...');
                $('#summary_prev_due').text(`৳ 0.00`);
            }
            
            // Trigger recalculation of totals
            let subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            updateSummary(subtotal);
        });

        // Add Item
        $('#add_item_btn').click(function() {
            // LOADING STATE FIX
            const addButton = this;
            let productId = $('#product_select').val();
            let qty = parseInt($('#product_qty').val());

            if (!productId || qty < 1) {
                Swal.fire('Error', 'Please select a product and valid quantity', 'error');
                return;
            }

            window.THTradeUX.setButtonLoading(addButton, 'Adding...');

            // Check if already in list
            let existing = items.find(i => i.product_id == productId);
            if (existing) {
                existing.quantity += qty;
                renderTable();
                window.THTradeUX.resetButton(addButton);
                return;
            }

            // Fetch details from server (Security: get real price from backend)
            $.get(`{{ url('/pos/product') }}/${productId}`, function(product) {
                if (product.stock_quantity < qty) {
                    Swal.fire('Low Stock', `Only ${product.stock_quantity} items available`, 'warning');
                    return;
                }

                items.push({
                    product_id: product.id,
                    name: product.product_name,
                    quantity: qty,
                    price: product.selling_price,
                    total: product.selling_price * qty
                });
                renderTable();
            }).fail(function() {
                Swal.fire('Error', 'Could not load product details. Please try again.', 'error');
            }).always(function() {
                window.THTradeUX.resetButton(addButton);
            });
        });

        // Remove Item
        $(document).on('click', '.remove-item', function() {
            let index = $(this).data('index');
            items.splice(index, 1);
            renderTable();
        });

        // Render Table
        function renderTable() {
            let html = '';
            let subtotal = 0;
            items.forEach((item, index) => {
                let total = item.price * item.quantity;
                subtotal += total;
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>৳ ${parseFloat(item.price).toFixed(2)}</td>
                        <td>৳ ${total.toFixed(2)}</td>
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm remove-item" data-index="${index}">×</button>
                        </td>
                    </tr>
                `;
            });
            $('#invoice_table tbody').html(html);
            updateSummary(subtotal);
        }

        // Summary Calculations
        function updateSummary(subtotal) {
            // POS INPUT UX FIX
            // FINANCIAL CALCULATION FIX
            let discP = parseFloat($('#discount_percent').val() || 0) || 0;
            let vatP = parseFloat($('#vat_percent').val() || 0) || 0;
            let aitP = parseFloat($('#ait_percent').val() || 0) || 0;
            let extra = parseFloat($('#extra_charge').val() || 0) || 0;
            let received = parseFloat($('#received_amount').val() || 0) || 0;

            let prevDue = 0;
            let selectedOption = $('#customer_select').find('option:selected');
            if (selectedOption.val()) {
                prevDue = parseFloat(selectedOption.data('due') || 0) || 0;
            }

            let discount = parseFloat(((subtotal * discP) / 100).toFixed(2));
            let vat = parseFloat(((subtotal * vatP) / 100).toFixed(2));
            let ait = parseFloat(((subtotal * aitP) / 100).toFixed(2));

            let currentBill = parseFloat(((subtotal - discount) + vat + ait + extra).toFixed(2));
            let totalPayable = parseFloat((currentBill + prevDue).toFixed(2));
            
            let due = parseFloat(Math.max(0, totalPayable - received).toFixed(2));

            $('#summary_subtotal').text(`৳ ${subtotal.toFixed(2)}`);
            $('#summary_current_bill').text(`৳ ${currentBill.toFixed(2)}`);
            $('#summary_total_payable').text(`৳ ${totalPayable.toFixed(2)}`);
            $('#summary_due').text(`৳ ${due.toFixed(2)}`);
        }

        // Listen for summary changes
        $('#discount_percent, #vat_percent, #ait_percent, #extra_charge, #received_amount').on('input', function() {
            let subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            updateSummary(subtotal);
        });

        // Clear All
        $('#clear_all_btn').click(function() {
            items = [];
            renderTable();
        });

        // Save Invoice
        $('#save_invoice_btn').click(function() {
            // LOADING STATE FIX
            const saveButton = this;
            if (saveButton.disabled) {
                return;
            }

            if (items.length === 0) {
                Swal.fire('Error', 'Please add at least one product', 'error');
                return;
            }

            let customerId = $('#customer_select').val();
            if (!customerId) {
                Swal.fire('Error', 'Please select a customer', 'error');
                return;
            }

            // RESPONSIVENESS ROLLBACK
            // KEEP SIMPLE BUTTON LOADER
            window.THTradeUX.setButtonLoading(saveButton, 'Saving...');

            let data = {
                _token: '{{ csrf_token() }}',
                customer_id: customerId,
                items: items,
                discount_percent: $('#discount_percent').val(),
                vat_percent: $('#vat_percent').val(),
                ait_percent: $('#ait_percent').val(),
                extra_charge: $('#extra_charge').val(),
                received_amount: $('#received_amount').val(),
                date: new Date().toISOString().slice(0, 10)
            };

            $.ajax({
                url: '{{ route("pos.store") }}',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function(response) {
                    // TOAST NOTIFICATIONS
                    window.THTradeUX.toast('Invoice Saved Successfully', 'success');
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Print Invoice',
                        cancelButtonText: 'Done'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(`{{ url('/pos/print') }}/${response.invoice_id}`, '_blank');
                        }
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    window.THTradeUX.toast(msg, 'error');
                    Swal.fire('Error', msg, 'error');
                },
                complete: function() {
                    window.THTradeUX.resetButton(saveButton);
                }
            });
        });
    });
</script>
@endsection
