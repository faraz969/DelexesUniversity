<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
            background: #f5f5f5;
        }
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        .print-button button {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .print-button button:hover {
            background: #1557b0;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            background: white;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .bank-logo {
            max-height: 70px;
            max-width: 180px;
        }
        .receipt-info {
            text-align: right;
        }
        .receipt-number {
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 8px;
            color: #000;
        }
        .date-time {
            font-size: 12px;
            color: #666;
        }
        .paid-watermark {
            position: absolute;
            top: 150px;
            right: 80px;
            font-size: 72px;
            color: rgba(0, 0, 0, 0.08);
            transform: rotate(-45deg);
            font-weight: bold;
            z-index: 0;
        }
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 25px;
            position: relative;
            z-index: 1;
        }
        .left-section, .right-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .field-group {
            margin-bottom: 10px;
        }
        .field-label {
            font-weight: bold;
            font-size: 11px;
            color: #333;
            margin-bottom: 3px;
        }
        .field-value {
            font-size: 13px;
            color: #000;
        }
        .payment-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .payment-table th,
        .payment-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .payment-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 12px;
        }
        .payment-table td {
            font-size: 12px;
        }
        .amount-section {
            margin-top: 25px;
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            padding-right: 10px;
        }
        .bank-stamp {
            margin-top: 40px;
            text-align: center;
            border: 3px solid #000;
            border-radius: 50%;
            width: 220px;
            height: 220px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 25px;
            background: rgba(255, 255, 255, 0.9);
        }
        .stamp-text {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            line-height: 1.5;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        @media print {
            body {
                padding: 0;
                background: white;
            }
            .print-button {
                display: none;
            }
            .receipt-container {
                border: none;
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>
    </div>
    
    <div class="receipt-container">
        <div class="paid-watermark">PAID</div>
        
        <div class="header">
            <div>
                @if($bank_logo)
                    <img src="{{ $bank_logo }}" alt="Bank Logo" class="bank-logo">
                @else
                    <div style="font-size: 20px; font-weight: bold;">{{ $bank_name }}</div>
                @endif
            </div>
            <div class="receipt-info">
                <div class="receipt-number">RECEIPT #: {{ $receipt_number }}</div>
                <div class="date-time">{{ \Carbon\Carbon::parse($transaction_date)->format('d-M-Y H:i:s') }}</div>
            </div>
        </div>

        <div class="content">
            <div class="left-section">
                <div class="field-group">
                    <div class="field-label">Institution:</div>
                    <div class="field-value">{{ $institution }}</div>
                </div>
                <div class="field-group">
                    <div class="field-label">Form Type:</div>
                    <div class="field-value">{{ $form_type }}</div>
                </div>
                <div class="field-group">
                    <div class="field-label">Branch paid:</div>
                    <div class="field-value">{{ $bank_name }} ({{ $branch }})</div>
                </div>
                <div class="field-group">
                    <div class="field-label">Paid By:</div>
                    <div class="field-value">{{ $paid_by }}</div>
                </div>
                @if(isset($voucher_for) && $voucher_for)
                <div class="field-group">
                    <div class="field-label">Voucher For:</div>
                    <div class="field-value">{{ $voucher_for }}</div>
                </div>
                @endif
            </div>

            <div class="right-section">
                <div class="field-group">
                    <div class="field-label">Serial #:</div>
                    <div class="field-value">{{ $serial_number }}</div>
                </div>
                <div class="field-group">
                    <div class="field-label">PIN:</div>
                    <div class="field-value">{{ $pin }}</div>
                </div>
                <div class="field-group">
                    <div class="field-label">Academic Year:</div>
                    <div class="field-value">{{ $academic_year }}</div>
                </div>
                <div class="field-group">
                    <div class="field-label">TRANSACTION DATE:</div>
                    <div class="field-value">{{ \Carbon\Carbon::parse($transaction_date)->format('m/d/Y h:i:s A') }}</div>
                </div>
            </div>
        </div>

        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 90%;">Payment Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ $payment_description }}</td>
                </tr>
            </tbody>
        </table>

        <div class="amount-section">
            <div style="margin-bottom: 8px;">Amount Paid:</div>
            <div style="font-size: 20px;">GHS {{ $amount_paid }}</div>
        </div>

        <div class="bank-stamp">
            <div class="stamp-text">
                {{ strtoupper($bank_name) }}<br>
                {{ strtoupper($branch) }}<br>
                <br>
                RECEIVED<br>
                {{ \Carbon\Carbon::parse($transaction_date)->format('d M Y') }}<br>
                <br>
                TELLER
            </div>
        </div>

        <div class="footer">
            Issued by {{ $bank_name }} with authority of {{ $institution }}.
        </div>
    </div>
</body>
</html>


