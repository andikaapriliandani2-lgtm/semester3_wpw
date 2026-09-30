@extends('layouts.admin', ['title' => 'Kasir POS', 'pageLabel' => 'Kasir · Transaksi Baru'])

@push('styles')
    <style>
        .pos-page { padding: 0 1.5rem 1.5rem; }
        .pos-heading { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; margin-bottom: 1rem; }
        .pos-heading h1 { margin: 0; color: #173f35; font-size: 1.35rem; font-weight: 700; }
        .pos-meta { color: #65746e; font-size: .78rem; text-align: right; }
        .pos-meta strong { display: block; color: #173f35; font-size: .9rem; }
        .pos-page .card { border-radius: 8px; box-shadow: 0 2px 8px rgba(23, 63, 53, .045); }
        .pos-page .card-header { padding: .8rem 1rem; color: #173f35; font-size: .83rem; font-weight: 700; background: #fff; }
        .pos-page .card-body { padding: 1rem; }
        .pos-search-row { display: flex; gap: .55rem; }
        .pos-search-row .form-control { min-width: 0; height: 44px; }
        .pos-search-row .btn { min-width: 48px; }
        .pos-results { position: absolute; z-index: 5; top: calc(100% + 4px); right: 0; left: 0; display: none; max-height: 280px; overflow-y: auto; border: 1px solid #dbe2df; border-radius: 6px; background: #fff; box-shadow: 0 8px 20px rgba(25, 47, 40, .12); }
        .pos-result { display: flex; justify-content: space-between; gap: 1rem; padding: .7rem .85rem; cursor: pointer; border-bottom: 1px solid #eef1ef; }
        .pos-result:last-child { border-bottom: 0; }
        .pos-result:hover, .pos-result:focus { background: #f0f6f2; outline: 0; }
        .pos-result-name { color: #193c32; font-size: .84rem; font-weight: 700; }
        .pos-result-code, .pos-result-stock { color: #708079; font-size: .72rem; }
        .pos-result-price { color: #28745f; font-size: .8rem; font-weight: 700; white-space: nowrap; }
        .pos-label { display: block; margin-bottom: .35rem; color: #53635d; font-size: .72rem; font-weight: 700; }
        .pos-table-wrap { overflow-x: auto; }
        .pos-table { min-width: 620px; margin: 0; }
        .pos-table thead th { color: #687770; background: #f5f7f5; font-size: .7rem; text-transform: uppercase; white-space: nowrap; }
        .pos-table td { vertical-align: middle; font-size: .8rem; }
        .pos-product-name { color: #1b3a31; font-weight: 700; }
        .pos-product-code { color: #7a8983; font-size: .69rem; }
        .pos-qty { display: flex; align-items: center; justify-content: center; gap: .3rem; }
        .pos-qty button, .pos-remove { display: inline-flex; width: 30px; height: 30px; align-items: center; justify-content: center; padding: 0; border: 1px solid #d6dfda; border-radius: 5px; background: #fff; color: #244b3e; }
        .pos-qty input { width: 48px; height: 30px; padding: .2rem; text-align: center; }
        .pos-empty { height: 160px; color: #87928d; text-align: center; }
        .pos-summary { position: sticky; top: 1rem; }
        .pos-summary-row { display: flex; align-items: center; justify-content: space-between; gap: .65rem; padding: .3rem 0; color: #697871; font-size: .78rem; }
        .pos-summary-row strong { color: #263d35; font-size: .81rem; }
        .pos-summary-row input { width: 108px; height: 32px; text-align: right; }
        .pos-total { margin-top: .8rem; padding: .85rem; border-radius: 6px; background: #edf4ef; }
        .pos-total-label, .pos-change-label { color: #5b7167; font-size: .68rem; font-weight: 700; }
        .pos-total-value { color: #173f35; font-size: 1.65rem; font-weight: 700; line-height: 1.2; }
        .pos-payment { margin-top: .8rem; padding: .85rem; border: 1px solid #cfdfd5; border-radius: 6px; background: #f6faf7; }
        .pos-payment-input { height: 48px; font-size: 1.2rem; font-weight: 700; text-align: right; }
        .pos-methods { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .35rem; }
        .pos-method { padding: .45rem .2rem; border: 1px solid #d5dfd9; border-radius: 5px; background: #fff; color: #43564d; font-size: .7rem; }
        .pos-method.active { border-color: #28745f; background: #28745f; color: #fff; }
        .pos-change { margin-top: .75rem; padding: .65rem .75rem; border-radius: 5px; background: #d9eee3; }
        .pos-change.short { background: #f8e3df; }
        .pos-change-value { color: #1c6048; font-size: 1.2rem; font-weight: 700; }
        .pos-change.short .pos-change-value { color: #a83e32; }
        .pos-quick-cash { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .6rem; }
        .pos-quick-cash button { padding: .3rem .5rem; border: 1px solid #cad9d0; border-radius: 4px; background: #fff; color: #345447; font-size: .68rem; }
        .pos-actions { display: grid; grid-template-columns: 1fr 1fr; gap: .45rem; margin-top: .8rem; }
        .pos-actions .btn { padding: .65rem .4rem; font-size: .76rem; }
        .pos-actions .pos-pay { grid-column: 1 / -1; padding: .8rem; background: #28745f; border-color: #28745f; font-weight: 700; }
        .pos-held { display: none; margin-top: .65rem; border-top: 1px solid #edf0ee; }
        .pos-held-item { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .6rem 0; border-bottom: 1px solid #edf0ee; font-size: .75rem; }
        .pos-shortcuts { margin-top: 1rem; color: #87928d; font-size: .68rem; }
        @media (max-width: 767.98px) {
            .pos-page { padding: 0 .75rem 1rem; }
            .pos-heading { align-items: flex-start; }
            .pos-meta { max-width: 48%; overflow-wrap: anywhere; }
            .pos-summary { position: static; }
        }
        @media print {
            .pos-page { padding: 0; }
            .pos-heading, .pos-search-card, .pos-summary, .pos-shortcuts, #accordionSidebar, .topbar { display: none !important; }
        }
    </style>
@endpush

@section('content')
    <main class="pos-page" id="posApp" data-search-url="{{ route('transactions.products') }}" data-has-errors="{{ $errors->any() ? '1' : '0' }}">
        <header class="pos-heading">
            <div>
                <h1>Kasir · Transaksi Baru</h1>
                <div class="small text-muted">TOKO RETAIL MAKMUR · Jember</div>
            </div>
            <div class="pos-meta">No. Transaksi<strong id="transactionNumber">{{ $transactionNumber }}</strong><span id="currentDate"></span></div>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Transaksi belum diproses.</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="checkoutForm" method="POST" action="{{ route('transactions.store') }}">
            @csrf
            <input type="hidden" name="transaction_number" value="{{ $transactionNumber }}">
            <div class="row">
                <section class="col-lg-8">
                    <div class="card pos-search-card mb-3">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <span>Tambah Barang</span>
                            <button id="heldToggle" class="btn btn-sm btn-outline-secondary" type="button" aria-expanded="false"><i class="fas fa-pause mr-1" aria-hidden="true"></i>Ditahan <span id="heldCount">0</span></button>
                        </div>
                        <div class="card-body">
                            <div class="pos-search-row">
                                <div class="position-relative flex-grow-1">
                                    <input id="searchProduct" class="form-control" type="search" placeholder="Cari nama, kode, atau scan barcode..." autocomplete="off" aria-label="Cari produk" aria-controls="productResults">
                                    <div id="productResults" class="pos-results" role="listbox"></div>
                                </div>
                                <button id="searchButton" class="btn btn-primary" type="button" aria-label="Cari produk" title="Cari produk"><i class="fas fa-search" aria-hidden="true"></i></button>
                            </div>
                            <div class="pos-held" id="heldList" aria-live="polite"></div>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <label class="pos-label" for="customerName">Pelanggan</label>
                                    <input id="customerName" name="customer_name" class="form-control" type="text" value="{{ old('customer_name', 'Umum') }}" maxlength="255" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="pos-label" for="customerPhone">No. Member / HP</label>
                                    <input id="customerPhone" name="customer_phone" class="form-control" type="tel" value="{{ old('customer_phone') }}" maxlength="30" placeholder="Opsional">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <span>Keranjang Belanja</span>
                            <span id="itemCount">0 Item</span>
                        </div>
                        <div class="pos-table-wrap">
                            <table class="table pos-table">
                                <thead><tr><th>No</th><th>Barang</th><th class="text-right">Harga</th><th class="text-center">Qty</th><th class="text-right">Subtotal</th><th></th></tr></thead>
                                <tbody id="cartBody"><tr><td colspan="6" class="pos-empty">Keranjang masih kosong.<br>Silakan cari atau scan barang.</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <aside class="col-lg-4 mt-3 mt-lg-0">
                    <div class="card pos-summary">
                        <div class="card-header">Ringkasan Pembayaran</div>
                        <div class="card-body">
                            <div class="pos-summary-row"><span>Total Item</span><strong id="totalQty">0</strong></div>
                            <div class="pos-summary-row"><span>Subtotal</span><strong id="subtotal">Rp0</strong></div>
                            <div class="pos-summary-row"><label for="discountPercent">Diskon (%)</label><input id="discountPercent" name="discount_percent" class="form-control form-control-sm" type="number" min="0" max="100" step="0.01" value="{{ old('discount_percent', 0) }}"></div>
                            <div class="pos-summary-row"><label for="discountAmount">Diskon (Rp)</label><input id="discountAmount" name="discount_amount" class="form-control form-control-sm" type="number" min="0" step="1" value="{{ old('discount_amount', 0) }}"></div>
                            <div class="pos-summary-row"><label for="tax">Pajak / PPN</label><input id="tax" name="tax" class="form-control form-control-sm" type="number" min="0" step="1" value="{{ old('tax', 0) }}"></div>
                            <div class="pos-summary-row"><label for="otherFee">Biaya lain</label><input id="otherFee" name="other_fee" class="form-control form-control-sm" type="number" min="0" step="1" value="{{ old('other_fee', 0) }}"></div>
                            <div class="pos-total"><div class="pos-total-label">TOTAL AKHIR</div><div class="pos-total-value" id="grandTotal">Rp0</div></div>

                            <div class="pos-payment">
                                <label class="pos-label" for="payment">Uang Dibayar</label>
                                <input id="payment" name="paid_amount" class="form-control pos-payment-input" type="number" min="0" step="1" value="{{ old('paid_amount', 0) }}" required>
                                <div class="pos-quick-cash" id="quickCash"></div>
                                <div class="pos-label mt-3">Metode Pembayaran</div>
                                <input id="paymentMethodInput" name="payment_method" type="hidden" value="{{ old('payment_method', 'Tunai') }}">
                                <div class="pos-methods" role="group" aria-label="Metode pembayaran">
                                    @foreach (['Tunai', 'QRIS', 'Debit', 'Kredit', 'E-Wallet', 'Transfer'] as $method)
                                        <button class="pos-method {{ old('payment_method', 'Tunai') === $method ? 'active' : '' }}" type="button" data-method="{{ $method }}" aria-pressed="{{ old('payment_method', 'Tunai') === $method ? 'true' : 'false' }}">{{ $method }}</button>
                                    @endforeach
                                </div>
                                <div class="pos-change" id="changeBox"><div class="pos-change-label" id="changeLabel">KEMBALIAN</div><div class="pos-change-value" id="change">Rp0</div></div>
                            </div>

                            <div id="checkoutItems"></div>
                            <div class="pos-actions">
                                <button id="holdButton" class="btn btn-warning" type="button"><i class="fas fa-pause mr-1" aria-hidden="true"></i>Tahan</button>
                                <button id="cancelButton" class="btn btn-danger" type="button"><i class="fas fa-times mr-1" aria-hidden="true"></i>Batal</button>
                                <button class="btn btn-success pos-pay" type="submit"><i class="fas fa-cash-register mr-1" aria-hidden="true"></i>BAYAR &amp; CETAK</button>
                            </div>
                            <div class="pos-shortcuts">F2 Cari Barang · F4 Bayar · Esc Tutup Pencarian</div>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('admin_assets/js/cashier-pos.js') }}" defer></script>
@endpush