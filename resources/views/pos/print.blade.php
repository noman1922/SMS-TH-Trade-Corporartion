<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #e2e8f0;
            font-family: 'Inter', sans-serif;
            color: #111;
        }

        .invoice-page {
            background: #fff;
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 28px 36px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
            position: relative;
            /* // FOOTER POSITION FIX */
            /* // INVOICE FOOTER FIX */
            display: flex;
            flex-direction: column;
        }

        .invoice-content {
            /* // FOOTER POSITION FIX */
            /* // INVOICE FOOTER FIX */
            flex: 0 0 auto;
        }

        /* Header */
        .invoice-header {
            /* // PRINT LAYOUT FIX */
            border-bottom: 2px solid #111;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }

        .company-name {
            /* // PROFESSIONAL INVOICE STYLE */
            font-size: 1.45rem;
            font-weight: 700;
            color: #111;
            letter-spacing: 1px;
        }

        .company-tagline {
            color: #333;
            font-size: 0.78rem;
            margin-top: 2px;
        }

        .company-contact {
            font-size: 0.72rem;
            color: #333;
            line-height: 1.35;
        }

        .invoice-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .invoice-meta {
            font-size: 0.78rem;
            color: #333;
        }

        .invoice-meta strong {
            color: #111;
        }

        /* Billing Section */
        .billing-section {
            /* // PROFESSIONAL INVOICE STYLE */
            background: #fff;
            border: 0;
            border-radius: 0;
            padding: 0;
            margin-bottom: 16px;
        }

        .billing-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #333;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .billing-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: #111;
        }

        .billing-detail {
            font-size: 0.78rem;
            color: #333;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            /* // PRINT TABLE BORDER FIX */
            margin-bottom: 14px;
            border: 1px solid #777;
        }

        .items-table thead th {
            background: #fff;
            color: #111;
            padding: 7px 8px;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            border: 1px solid #555;
        }

        .items-table thead th:first-child {
            border-radius: 0;
        }

        .items-table thead th:last-child {
            border-radius: 0;
        }

        .items-table tbody td {
            padding: 6px 8px;
            /* // PRINT TABLE BORDER FIX */
            border: 1px solid #bbb;
            font-size: 0.76rem;
        }

        .items-table tbody tr:nth-child(even) {
            background: #fff;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 1px solid #777;
        }

        /* Totals */
        .totals-section {
            margin-left: auto;
            width: 300px;
        }

        .totals-table {
            width: 100%;
        }

        .totals-table td {
            padding: 3px 0;
            font-size: 0.78rem;
            color: #222;
            font-weight: 400;
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: 400;
            color: #111;
        }

        .totals-table .grand-total td {
            border-top: 1px solid #111;
            font-size: 0.78rem;
            font-weight: 400;
            padding-top: 5px;
            color: #111;
        }

        .totals-table .paid td {
            color: #111;
        }

        .totals-table .due td {
            color: #111;
            font-weight: 700;
        }

        .totals-table .total-payable td,
        .totals-table .final-due td {
            font-size: 0.95rem;
            font-weight: 700;
            color: #111;
        }

        /* Footer */
        .invoice-footer {
            /* // PRINT LAYOUT FIX */
            /* // FOOTER POSITION FIX */
            position: relative;
            bottom: auto;
            left: auto;
            right: auto;
            margin-top: auto;
            padding-top: 38px;
            flex-shrink: 0;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-line {
            border-top: 1px solid #111;
            width: 180px;
            padding-top: 5px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #111;
        }

        .footer-note {
            border-top: 1px solid #ddd;
            padding-top: 8px;
            margin-top: 22px;
            text-align: center;
            color: #333;
            font-size: 0.68rem;
        }

        /* Print Actions */
        .print-actions {
            text-align: center;
            margin: 20px 0;
        }

        @media print {
            /* // PRINT LAYOUT FIX */
            @page {
                size: A4;
                margin: 10mm;
            }

            body {
                background: #fff;
                margin: 0;
                padding: 0;
                color: #111;
            }

            .invoice-page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: 100%;
                min-height: calc(297mm - 20mm);
            }

            .invoice-content {
                flex: 0 0 auto;
            }

            .print-actions {
                display: none !important;
            }

            .page-break {
                page-break-before: avoid;
            }

            .invoice-header,
            .billing-section,
            .totals-section,
            .invoice-footer {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            thead {
                display: table-header-group;
            }

            .invoice-footer {
                /* // FOOTER POSITION FIX */
                margin-top: auto;
                padding-top: 28px;
            }

            * {
                color: #111 !important;
                background: #fff !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="print-actions">
        {{-- // LOADING STATE FIX --}}
        <button onclick="handlePrintClick(this)" class="btn btn-primary btn-lg px-5 shadow">
            <i class="bi bi-printer me-2"></i> Print Invoice
        </button>
        <a href="{{ route('pos.index') }}" class="btn btn-secondary btn-lg px-4 shadow ms-2">Back to POS</a>
    </div>

    <div class="invoice-page">
        <div class="invoice-content">
        <!-- Header -->
        <div class="invoice-header">
            <div class="row align-items-start">
                <div class="col-7">
                    <div class="company-name">TH TRADE CORPORATION</div>
                    <div class="company-tagline">Specialized Medical Equipment Supplier</div>
                    <div class="company-contact mt-2">
                        Dhaka, Bangladesh<br>
                        Phone: +880-XXXX-XXXXXX<br>
                        Email: info@thtradecorp.com
                    </div>
                </div>
                <div class="col-5 text-end">
                    <div class="invoice-title">Invoice</div>
                    <div class="invoice-meta mt-2">
                        <strong>Invoice #:</strong> {{ $invoice->invoice_no }}<br>
                        <strong>Date:</strong> {{ date('d M, Y', strtotime($invoice->date)) }}<br>
                        <strong>Prepared by:</strong> {{ $invoice->user->name }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Section -->
        <div class="billing-section">
            <div class="row">
                <div class="col-6">
                    <div class="billing-label">Bill To</div>
                    <div class="billing-name">{{ $invoice->customer->customer_name }}</div>
                    @if($invoice->customer->hospital_name)
                        <div class="billing-detail">{{ $invoice->customer->hospital_name }}</div>
                    @endif
                    <div class="billing-detail">{{ $invoice->customer->mobile }}</div>
                    <div class="billing-detail">{{ $invoice->customer->address }}</div>
                </div>
                <div class="col-6 text-end">
                    <div class="billing-label">Payment Status</div>
                    @if($invoice->due_amount <= 0)
                        {{-- // PROFESSIONAL INVOICE STYLE --}}
                        <span style="color: #111; font-weight: 600; font-size: 0.9rem;">PAID</span>
                    @elseif($invoice->received_amount > 0)
                        <span style="color: #111; font-weight: 600; font-size: 0.9rem;">PARTIAL</span>
                    @else
                        <span style="color: #111; font-weight: 600; font-size: 0.9rem;">UNPAID</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="50">SL</th>
                    <th>Product Description</th>
                    <th class="text-center" width="80">Qty</th>
                    <th class="text-end" width="130">Unit Price</th>
                    <th class="text-end" width="130">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $item->product->product_name }}
                            @if($item->product->model_no)
                                <br><small style="color: #333;">Model: {{ $item->product->model_no }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">৳ {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">৳ {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td>৳ {{ number_format($invoice->sub_total, 2) }}</td>
                </tr>
                @if($invoice->discount_percent > 0)
                    <tr>
                        <td>Discount ({{ $invoice->discount_percent }}%):</td>
                        <td>- ৳
                            {{ number_format(($invoice->sub_total * $invoice->discount_percent) / 100, 2) }}
                        </td>
                    </tr>
                @endif
                @if($invoice->vat_percent > 0)
                    <tr>
                        <td>VAT ({{ $invoice->vat_percent }}%):</td>
                        <td>+ ৳ {{ number_format(($invoice->sub_total * $invoice->vat_percent) / 100, 2) }}</td>
                    </tr>
                @endif
                @if($invoice->ait_percent > 0)
                    <tr>
                        <td>AIT ({{ $invoice->ait_percent }}%):</td>
                        <td>+ ৳ {{ number_format(($invoice->sub_total * $invoice->ait_percent) / 100, 2) }}</td>
                    </tr>
                @endif
                @if($invoice->extra_charge > 0)
                    <tr>
                        <td>Extra Charge:</td>
                        <td>+ ৳ {{ number_format($invoice->extra_charge, 2) }}</td>
                    </tr>
                @endif
                @php
                    // FINANCIAL CALCULATION FIX
                    // CRITICAL ACCOUNTING FIX
                    // DUE CALCULATION FIX
                    // INVOICE PRINT DATA FIX
                    $current_invoice = round((float) $invoice->net_payable, 2);
                    $paid_amount = round((float) $invoice->received_amount, 2);
                    $final_due = round(max(0, (float) $invoice->due_amount), 2);
                    $total_payable = round($paid_amount + $final_due, 2);
                    $previous_due = round(max(0, $total_payable - $current_invoice), 2);
                @endphp
                <tr class="grand-total">
                    <td>Current Invoice:</td>
                    <td>৳ {{ number_format($current_invoice, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-top: 5px;">Previous Due:</td>
                    <td style="padding-top: 5px;">+ ৳
                        {{ number_format($previous_due, 2) }}
                    </td>
                </tr>
                <tr class="total-payable">
                    <td
                        style="padding-top: 6px; border-top: 1px solid #111;">
                        Total Payable:</td>
                    <td
                        style="padding-top: 6px; border-top: 1px solid #111;">
                        ৳ {{ number_format($total_payable, 2) }}</td>
                </tr>
                <tr class="paid">
                    <td style="padding-top: 5px;">Paid Amount:</td>
                    <td style="padding-top: 5px;">- ৳ {{ number_format($paid_amount, 2) }}</td>
                </tr>
                <tr class="final-due">
                    <td style="padding-top: 7px;">Final Due:</td>
                    <td style="padding-top: 7px;">৳ {{ number_format($final_due, 2) }}</td>
                </tr>
            </table>
        </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted">Issued by: {{ $invoice->user->name }}</small>
                </div>
                <div class="col-6 text-end">
                    <div class="signature-line d-inline-block text-center">
                        Authorized Signature
                    </div>
                </div>
            </div>
            <div class="footer-note">
                <p class="mb-1">Thank you for your business!</p>
                <p class="mb-0">This is a computer-generated invoice. No signature is required for amounts below
                    ৳10,000.</p>
            </div>
        </div>
    </div>

    <script>
        // RESPONSIVENESS ROLLBACK
        // LOADING STATE FIX
        function handlePrintClick(button) {
            const original = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Processing...';
            window.setTimeout(function() {
                window.print();
                button.disabled = false;
                button.innerHTML = original;
            }, 120);
        }
    </script>
</body>

</html>
