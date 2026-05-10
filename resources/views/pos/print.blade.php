<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* // INVOICE PRINT REDESIGN */
        /* // A4 PRINT LAYOUT */
        /* // ERP STYLE INVOICE */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #d9d9d9;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        .print-actions {
            margin: 18px 0;
            text-align: center;
        }

        .invoice-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px;
            padding: 42mm 12mm 12mm;
            background: #fff;
            box-shadow: 0 3px 18px rgba(0, 0, 0, 0.18);
            display: flex;
            flex-direction: column;
        }

        .invoice-body {
            flex: 1 0 auto;
        }

        .invoice-title-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: end;
            gap: 12px;
            border-bottom: 2px solid #111;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .invoice-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .invoice-meta {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-meta td {
            padding: 1px 0 1px 8px;
            font-size: 11px;
            white-space: nowrap;
        }

        .invoice-meta td:first-child {
            color: #333;
            text-align: right;
            font-weight: 700;
        }

        .invoice-box {
            border: 1px solid #111;
            margin-bottom: 8px;
        }

        .invoice-box-title {
            border-bottom: 1px solid #111;
            padding: 3px 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            background: #f4f4f4;
        }

        .customer-grid {
            display: grid;
            grid-template-columns: 1.45fr 0.9fr;
            gap: 0;
        }

        .customer-panel {
            padding: 6px;
            min-height: 72px;
        }

        .customer-panel + .customer-panel {
            border-left: 1px solid #111;
        }

        .info-line {
            display: grid;
            grid-template-columns: 76px 1fr;
            gap: 6px;
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: 700;
            color: #222;
        }

        .info-value {
            color: #111;
        }

        .status-text {
            font-weight: 700;
            text-transform: uppercase;
        }

        .items-table,
        .totals-table,
        .timeline-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table {
            border: 1px solid #111;
            margin-bottom: 8px;
        }

        .items-table th {
            border: 1px solid #111;
            padding: 4px 5px;
            background: #f4f4f4;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .items-table td {
            border: 1px solid #555;
            padding: 4px 5px;
            vertical-align: top;
            font-size: 10.5px;
        }

        .items-table .description {
            line-height: 1.25;
        }

        .product-name {
            font-weight: 700;
        }

        .model-no {
            display: block;
            margin-top: 2px;
            font-size: 9.5px;
            color: #333;
        }

        .amount-row {
            display: grid;
            grid-template-columns: 1fr 78mm;
            gap: 10mm;
            align-items: start;
            margin-top: 6px;
        }

        .amount-words {
            border: 1px solid #111;
            min-height: 34px;
            padding: 6px;
        }

        .amount-words-title {
            margin-bottom: 3px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .totals-table {
            border: 1px solid #111;
        }

        .totals-table td {
            border-bottom: 1px solid #c5c5c5;
            padding: 3px 6px;
            font-size: 10.5px;
        }

        .totals-table td:first-child {
            font-weight: 700;
        }

        .totals-table td:last-child {
            width: 32mm;
            text-align: right;
            white-space: nowrap;
        }

        .totals-table tr:last-child td {
            border-bottom: 0;
        }

        .totals-table .net-row td,
        .totals-table .due-row td {
            border-top: 1px solid #111;
            font-size: 11px;
            font-weight: 700;
        }

        .timeline-section {
            margin-top: 9px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .timeline-table {
            border: 1px solid #111;
        }

        .timeline-table th,
        .timeline-table td {
            border: 1px solid #777;
            padding: 3px 5px;
            font-size: 10px;
        }

        .timeline-table th {
            background: #f4f4f4;
            font-weight: 700;
            text-transform: uppercase;
        }

        .closing-note {
            margin-top: 8px;
            border-top: 1px solid #111;
            border-bottom: 1px solid #111;
            padding: 5px 0;
            font-size: 10px;
            text-align: center;
        }

        .invoice-footer {
            flex-shrink: 0;
            margin-top: auto;
            padding-top: 22mm;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 34mm;
            align-items: end;
        }

        .signature-block {
            min-height: 24mm;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .signature-line {
            border-top: 1px solid #111;
            padding-top: 5px;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
        }

        .signature-hint {
            margin-top: 3px;
            text-align: center;
            font-size: 9px;
            color: #333;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: #fff;
                color: #111;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-actions {
                display: none !important;
            }

            .invoice-page {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                padding: 42mm 12mm 12mm;
                box-shadow: none;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .invoice-title-row,
            .invoice-box,
            .amount-row,
            .timeline-section,
            .closing-note,
            .invoice-footer {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="print-actions">
        <button onclick="handlePrintClick(this)" class="btn btn-dark px-5">
            Print Invoice
        </button>
        <a href="{{ route('pos.index') }}" class="btn btn-secondary px-4 ms-2">Back to POS</a>
    </div>

    @php
        // FINANCIAL CALCULATION FIX
        // CRITICAL ACCOUNTING FIX
        // DUE CALCULATION FIX
        // INVOICE PRINT DATA FIX
        // INVOICE PRINT REDESIGN
        $subTotal = round((float) $invoice->sub_total, 2);
        // DISCOUNT TYPE SYSTEM
        $discountType = $invoice->discount_type ?? 'percentage';
        $discountAmount = $discountType === 'fixed'
            ? round((float) $invoice->discount_amount, 2)
            : round(($subTotal * (float) $invoice->discount_percent) / 100, 2);
        $vatAmount = round(($subTotal * (float) $invoice->vat_percent) / 100, 2);
        $aitAmount = round(($subTotal * (float) $invoice->ait_percent) / 100, 2);
        $extraCharge = round((float) $invoice->extra_charge, 2);
        $currentInvoice = round((float) $invoice->net_payable, 2);
        $paidAmount = round((float) $invoice->received_amount, 2);
        $finalDue = round(max(0, (float) $invoice->due_amount), 2);
        $totalPayable = round($paidAmount + $finalDue, 2);
        $previousDue = round(max(0, $totalPayable - $currentInvoice), 2);
        $paymentAllocations = $invoice->allocations()->with(['payment.user'])->orderBy('created_at')->get();

        $numberWords = [
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty',
            50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        ];

        $toWords = function ($number) use (&$toWords, $numberWords) {
            $number = (int) $number;

            if ($number < 21) {
                return $numberWords[$number];
            }

            if ($number < 100) {
                $tens = ((int) floor($number / 10)) * 10;
                $rest = $number % 10;
                return $numberWords[$tens] . ($rest ? ' ' . $numberWords[$rest] : '');
            }

            if ($number < 1000) {
                $hundreds = (int) floor($number / 100);
                $rest = $number % 100;
                return $numberWords[$hundreds] . ' Hundred' . ($rest ? ' ' . $toWords($rest) : '');
            }

            if ($number < 100000) {
                $thousands = (int) floor($number / 1000);
                $rest = $number % 1000;
                return $toWords($thousands) . ' Thousand' . ($rest ? ' ' . $toWords($rest) : '');
            }

            if ($number < 10000000) {
                $lakhs = (int) floor($number / 100000);
                $rest = $number % 100000;
                return $toWords($lakhs) . ' Lakh' . ($rest ? ' ' . $toWords($rest) : '');
            }

            $crores = (int) floor($number / 10000000);
            $rest = $number % 10000000;
            return $toWords($crores) . ' Crore' . ($rest ? ' ' . $toWords($rest) : '');
        };

        $wholeAmount = (int) floor($finalDue);
        $paisaAmount = (int) round(($finalDue - $wholeAmount) * 100);
        $amountInWords = $toWords($wholeAmount) . ' Taka';
        if ($paisaAmount > 0) {
            $amountInWords .= ' and ' . $toWords($paisaAmount) . ' Paisa';
        }
        $amountInWords .= ' Only';
    @endphp

    <div class="invoice-page">
        <div class="invoice-body">
            <div class="invoice-title-row">
                <div>
                    <h1 class="invoice-title">Invoice</h1>
                </div>
                <table class="invoice-meta">
                    <tr>
                        <td>Invoice No:</td>
                        <td>{{ $invoice->invoice_no }}</td>
                    </tr>
                    <tr>
                        <td>Date:</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M, Y') }}</td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td>
                            <span class="status-text">
                                @if($finalDue <= 0)
                                    Paid
                                @elseif($paidAmount > 0)
                                    Partial
                                @else
                                    Unpaid
                                @endif
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="invoice-box">
                <div class="invoice-box-title">Customer & Approval Information</div>
                <div class="customer-grid">
                    <div class="customer-panel">
                        <div class="info-line">
                            <div class="info-label">Customer ID</div>
                            <div class="info-value">{{ $invoice->customer->customer_id }}</div>
                        </div>
                        <div class="info-line">
                            <div class="info-label">Customer</div>
                            <div class="info-value"><strong>{{ $invoice->customer->customer_name ?: 'N/A' }}</strong></div>
                        </div>
                        @if($invoice->customer->hospital_name)
                            <div class="info-line">
                            <div class="info-label">Hospital</div>
                            <div class="info-value">{{ $invoice->customer->hospital_name }}</div>
                            </div>
                        @endif
                        <div class="info-line">
                            <div class="info-label">Mobile</div>
                            <div class="info-value">{{ $invoice->customer->mobile }}</div>
                        </div>
                        <div class="info-line">
                            <div class="info-label">Address</div>
                            <div class="info-value">{{ $invoice->customer->address }}</div>
                        </div>
                    </div>
                    <div class="customer-panel">
                        <div class="info-line">
                            <div class="info-label">Entered By</div>
                            <div class="info-value">{{ $invoice->user->name }}</div>
                        </div>
                        <div class="info-line">
                            <div class="info-label">Approved By</div>
                            <div class="info-value">Authorized Person</div>
                        </div>
                        <div class="info-line">
                            <div class="info-label">Printed On</div>
                            <div class="info-value">{{ now()->format('d M, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 9mm;">SL</th>
                        <th style="width: 27mm;">Product Code</th>
                        <th>Product Description</th>
                        <th style="width: 22mm;">Pack Size</th>
                        <th style="width: 14mm;">Qty</th>
                        <th style="width: 25mm;">Unit Price</th>
                        <th style="width: 28mm;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item->product->product_id }}</td>
                            <td class="description">
                                <span class="product-name">{{ $item->product->product_name }}</span>
                                @if($item->product->model_no)
                                    <span class="model-no">Model No: {{ $item->product->model_no }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->product->pack_size ?? '-' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right nowrap">Tk. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right nowrap">Tk. {{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="amount-row">
                <div class="amount-words">
                    <div class="amount-words-title">Amount in Words</div>
                    {{ $amountInWords }}
                </div>

                <table class="totals-table">
                    <tr>
                        <td>Total Amount</td>
                        <td>Tk. {{ number_format($subTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>
                            @if($discountType === 'fixed')
                                Discount (Fixed)
                            @else
                                Discount @ {{ number_format((float) $invoice->discount_percent, 2) }}%
                            @endif
                        </td>
                        <td>- Tk. {{ number_format($discountAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>VAT @ {{ number_format((float) $invoice->vat_percent, 2) }}%</td>
                        <td>+ Tk. {{ number_format($vatAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>AIT @ {{ number_format((float) $invoice->ait_percent, 2) }}%</td>
                        <td>+ Tk. {{ number_format($aitAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Extra Charges</td>
                        <td>+ Tk. {{ number_format($extraCharge, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Previous Due</td>
                        <td>+ Tk. {{ number_format($previousDue, 2) }}</td>
                    </tr>
                    <tr class="net-row">
                        <td>Net Payable</td>
                        <td>Tk. {{ number_format($totalPayable, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Received Amount</td>
                        <td>- Tk. {{ number_format($paidAmount, 2) }}</td>
                    </tr>
                    <tr class="due-row">
                        <td>Final Due</td>
                        <td>Tk. {{ number_format($finalDue, 2) }}</td>
                    </tr>
                </table>
            </div>

            @if($paymentAllocations->isNotEmpty())
                <div class="timeline-section">
                    <div class="invoice-box-title" style="border: 1px solid #111; border-bottom: 0;">Invoice Timeline / Payment History</div>
                    <table class="timeline-table">
                        <thead>
                            <tr>
                                <th style="width: 34mm;">Date</th>
                                <th style="width: 36mm;">Type</th>
                                <th>Reference / Note</th>
                                <th style="width: 30mm;" class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M, Y') }}</td>
                                <td>Invoice Created</td>
                                <td>{{ $invoice->invoice_no }}</td>
                                <td class="text-right">Tk. {{ number_format($currentInvoice, 2) }}</td>
                            </tr>
                            @foreach($paymentAllocations as $allocation)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($allocation->payment->date)->format('d M, Y') }}</td>
                                    <td>
                                        {{ $allocation->payment->payment_type === 'invoice' ? 'Payment / Due Collection' : 'Payment' }}
                                    </td>
                                    <td>
                                        {{ $allocation->payment->note ?: ucfirst($allocation->payment->payment_method) . ' collection' }}
                                        @if($allocation->payment->user)
                                            <span> - {{ $allocation->payment->user->name }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">Tk. {{ number_format($allocation->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="closing-note">
                Closing Balance / Final Due: <strong>Tk. {{ number_format($finalDue, 2) }}</strong>
            </div>
        </div>

        <div class="invoice-footer">
            <div class="signature-grid">
                <div class="signature-block">
                    <div class="signature-line">Customer Signature with Seal</div>
                    <div class="signature-hint">Received goods in good condition</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line">Authorized Signature</div>
                    <div class="signature-hint">For TH Trade Corporation</div>
                </div>
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
