<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $receiptNo ?? 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* // DUE COLLECTION IMPROVEMENT */
        /* // DUE HISTORY SYSTEM */
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #d9d9d9;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }
        .print-actions { margin: 18px 0; text-align: center; }
        .receipt-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px;
            padding: 16mm 13mm;
            background: #fff;
            box-shadow: 0 3px 18px rgba(0, 0, 0, 0.18);
        }
        .receipt-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            align-items: end;
            border-bottom: 2px solid #111;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .company-name { font-size: 18px; font-weight: 700; text-transform: uppercase; }
        .receipt-title { font-size: 16px; font-weight: 700; text-transform: uppercase; text-align: right; letter-spacing: 1px; }
        .meta-table, .summary-table, .history-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 2px 0 2px 8px; text-align: right; }
        .meta-table td:first-child { font-weight: 700; color: #333; }
        .info-box, .summary-box { border: 1px solid #111; margin-bottom: 9px; }
        .box-title {
            background: #f4f4f4;
            border-bottom: 1px solid #111;
            padding: 4px 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
        }
        .info-panel { padding: 7px; }
        .info-panel + .info-panel { border-left: 1px solid #111; }
        .line { display: grid; grid-template-columns: 92px 1fr; gap: 6px; margin-bottom: 3px; }
        .label { font-weight: 700; }
        .amount-strip {
            border: 2px solid #111;
            padding: 8px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 9px;
        }
        .amount-strip .amount { font-size: 20px; font-weight: 700; }
        .summary-table td, .history-table th, .history-table td {
            border: 1px solid #777;
            padding: 4px 6px;
            vertical-align: top;
        }
        .summary-table td:first-child { font-weight: 700; }
        .summary-table td:last-child { text-align: right; white-space: nowrap; }
        .summary-table .final td { border-top: 2px solid #111; font-weight: 700; font-size: 12px; }
        .history-table th { background: #f4f4f4; text-transform: uppercase; font-size: 10px; }
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 38mm;
            margin-top: 28mm;
        }
        .signature-line { border-top: 1px solid #111; padding-top: 5px; text-align: center; font-weight: 700; }
        .footer-note {
            margin-top: 12px;
            border-top: 1px solid #111;
            padding-top: 6px;
            text-align: center;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: #fff; color: #111; }
            .print-actions { display: none !important; }
            .receipt-page { width: 210mm; min-height: 297mm; margin: 0; padding: 14mm 12mm; box-shadow: none; }
            tr, .info-box, .summary-box, .amount-strip, .signature-grid { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    @php
        // DUE COLLECTION IMPROVEMENT
        // INVOICE PAYMENT FLOW
        $allocatedInvoices = $payment->allocations->pluck('invoice.invoice_no')->filter()->values();
        $invoiceLabel = $payment->invoice
            ? $payment->invoice->invoice_no
            : ($allocatedInvoices->isNotEmpty() ? $allocatedInvoices->join(', ') : 'Full customer due');
    @endphp

    <div class="print-actions">
        <button onclick="handlePrintClick(this)" class="btn btn-dark px-5">Print Receipt</button>
        <a href="{{ route('payments.index') }}" class="btn btn-secondary px-4 ms-2">Back to List</a>
    </div>

    <div class="receipt-page">
        <div class="receipt-header">
            <div>
                <div class="company-name">TH Trade Corporation</div>
                <div>Specialized Medical Equipment Supplier<br>Dhaka, Bangladesh</div>
            </div>
            <div>
                <div class="receipt-title">Money Receipt</div>
                <table class="meta-table">
                    <tr><td>Receipt No:</td><td>{{ $receiptNo ?? 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
                    <tr><td>Collection Date:</td><td>{{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}</td></tr>
                    <tr><td>Printed On:</td><td>{{ now()->format('d M, Y h:i A') }}</td></tr>
                </table>
            </div>
        </div>

        <div class="amount-strip">
            <div>
                <div class="label">Paid Amount</div>
                <div>Payment Method: {{ ucfirst($payment->payment_method) }}</div>
            </div>
            <div class="amount">Tk. {{ number_format($payment->amount, 2) }}</div>
        </div>

        <div class="info-box">
            <div class="box-title">Customer & Collection Information</div>
            <div class="info-grid">
                <div class="info-panel">
                    <div class="line"><div class="label">Customer ID</div><div>{{ $payment->customer->customer_id }}</div></div>
                    <div class="line"><div class="label">Organization</div><div><strong>{{ $payment->customer->hospital_name }}</strong></div></div>
                    <div class="line"><div class="label">Customer</div><div>{{ $payment->customer->customer_name ?: 'N/A' }}</div></div>
                    <div class="line"><div class="label">Mobile</div><div>{{ $payment->customer->mobile }}</div></div>
                </div>
                <div class="info-panel">
                    <div class="line"><div class="label">Invoice No</div><div>{{ $invoiceLabel }}</div></div>
                    <div class="line"><div class="label">Collected By</div><div>{{ $payment->user->name ?? 'System' }}</div></div>
                    <div class="line"><div class="label">Receipt Type</div><div>{{ $payment->invoice_id ? 'Specific Invoice Payment' : 'Full Customer Due Payment' }}</div></div>
                </div>
            </div>
        </div>

        <div class="summary-box">
            <div class="box-title">Due Summary</div>
            <table class="summary-table">
                <tr>
                    <td>Previous Due</td>
                    <td>Tk. {{ number_format($previousDue, 2) }}</td>
                </tr>
                <tr>
                    <td>Paid Amount</td>
                    <td>- Tk. {{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr class="final">
                    <td>Remaining Due After This Collection</td>
                    <td>Tk. {{ number_format($receiptRemainingDue ?? $remainingDue, 2) }}</td>
                </tr>
                <tr>
                    <td>Customer Current Due</td>
                    <td>Tk. {{ number_format($remainingDue, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="box-title" style="border: 1px solid #111; border-bottom: 0;">Payment History Summary</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th class="text-right">Invoice Total</th>
                    <th class="text-right">Allocated Paid</th>
                    <th class="text-right">Current Invoice Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payment->allocations as $allocation)
                    <tr>
                        <td>{{ $allocation->invoice->invoice_no ?? 'Invoice' }}</td>
                        <td class="text-right">Tk. {{ number_format($allocation->invoice->net_payable ?? 0, 2) }}</td>
                        <td class="text-right">Tk. {{ number_format($allocation->amount, 2) }}</td>
                        <td class="text-right">Tk. {{ number_format($allocation->invoice->due_amount ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>{{ $invoiceLabel }}</td>
                        <td class="text-right">---</td>
                        <td class="text-right">Tk. {{ number_format($payment->amount, 2) }}</td>
                        <td class="text-right">Tk. {{ number_format($receiptRemainingDue ?? $remainingDue, 2) }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($payment->note)
            <div class="info-box mt-2">
                <div class="box-title">Remarks</div>
                <div class="info-panel">{{ $payment->note }}</div>
            </div>
        @endif

        <div class="signature-grid">
            <div class="signature-line">Customer Signature</div>
            <div class="signature-line">Authorized Signature</div>
        </div>

        <div class="footer-note">
            This receipt records payment collection only. Invoice, stock, and due calculations remain controlled by the accounting system.
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
