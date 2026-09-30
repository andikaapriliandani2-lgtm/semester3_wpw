@extends('layouts.admin', ['title' => 'Struk '.$transaction->transaction_number, 'pageLabel' => 'Struk Transaksi'])

@push('styles')
    <style>
        .receipt-wrap { max-width: 640px; margin: 0 auto 2rem; padding: 0 1rem; }
        .receipt-paper { padding: 1.5rem; border: 1px solid #dfe5e1; border-radius: 8px; background: #fff; }
        .receipt-title { color: #173f35; font-size: 1.1rem; font-weight: 700; }
        .receipt-meta { color: #697871; font-size: .78rem; }
        .receipt-table th, .receipt-table td { padding: .5rem .35rem; font-size: .8rem; }
        .receipt-total { color: #173f35; font-size: 1.15rem; font-weight: 700; }
        @media print {
            #accordionSidebar, .topbar, .receipt-actions { display: none !important; }
            #content-wrapper, #content { margin: 0 !important; padding: 0 !important; }
            .receipt-wrap { max-width: none; margin: 0; padding: 0; }
            .receipt-paper { border: 0; }
        }
    </style>
@endpush

@section('content')
    <main class="receipt-wrap">
        <div class="receipt-actions d-flex justify-content-between align-items-center mb-3">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('transactions.index') }}"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>Transaksi Baru</a>
            <button class="btn btn-primary btn-sm" type="button" onclick="window.print()"><i class="fas fa-print mr-1" aria-hidden="true"></i>Cetak Struk</button>
        </div>
        <section class="receipt-paper">
            <header class="text-center mb-3">
                <div class="receipt-title">TOKO RETAIL MAKMUR</div>
                <div class="receipt-meta">Jl. Contoh No. 123 · Jember</div>
            </header>
            <div class="receipt-meta d-flex justify-content-between"><span>No. Transaksi</span><strong>{{ $transaction->transaction_number }}</strong></div>
            <div class="receipt-meta d-flex justify-content-between"><span>Kasir</span><strong>{{ $transaction->user->name }}</strong></div>
            <div class="receipt-meta d-flex justify-content-between mb-3"><span>{{ $transaction->created_at->format('d/m/Y H:i') }} · {{ $transaction->customer_name }}</span><strong>{{ $transaction->payment_method }}</strong></div>

            <div class="table-responsive">
                <table class="table receipt-table mb-2">
                    <thead><tr><th>Barang</th><th class="text-center">Qty</th><th class="text-right">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($transaction->details as $detail)
                            <tr>
                                <td>{{ $detail->product_name }}<div class="receipt-meta">{{ $detail->product_code }} · {{ \Illuminate\Support\Number::currency((float) $detail->price, 'IDR', locale: 'id') }}</div></td>
                                <td class="text-center">{{ $detail->quantity }}</td>
                                <td class="text-right">{{ \Illuminate\Support\Number::currency((float) $detail->subtotal, 'IDR', locale: 'id') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="receipt-meta d-flex justify-content-between"><span>Subtotal</span><span>{{ \Illuminate\Support\Number::currency((float) $transaction->subtotal, 'IDR', locale: 'id') }}</span></div>
            <div class="receipt-meta d-flex justify-content-between"><span>Diskon</span><span>- {{ \Illuminate\Support\Number::currency((float) $transaction->discount, 'IDR', locale: 'id') }}</span></div>
            <div class="receipt-meta d-flex justify-content-between"><span>Pajak &amp; biaya lain</span><span>{{ \Illuminate\Support\Number::currency((float) ($transaction->tax + $transaction->other_fee), 'IDR', locale: 'id') }}</span></div>
            <div class="d-flex justify-content-between mt-2"><strong>TOTAL</strong><strong class="receipt-total">{{ \Illuminate\Support\Number::currency((float) $transaction->grand_total, 'IDR', locale: 'id') }}</strong></div>
            <div class="receipt-meta d-flex justify-content-between"><span>Bayar</span><span>{{ \Illuminate\Support\Number::currency((float) $transaction->paid_amount, 'IDR', locale: 'id') }}</span></div>
            <div class="receipt-meta d-flex justify-content-between"><span>Kembali</span><span>{{ \Illuminate\Support\Number::currency((float) $transaction->change_amount, 'IDR', locale: 'id') }}</span></div>
            <div class="receipt-meta text-center mt-4">Terima kasih telah berbelanja.</div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        window.addEventListener('load', () => {
            sessionStorage.removeItem('minimarket-checkout-draft');
            window.print();
        });
    </script>
@endpush