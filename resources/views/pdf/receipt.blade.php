<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header-table { width: 100%; margin-bottom: 20px; }
        .business-name { font-size: 20px; font-weight: bold; }
        .doc-title { font-size: 22px; font-weight: bold; text-align: right; color: #555; }
        table.details { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.details td { border: 1px solid #ddd; padding: 8px; }
        table.details td.label { width: 40%; background: #f5f5f5; font-weight: bold; }
        .amount-box { text-align: center; margin: 30px 0; }
        .amount-box .amount { font-size: 28px; font-weight: bold; }
        .footer { margin-top: 40px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="business-name">{{ $receipt->payment->order->business->name }}</div>
                <div>{{ $receipt->payment->order->business->phone }}</div>
            </td>
            <td class="doc-title">RISITI<br><span style="font-size:14px;">{{ $receipt->receipt_number }}</span></td>
        </tr>
    </table>

    <div class="amount-box">
        <div>Imepokelewa kutoka</div>
        <div style="font-size:16px;font-weight:bold;">{{ $receipt->customer->name }}</div>
        <div class="amount">{{ money($receipt->amount) }}</div>
    </div>

    <table class="details">
        <tr>
            <td class="label">Agizo Namba</td>
            <td>{{ $receipt->payment->order->order_number }}</td>
        </tr>
        <tr>
            <td class="label">Njia ya Malipo</td>
            <td>{{ strtoupper($receipt->method) }}</td>
        </tr>
        @if ($receipt->transaction_reference)
            <tr>
                <td class="label">Kumbukumbu</td>
                <td>{{ $receipt->transaction_reference }}</td>
            </tr>
        @endif
        <tr>
            <td class="label">Tarehe</td>
            <td>{{ $receipt->issued_at->format('d M Y H:i') }}</td>
        </tr>
    </table>

    <div class="footer">
        Asante kwa kuchagua {{ $receipt->payment->order->business->name }} — Powered by AIO Graphics &amp; Technology
    </div>
</body>
</html>
