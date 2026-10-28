<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->transaction_number }}</title>
    <style>
        @page { margin: 32px; }
        body { color: #26332d; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1, p { margin: 0; }
        .header { border-bottom: 2px solid #28745f; margin-bottom: 24px; padding-bottom: 14px; }
        .store-name { color: #173f35; font-size: 20px; font-weight: bold; }
        .muted { color: #687770; }
        .invoice-title { color: #173f35; font-size: 18px; font-weight: bold; text-align: right; }
        .info, .items, .totals { border-collapse: collapse; width: 100%; }
        .info { margin-bottom: 20px; }
        .info td { padding: 4px 0; vertical-align: top; }
        .items th, .items td { border-bottom: 1px solid #dfe5e1; padding: 9px 7px; vertical-align: middle; }
        .items th { background: #edf4ef; color: #173f35; text-align: left; }
        .product-image { height: 42px; width: 42px; }
        .right { text-align: right; }
        .center { text-align: center; }
        .totals { margin-top: 16px; }
        .totals td { padding: 5px 7px; }
        .grand-total { border-top: 2px solid #28745f; color: #173f35; font-size: 14px; font-weight: bold; }
        .thanks { border-top: 1px solid #dfe5e1; margin-top: 28px; padding-top: 14px; text-align: center; }
    </style>
</head>
<body>
    <header class="header">
        <table class="info">
            <tr>
                <td>
                    <p class="store-name">TOKO DIKA MAKMUR</p>
                    <p class="muted">Jl. Contoh No. 123 · Jember</p>
                </td>
                <td class="right">
                    <p class="invoice-title">INVOICE</p>
                    <p class="muted">{{ $transaction->transaction_number }}</p>
                </td>
            </tr>
        </table>
    </header>

    <table class="info">
        <tr>
            <td class="muted">Tanggal</td>
            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
            <td class="muted">Kasir</td>
            <td>{{ $transaction->user->name }}</td>
        </tr>
        <tr>
            <td class="muted">Pelanggan</td>
            <td>{{ $transaction->customer_name }}</td>
            <td class="muted">Pembayaran</td>
            <td>{{ $transaction->payment_method }}</td>
        </tr>
        @if ($transaction->customer_phone)
            <tr>
                <td class="muted">No. Telepon</td>
                <td colspan="3">{{ $transaction->customer_phone }}</td>
            </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Gambar</th>
                <th>Produk</th>
                <th class="center">Jumlah</th>
                <th class="right">Harga</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->details as $detail)
                <tr>
                    <td>
                        @if ($invoiceImages[$detail->id] ?? false)
                            <img class="product-image" src="{{ $invoiceImages[$detail->id] }}" alt="Gambar {{ $detail->product_name }}">
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $detail->product_name }}<br><span class="muted">{{ $detail->product_code ?: '-' }}</span></td>
                    <td class="center">{{ $detail->quantity }}</td>
                    <td class="right">{{ \Illuminate\Support\Number::currency((float) $detail->price, 'IDR', locale: 'id') }}</td>
                    <td class="right">{{ \Illuminate\Support\Number::currency((float) $detail->subtotal, 'IDR', locale: 'id') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="right muted">Subtotal</td>
            <td class="right">{{ \Illuminate\Support\Number::currency((float) $transaction->subtotal, 'IDR', locale: 'id') }}</td>
        </tr>
        <tr>
            <td class="right muted">Diskon</td>
            <td class="right">- {{ \Illuminate\Support\Number::currency((float) $transaction->discount, 'IDR', locale: 'id') }}</td>
        </tr>
        <tr>
            <td class="right muted">Pajak &amp; biaya lain</td>
            <td class="right">{{ \Illuminate\Support\Number::currency((float) ($transaction->tax + $transaction->other_fee), 'IDR', locale: 'id') }}</td>
        </tr>
        <tr class="grand-total">
            <td class="right">TOTAL</td>
            <td class="right">{{ \Illuminate\Support\Number::currency((float) $transaction->grand_total, 'IDR', locale: 'id') }}</td>
        </tr>
        <tr>
            <td class="right muted">Dibayar</td>
            <td class="right">{{ \Illuminate\Support\Number::currency((float) $transaction->paid_amount, 'IDR', locale: 'id') }}</td>
        </tr>
        <tr>
            <td class="right muted">Kembalian</td>
            <td class="right">{{ \Illuminate\Support\Number::currency((float) $transaction->change_amount, 'IDR', locale: 'id') }}</td>
        </tr>
    </table>

    <p class="thanks">Terima kasih telah berbelanja di TOKO RETAIL MAKMUR.</p>
</body>
</html>
