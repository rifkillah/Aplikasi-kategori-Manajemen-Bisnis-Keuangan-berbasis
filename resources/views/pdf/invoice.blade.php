<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $transaction->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 40px;
            line-height: 1.5;
        }
        .header { width: 100%; margin-bottom: 32px; }
        .header td { vertical-align: top; }
        .brand { font-size: 22px; font-weight: bold; color: #4f46e5; letter-spacing: -0.5px; }
        .muted { color: #64748b; font-size: 11px; }
        .invoice-title { text-align: right; font-size: 28px; font-weight: bold; letter-spacing: 2px; color: #0f172a; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            background: #ecfdf5;
            color: #047857;
        }
        .badge-unpaid { background: #fef2f2; color: #b91c1c; }
        .badge-partial { background: #fffbeb; color: #b45309; }
        .badge-void { background: #fef2f2; color: #b91c1c; }
        .info-table { width: 100%; margin-bottom: 28px; }
        .info-table td { vertical-align: top; width: 50%; }
        .section-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #94a3b8;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .items { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .items thead th {
            background: #f1f5f9;
            color: #475569;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            text-align: left;
            padding: 9px 10px;
        }
        .items tbody td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .items .right { text-align: right; }
        .totals { width: 260px; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 5px 0; font-size: 11px; }
        .totals .label { color: #64748b; }
        .totals .value { text-align: right; font-weight: bold; }
        .totals .grand td { border-top: 1px solid #cbd5e1; padding-top: 9px; font-size: 14px; color: #0f172a; }
        .notes { margin-top: 24px; padding: 12px; background: #fffbeb; border-left: 3px solid #f59e0b; font-size: 10px; color: #92400e; }
        .footer { margin-top: 48px; border-top: 1px solid #e2e8f0; padding-top: 14px; text-align: center; font-size: 9px; color: #94a3b8; }
        .void-watermark {
            position: fixed;
            top: 40%;
            left: 12%;
            font-size: 90px;
            color: rgba(225, 29, 72, 0.08);
            font-weight: bold;
            transform: rotate(-18deg);
            letter-spacing: 8px;
        }
        .logo { max-height: 56px; max-width: 140px; margin-bottom: 8px; }
    </style>
</head>
<body>
    @if ($transaction->is_void)
        <div class="void-watermark">DIBATALKAN</div>
    @endif

    <table class="header">
        <tr>
            <td style="width: 60%;">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" class="logo" alt="Logo">
                @endif
                <div class="brand">{{ $tenant->business_name }}</div>
                <div class="muted" style="margin-top: 6px;">
                    {{ $tenant->address ?: 'Alamat belum diatur' }}<br>
                    @if ($tenant->phone) {{ $tenant->phone }} @endif
                    @if ($tenant->email) · {{ $tenant->email }} @endif
                </div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="invoice-title">INVOICE</div>
                <div style="margin-top: 6px; font-weight: bold; color: #4f46e5;">{{ $transaction->invoice_number }}</div>
                <div class="muted" style="margin-top: 6px;">
                    Tanggal: {{ $transaction->transaction_date->format('d/m/Y H:i') }}<br>
                    Tipe: {{ $transaction->type_label }}
                </div>
                <div style="margin-top: 8px;">
                    <span class="badge {{ $transaction->is_void ? 'badge-void' : ($transaction->payment_status === 'unpaid' ? 'badge-unpaid' : ($transaction->payment_status === 'partial' ? 'badge-partial' : '')) }}">
                        {{ $transaction->is_void ? 'DIBATALKAN' : strtoupper($transaction->payment_status_label) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>
                <div class="section-label">Ditagihkan kepada</div>
                <div style="font-size: 13px; font-weight: bold;">{{ $transaction->customer?->name ?? 'Pelanggan umum' }}</div>
                <div class="muted">
                    {{ $transaction->customer?->phone ?? '' }}<br>
                    {{ $transaction->customer?->address ?? '' }}
                </div>
            </td>
            <td style="text-align: right;">
                <div class="section-label">Informasi pembayaran</div>
                <div class="muted">
                    Metode: <strong style="color: #334155;">{{ strtoupper($transaction->payment_method ?? '—') }}</strong><br>
                    Kasir: <strong style="color: #334155;">{{ $transaction->user?->name ?? 'Sistem' }}</strong><br>
                    @if ($transaction->due_date)
                        Jatuh tempo: <strong style="color: #334155;">{{ $transaction->due_date->format('d/m/Y') }}</strong>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if ($transaction->details->isNotEmpty())
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 45%;">Produk</th>
                    <th style="width: 15%;">SKU</th>
                    <th style="width: 10%;" class="right">Qty</th>
                    <th style="width: 15%;" class="right">Harga</th>
                    <th style="width: 15%;" class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->details as $detail)
                    <tr>
                        <td>{{ $detail->product_name }}</td>
                        <td class="muted">{{ $detail->sku ?? '—' }}</td>
                        <td class="right">{{ $detail->quantity }}</td>
                        <td class="right">{{ rupiah($detail->price) }}</td>
                        <td class="right" style="font-weight: bold;">{{ rupiah($detail->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table class="items">
            <thead>
                <tr><th>Deskripsi</th><th class="right">Total</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $transaction->description ?? $transaction->type_label }}</td>
                    <td class="right" style="font-weight: bold;">{{ rupiah($transaction->total_amount) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">{{ rupiah($transaction->subtotal) }}</td>
        </tr>
        @if ((float) $transaction->discount > 0)
            <tr>
                <td class="label">Diskon</td>
                <td class="value" style="color: #e11d48;">- {{ rupiah($transaction->discount) }}</td>
            </tr>
        @endif
        @if ((float) $transaction->tax > 0)
            <tr>
                <td class="label">Pajak</td>
                <td class="value">{{ rupiah($transaction->tax) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td style="font-weight: bold;">Total</td>
            <td class="value">{{ rupiah($transaction->total_amount) }}</td>
        </tr>
        <tr>
            <td class="label">Dibayar</td>
            <td class="value">{{ rupiah($transaction->paid_amount) }}</td>
        </tr>
        @if ((float) $transaction->total_amount > (float) $transaction->paid_amount)
            <tr>
                <td class="label" style="color: #e11d48;">Sisa tagihan</td>
                <td class="value" style="color: #e11d48;">{{ rupiah($transaction->total_amount - $transaction->paid_amount) }}</td>
            </tr>
        @endif
    </table>

    @if ($transaction->notes)
        <div class="notes"><strong>Catatan:</strong> {{ $transaction->notes }}</div>
    @endif

    <div style="margin-top: 44px; width: 100%;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%; font-size: 10px; color: #64748b;">
                    Terima kasih telah berbelanja di {{ $tenant->business_name }}.<br>
                    Invoice ini dibuat secara otomatis oleh Kasirin.
                </td>
                <td style="width: 40%; text-align: center;">
                    <div style="height: 48px;"></div>
                    <div style="border-top: 1px solid #94a3b8; padding-top: 6px; font-size: 10px; color: #64748b;">
                        {{ $tenant->business_name }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ $tenant->business_name }} · {{ $tenant->phone ?? '' }} · {{ $tenant->email ?? '' }}<br>
        Dicetak pada {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
