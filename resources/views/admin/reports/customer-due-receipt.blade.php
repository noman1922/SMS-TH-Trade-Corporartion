<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Statement - {{ $customer->customer_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* // CUSTOMER MODULE IMPROVEMENT */
        /* // CUSTOMER LEDGER SYSTEM */
        body {
            background: #d9d9d9;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        .print-actions {
            text-align: center;
            margin: 18px 0;
        }

        .statement-page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px;
            padding: 14mm 12mm;
            box-shadow: 0 3px 18px rgba(0, 0, 0, 0.18);
        }

        .statement-header {
            border-bottom: 2px solid #111;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .statement-title {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: right;
        }

        .info-box {
            border: 1px solid #111;
            margin-bottom: 10px;
        }

        .info-box-title {
            background: #f4f4f4;
            border-bottom: 1px solid #111;
            padding: 4px 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .info-panel {
            padding: 7px;
        }

        .info-panel + .info-panel {
            border-left: 1px solid #111;
        }

        .line {
            display: grid;
            grid-template-columns: 95px 1fr;
            gap: 6px;
            margin-bottom: 3px;
        }

        .label {
            font-weight: 700;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }

        .summary-card {
            border: 1px solid #111;
            padding: 6px;
        }

        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #333;
            font-weight: 700;
        }

        .summary-value {
            font-size: 13px;
            font-weight: 700;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .statement-table {
            margin-bottom: 10px;
            border: 1px solid #111;
        }

        .statement-table th,
        .statement-table td {
            border: 1px solid #666;
            padding: 4px 5px;
            font-size: 10px;
            vertical-align: top;
        }

        .statement-table th {
            background: #f4f4f4;
            font-weight: 700;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 10px 0 4px;
        }

        .signature-area {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40mm;
            margin-top: 24mm;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-line {
            border-top: 1px solid #111;
            padding-top: 5px;
            text-align: center;
            font-weight: 700;
        }

        .text-right {
            text-align: right;
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: #fff;
                margin: 0;
            }

            .print-actions {
                display: none !important;
            }

            .statement-page {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                padding: 12mm;
                box-shadow: none;
            }

            thead {
                display: table-header-group;
            }

            tr,
            .info-box,
            .summary-grid,
            .signature-area {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    @php
        // CUSTOMER LEDGER SYSTEM
        $openingBalance = round((float) $customer->previous_due, 2);
        $closingBalance = round((float) $customer->current_due, 2);
        $totalPurchased = round((float) $customer->total_purchased, 2);
        $totalPaid = round((float) $customer->total_paid, 2);
    @endphp

    <div class="print-actions">
        <button onclick="handlePrintClick(this)" class="btn btn-dark px-4">Print Statement</button>
    </div>

    <div class="statement-page">
        <div class="statement-header">
            <div class="row">
                <div class="col-7">
                    <div class="company-name">TH Trade Corporation</div>
                    <div>Customer Accounting Statement</div>
                </div>
                <div class="col-5">
                    <div class="statement-title">Customer Statement</div>
                    <div class="text-end">
                        <strong>Statement No:</strong> STMT-{{ $customer->customer_id }}<br>
                        <strong>Date:</strong> {{ now()->format('d M, Y h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="info-box">
            <div class="info-box-title">Customer Information</div>
            <div class="info-grid">
                <div class="info-panel">
                    <div class="line"><div class="label">Customer ID</div><div>{{ $customer->customer_id }}</div></div>
                    <div class="line"><div class="label">Organization</div><div><strong>{{ $customer->hospital_name }}</strong></div></div>
                    <div class="line"><div class="label">Customer Name</div><div>{{ $customer->customer_name ?: 'N/A' }}</div></div>
                </div>
                <div class="info-panel">
                    <div class="line"><div class="label">Mobile</div><div>{{ $customer->mobile }}</div></div>
                    <div class="line"><div class="label">Address</div><div>{{ $customer->address }}</div></div>
                    <div class="line"><div class="label">Printed By</div><div>{{ auth()->user()->name }}</div></div>
                </div>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Opening Balance</div>
                <div class="summary-value">Tk. {{ number_format($openingBalance, 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Invoices</div>
                <div class="summary-value">Tk. {{ number_format($totalPurchased, 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Payments</div>
                <div class="summary-value">Tk. {{ number_format($totalPaid, 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Closing Due</div>
                <div class="summary-value">Tk. {{ number_format($closingBalance, 2) }}</div>
            </div>
        </div>

        <div class="section-title">Invoice History</div>
        <table class="statement-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice No</th>
                    <th class="text-right">Sales Amount</th>
                    <th class="text-right">Paid Amount</th>
                    <th class="text-right">Due Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->invoices as $invoice)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M, Y') }}</td>
                        <td>{{ $invoice->invoice_no }}</td>
                        <td class="text-right">Tk. {{ number_format($invoice->net_payable, 2) }}</td>
                        <td class="text-right">Tk. {{ number_format($invoice->received_amount, 2) }}</td>
                        <td class="text-right">Tk. {{ number_format($invoice->due_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No invoices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Receipt / Payment History</div>
        <table class="statement-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Receipt</th>
                    <th>Invoice No</th>
                    <th>Payment Method</th>
                    <th>Type</th>
                    <th class="text-right">Previous Due</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Remaining Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->payments as $payment)
                    @php
                        // DUE COLLECTION IMPROVEMENT
                        // DUE HISTORY SYSTEM
                        $allocatedInvoices = $payment->allocations->pluck('invoice.invoice_no')->filter()->values();
                        $invoiceLabel = optional($payment->invoice)->invoice_no
                            ?: ($allocatedInvoices->isNotEmpty() ? $allocatedInvoices->join(', ') : 'Full customer due');
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}</td>
                        <td>REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $invoiceLabel }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>Due Collection</td>
                        <td class="text-right">Tk. {{ number_format($payment->previous_due ?? 0, 2) }}</td>
                        <td class="text-right">Tk. {{ number_format($payment->amount, 2) }}</td>
                        <td class="text-right">Tk. {{ number_format($payment->remaining_due ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="statement-table">
            <tbody>
                <tr>
                    <td><strong>Opening Balance</strong></td>
                    <td class="text-right">Tk. {{ number_format($openingBalance, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Add: Total Sales</strong></td>
                    <td class="text-right">Tk. {{ number_format($totalPurchased, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Less: Total Payments / Collections</strong></td>
                    <td class="text-right">Tk. {{ number_format($totalPaid, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Current Due / Closing Balance</strong></td>
                    <td class="text-right"><strong>Tk. {{ number_format($closingBalance, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="signature-area">
            <div class="signature-line">Customer Signature</div>
            <div class="signature-line">Authorized Signature</div>
        </div>
    </div>

    <script>
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
