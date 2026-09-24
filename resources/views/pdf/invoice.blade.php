<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header-table { width: 100%; margin-bottom: 20px; }
        .header-table td { vertical-align: top; }
        .business-name { font-size: 20px; font-weight: bold; }
        .doc-title { font-size: 22px; font-weight: bold; text-align: right; color: #555; }
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { vertical-align: top; padding: 2px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th, table.items td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        table.items th { background: #f5f5f5; }
        table.items td.num, table.items th.num { text-align: right; }
        table.totals { width: 40%; margin-left: 60%; border-collapse: collapse; }
        table.totals td { padding: 4px 8px; }
        table.totals tr.total-row td { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; background: #eee; font-weight: bold; }
        .footer { margin-top: 40px; font-size: 10px; color: #888; text-align: center; }
        .signature { margin-top: 60px; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin-top: 40px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="business-name">{{ $invoice->order->business->name }}</div>
                <div>{{ $invoice->order->business->phone }}</div>
                @if ($invoice->order->business->email)
                    <div>{{ $invoice->order->business->email }}</div>
                @endif
                <div>{{ trim(($invoice->order->business->district ?? '').', '.($invoice->order->business->region ?? ''), ', ') }}</div>
            </td>
            <td class="doc-title">ANKARA<br><span style="font-size:14px;">{{ $invoice->invoice_number }}</span></td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width:50%;">
                <strong>Mteja:</strong><br>
                {{ $invoice->customer->name }}<br>
                {{ $invoice->customer->phone }}
            </td>
            <td style="width:50%; text-align:right;">
                <strong>Tarehe:</strong> {{ $invoice->issue_date->format('d M Y') }}<br>
                @if ($invoice->due_date)
                    <strong>Malipo Yanahitajika:</strong> {{ $invoice->due_date->format('d M Y') }}<br>
                @endif
                <span class="status-badge">{{ strtoupper($invoice->status) }}</span>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Bidhaa</th>
                <th class="num">Bei</th>
                <th class="num">Idadi</th>
                <th class="num">Jumla</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ money($item->unit_price) }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ money($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Jumla Ndogo</td><td class="num" style="text-align:right;">{{ money($invoice->subtotal) }}</td></tr>
        @if ($invoice->discount_amount > 0)
            <tr><td>Punguzo</td><td class="num" style="text-align:right;">-{{ money($invoice->discount_amount) }}</td></tr>
        @endif
        @if ($invoice->tax_amount > 0)
            <tr><td>Kodi</td><td class="num" style="text-align:right;">{{ money($invoice->tax_amount) }}</td></tr>
        @endif
        @if ($invoice->delivery_fee > 0)
            <tr><td>Uwasilishaji</td><td class="num" style="text-align:right;">{{ money($invoice->delivery_fee) }}</td></tr>
        @endif
        <tr class="total-row"><td>JUMLA</td><td class="num" style="text-align:right;">{{ money($invoice->total) }}</td></tr>
        <tr><td>Alicholipa</td><td class="num" style="text-align:right;">{{ money($invoice->amount_paid) }}</td></tr>
        <tr><td><strong>Deni</strong></td><td class="num" style="text-align:right;"><strong>{{ money($invoice->balance()) }}</strong></td></tr>
    </table>

    @if ($invoice->notes)
        <p><strong>Maelezo:</strong> {{ $invoice->notes }}</p>
    @endif

    <div class="signature">
        <div class="signature-line"></div>
        Sahihi
    </div>

    <div class="footer">
        Ankara hii imeandaliwa na MAUZO FASTA — Powered by AIO Graphics &amp; Technology
    </div>
</body>
</html>
