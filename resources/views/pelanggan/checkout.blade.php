@extends('layouts.pelanggan')

@section('title', 'Checkout | Royal Florist')

@push('head')
<style>
.checkout-content {
  --maroon:#8a1f43;
  --dark:#651529;
  --blush:#fdeef1;
  --line:#e9c3cd;
  --text:#2a1a1e;
  width:100%;
  max-width:1260px;
  margin:0 auto;
  padding:34px 24px 60px;
  color:var(--text);
  font-family:'Inter',sans-serif;
  font-size:13px;
}
.checkout-content h1,
.checkout-content h2,
.checkout-content h3,
.checkout-content .total {
  font-family:'Playfair Display',serif;
}
.checkout-content h1 { font-size:32px;color:var(--maroon); }
.checkout-content .sub { color:var(--maroon);margin:2px 0 20px; }
.checkout-content .cols {
  display:grid;
  grid-template-columns:1fr 385px;
  gap:35px;
  align-items:start;
}
.checkout-content .card {
  background:white;
  border-radius:16px;
  padding:20px 23px;
  margin-bottom:19px;
}
.checkout-content .card h3 { font-size:17px;margin-bottom:10px; }
.checkout-content .types {
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:21px;
}
.checkout-content .type {
  display:flex;
  align-items:center;
  gap:14px;
  padding:0 20px;
  height:49px;
  border:1px solid var(--line);
  border-radius:10px;
  background:white;
  cursor:pointer;
  text-align:left;
  font:inherit;
}
.checkout-content .type.active,
.checkout-content .addr,
.checkout-content .pay {
  border:2px solid var(--maroon);
  background:var(--blush);
}
.checkout-content .type b { display:block; }
.checkout-content .type small { color:#7a5a62;font-size:10.5px; }
.checkout-content .type .txt { flex:1; }
.checkout-content .radio {
  width:18px;height:18px;border-radius:50%;
  border:1px solid var(--line);flex-shrink:0;
}
.checkout-content .type.active .radio {
  background:var(--maroon);
  box-shadow:inset 0 0 0 5px var(--maroon),inset 0 0 0 7px white;
}
.checkout-content .tag,
.checkout-content .badge {
  background:var(--maroon);
  color:white;
  font-weight:700;
  border-radius:4px;
  padding:4px 14px;
  font-size:10px;
}
.checkout-content .note {
  background:#fdf0c4;
  border:1px solid #f1dc92;
  border-radius:8px;
  padding:13px 20px;
  color:#7a5a00;
  display:flex;
  gap:28px;
  margin-bottom:16px;
}
.checkout-content .pay {
  border-radius:10px;padding:14px 20px;
  display:flex;gap:20px;
}
.checkout-content .pay .ico { font-size:28px; }
.checkout-content .pay h4 { font-size:15px;margin-bottom:3px; }
.checkout-content .pay .meta {
  display:flex;justify-content:space-between;
  gap:16px;color:#6f5a5f;
}
.checkout-content .pay .meta b { color:var(--maroon);white-space:nowrap; }
.checkout-content .badge {
  display:inline-block;margin-top:8px;
  padding:5px 28px;border-radius:999px;
}
.checkout-content .sum { padding:0;overflow:hidden; }
.checkout-content .sum h2 {
  background:linear-gradient(#8a2347,#651529);
  color:white;font-size:17px;padding:20px 23px;
}
.checkout-content .sum .body { padding:20px 23px 24px; }
.checkout-content .line {
  display:flex;justify-content:space-between;
  gap:12px;color:var(--maroon);margin-bottom:12px;
}
.checkout-content .line b { color:var(--text);white-space:nowrap; }
.checkout-content hr {
  border:0;border-top:1px solid var(--line);margin:14px 0;
}
.checkout-content .total {
  font-size:30px;font-weight:700;
  color:var(--maroon);margin:2px 0 14px;
}
.checkout-content .cta {
  width:100%;height:50px;border:0;border-radius:999px;
  color:white;font:600 14px 'Inter',sans-serif;
  background:linear-gradient(#9a2750,#6b1630);
  cursor:pointer;
}
.checkout-content .cta:disabled {
  opacity:.5;cursor:not-allowed;
}
.checkout-content .hint {
  text-align:center;font-size:11px;color:#6f5a5f;
  margin-top:14px;line-height:1.6;
}
.checkout-content .address-add-btn {
  width:100%;border:1px dashed var(--maroon);
  background:#fff8fa;color:var(--maroon);
  border-radius:9px;padding:12px;margin-bottom:15px;
  cursor:pointer;font:600 13px 'Inter',sans-serif;
}
.checkout-content .address-form {
  display:none;background:white;
  border:1px solid var(--line);border-radius:12px;
  padding:18px;margin:12px 0;
}
.checkout-content .address-form.show { display:block; }
.checkout-content .address-form h4 {
  color:var(--maroon);font-size:15px;margin-bottom:13px;
}
.checkout-content .address-form input,
.checkout-content .address-form textarea {
  display:block;width:100%;padding:11px;
  border:1px solid var(--line);border-radius:8px;
  margin-bottom:10px;font:inherit;
}
.checkout-content .address-form textarea { min-height:80px;resize:vertical; }
.checkout-content .address-form label {
  display:flex;align-items:center;gap:8px;margin:10px 0;
}
.checkout-content .address-form label input { width:auto;margin:0; }
.checkout-content .address-actions {
  display:flex;flex-wrap:wrap;gap:8px;margin-top:14px;
}
.checkout-content .address-btn {
  border:1px solid var(--maroon);background:white;
  color:var(--maroon);border-radius:8px;padding:8px 13px;
  cursor:pointer;font:600 11px 'Inter',sans-serif;
}
.checkout-content .address-btn.primary { background:var(--maroon);color:white; }
.checkout-content .address-btn.delete { border-color:#dc3545;color:#dc3545; }
.checkout-content .address-btn.cancel { border-color:#999;color:#555; }
.checkout-content .address-item {
  background:#fff;border:1px solid var(--line);
  border-radius:10px;padding:14px;margin-bottom:12px;
}
.checkout-content .address-item p { line-height:1.6; }
.checkout-content .address-radio {
  display:flex;align-items:center;gap:10px;
  margin-bottom:10px;cursor:pointer;
}
.checkout-content .address-radio input { accent-color:var(--maroon); }
.checkout-content .empty-address {
  background:#fff8fa;border:1px dashed var(--line);
  border-radius:10px;padding:18px;margin-bottom:12px;
}
.checkout-content .checkout-product {
  display:flex;justify-content:space-between;
  gap:12px;padding:10px 0;
  border-bottom:1px solid var(--line);
}
.checkout-content .checkout-product:last-child { border-bottom:0; }
.checkout-content .product-info { min-width:0; }
.checkout-content .product-info b { display:block;color:var(--text); }
.checkout-content .product-info small { color:#6f5a5f; }
.checkout-content .product-price { white-space:nowrap;font-weight:600;color:var(--text); }
.checkout-content .loading-cart,
.checkout-content .empty-cart {
  padding:15px;background:#fff8fa;
  border:1px dashed var(--line);border-radius:9px;
  margin-bottom:15px;
}
.checkout-content button:focus-visible {
  outline:2px solid var(--maroon);outline-offset:2px;
}
@media(max-width:900px) {
  .checkout-content .cols { grid-template-columns:1fr; }
  .checkout-content .types { grid-template-columns:1fr; }
  .checkout-content .note,
  .checkout-content .pay .meta { flex-direction:column;gap:6px; }
}
@media(max-width:600px) {
  .checkout-content { padding:24px 15px 40px; }
  .checkout-content h1 { font-size:25px; }
  .checkout-content .card { padding:17px; }
  .checkout-content .sum .body { padding:17px; }
}
</style>
@endpush

@section('content')
<div class="checkout-content">

  <h1>Pilih Metode Pengambilan / Pengiriman</h1>
  <p class="sub">Silakan tentukan apakah pesanan dikirim ke alamat Anda atau diambil langsung di toko kami</p>

  @if (session('success'))
    <div class="card" style="color:green;">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="card" style="color:red;">
      <ul style="padding-left:20px;">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('pelanggan.checkout.store') }}" method="POST" id="checkoutForm">
    @csrf
    <input type="hidden" name="jenis_pengambilan" id="metodePesanan" value="dikirim">
    <input type="hidden" name="metode_pembayaran" id="metodePembayaran" value="QRIS">
    <input type="hidden" name="cart_items" id="cartItemsInput">
  </form>

  <div class="cols">
    <div>

      <section class="card">
        <h3>Tipe Layanan Pesanan:</h3>
        <div class="types">
          <button type="button" class="type active" data-type="dikirim" aria-pressed="true">
            🚚
            <span class="txt"><b>Dikirim Kurir Florist</b><small>Wajib Bayar QRIS Sebelum Diproses</small></span>
            <span class="radio"></span>
          </button>

          <button type="button" class="type" data-type="ambil_di_toko" aria-pressed="false">
            🏪
            <span class="txt"><b>Ambil Sendiri di Toko</b><small>Bayar di Kasir Toko Saat Ambil</small></span>
            <span class="radio"></span>
          </button>
        </div>
      </section>

      <section class="card" id="addrCard">
        <h3>📍 Alamat Tujuan Pengiriman</h3>

        @forelse ($daftarAlamat as $alamat)
          <div class="address-item">
            <label class="address-radio">
              <input
                type="radio"
                name="id_alamat"
                form="checkoutForm"
                value="{{ $alamat->id_alamat }}"
                required
                @checked(old('id_alamat', optional($daftarAlamat->firstWhere('is_utama', true) ?? $daftarAlamat->first())->id_alamat) == $alamat->id_alamat)
              >
              <b>{{ $alamat->nama_penerima }}</b>
              <span>({{ $alamat->nomor_hp }})</span>
              @if ($alamat->is_utama)
                <span class="tag">UTAMA</span>
              @endif
            </label>
            <p>{{ $alamat->alamat_lengkap }}, {{ $alamat->kota }}</p>
            @if ($alamat->catatan)
              <small>Catatan: {{ $alamat->catatan }}</small>
            @endif
          </div>
        @empty
          <div class="empty-address">
            <p>Belum ada alamat pengiriman.</p>
            <small>Silakan tambahkan alamat terlebih dahulu.</small>
          </div>
        @endforelse

        <button type="button" class="address-add-btn" id="showAddForm">
          + Tambah Alamat Baru
        </button>

        <form action="{{ route('pelanggan.alamat-pengiriman.store') }}" method="POST"
              class="address-form" id="addAddressForm">
          @csrf
          <h4>Tambah Alamat Pengiriman</h4>
          <input type="text" name="nama_penerima" placeholder="Nama Penerima" maxlength="100" required>
          <input type="text" name="nomor_hp" placeholder="Nomor HP" maxlength="20" required>
          <textarea name="alamat_lengkap" placeholder="Alamat Lengkap (Nama jalan, nomor rumah, RT/RW, dan sebagainya)" required></textarea>
          <input type="text" name="kota" placeholder="Kota / Kabupaten" maxlength="100" required>
          <textarea name="catatan" placeholder="Catatan tambahan (opsional)"></textarea>
          <label>
            <input type="checkbox" name="is_utama" value="1">
            Jadikan alamat utama
          </label>
          <div class="address-actions">
            <button type="submit" class="address-btn primary">Simpan Alamat</button>
            <button type="button" class="address-btn cancel" data-close="addAddressForm">Batal</button>
          </div>
        </form>
      </section>

      @if ($daftarAlamat->isNotEmpty())
        <section class="card">
          <h3>⚙️ Kelola Alamat Tersimpan</h3>

          @foreach ($daftarAlamat as $alamat)
            <div class="address-item">
              <b>{{ $alamat->nama_penerima }}</b>
              <p>{{ $alamat->nomor_hp }}</p>
              <p>{{ $alamat->alamat_lengkap }}, {{ $alamat->kota }}</p>

              <div class="address-actions">
                <button type="button" class="address-btn" data-edit="{{ $alamat->id_alamat }}">Edit</button>

                @if (!$alamat->is_utama)
                  <form action="{{ route('pelanggan.alamat-pengiriman.utama', $alamat->id_alamat) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="address-btn primary">Jadikan Utama</button>
                  </form>
                @else
                  <span class="tag">Alamat Utama</span>
                @endif

                <form action="{{ route('pelanggan.alamat-pengiriman.destroy', $alamat->id_alamat) }}"
                      method="POST" onsubmit="return confirm('Yakin ingin menghapus alamat ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="address-btn delete">Hapus</button>
                </form>
              </div>

              <form action="{{ route('pelanggan.alamat-pengiriman.update', $alamat->id_alamat) }}"
                    method="POST" class="address-form" id="editForm{{ $alamat->id_alamat }}">
                @csrf
                @method('PUT')
                <h4>Edit Alamat Pengiriman</h4>
                <input type="text" name="nama_penerima" value="{{ $alamat->nama_penerima }}" placeholder="Nama Penerima" maxlength="100" required>
                <input type="text" name="nomor_hp" value="{{ $alamat->nomor_hp }}" placeholder="Nomor HP" maxlength="20" required>
                <textarea name="alamat_lengkap" placeholder="Alamat Lengkap" required>{{ $alamat->alamat_lengkap }}</textarea>
                <input type="text" name="kota" value="{{ $alamat->kota }}" placeholder="Kota / Kabupaten" maxlength="100" required>
                <textarea name="catatan" placeholder="Catatan tambahan">{{ $alamat->catatan }}</textarea>
                <label>
                  <input type="checkbox" name="is_utama" value="1" @checked($alamat->is_utama)>
                  Jadikan alamat utama
                </label>
                <div class="address-actions">
                  <button type="submit" class="address-btn primary">Simpan Perubahan</button>
                  <button type="button" class="address-btn cancel" data-close="editForm{{ $alamat->id_alamat }}">Batal</button>
                </div>
              </form>
            </div>
          @endforeach
        </section>
      @endif

      <section class="card">
        <h3>💳 Ketentuan Pembayaran Pesanan</h3>
        <div class="note">
          <b>Sistem Pengiriman:</b>
          <span>Bunga akan <b>mulai dirangkai &amp; dikirim</b> setelah Anda melakukan scan <b>QRIS</b> &amp; mengunggah bukti bayar.</span>
        </div>
        <div class="pay">
          <span class="ico">📱</span>
          <div style="flex:1">
            <h4>Pembayaran QRIS Instan (Upload Bukti)</h4>
            <div class="meta">
              <span>Mendukung GoPay, OVO, ShopeePay, Dana, BCA Mobile, Livin, BRImo, dll</span>
              <b>Bebas Biaya Admin</b>
            </div>
            <span class="badge">✓ Otomatis Di-Scan Kasir</span>
          </div>
        </div>
      </section>
    </div>

    <aside class="card sum">
      <h2>Ringkasan Tagihan</h2>
      <div class="body">

        <div id="checkoutProducts">
          <div class="loading-cart">Memuat produk dari keranjang...</div>
        </div>

        <div class="line">
          <span>Subtotal Produk</span>
          <b id="subtotal">Rp 0</b>
        </div>

        <div class="line">
          <span>Biaya Kurir Toko</span>
          <b id="ship">Rp 20.000</b>
        </div>

        <hr>
        <h3 style="font-size:15px">Total Wajib Bayar</h3>
        <div class="total" id="total">Rp 0</div>

        <button type="submit" form="checkoutForm" class="cta" id="cta" disabled>
          Memuat Keranjang...
        </button>

        <p class="hint" id="checkoutHint">
          📲 Halaman berikutnya akan menampilkan QRIS dan tombol unggah bukti transaksi.
        </p>
      </div>
    </aside>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {
  const DATA_URL = @json(route('pelanggan.keranjang.data'));
  const CART_KEY = 'rf_cart';
  const COURIER = 20000;

  let subtotalProduk = 0;
  let cartItems = {};
  let jenisPengambilan = 'dikirim';

  const rp = angka => 'Rp ' + Number(angka).toLocaleString('id-ID');

  const productsContainer = document.getElementById('checkoutProducts');
  const subtotalElement = document.getElementById('subtotal');
  const shipElement = document.getElementById('ship');
  const totalElement = document.getElementById('total');
  const cta = document.getElementById('cta');
  const hint = document.getElementById('checkoutHint');

  function updateTotal() {
    const ongkir = jenisPengambilan === 'dikirim' ? COURIER : 0;
    shipElement.textContent = rp(ongkir);
    totalElement.textContent = rp(subtotalProduk + ongkir);
    subtotalElement.textContent = rp(subtotalProduk);
  }

  function showEmptyCart(message) {
    productsContainer.innerHTML = '';
    const box = document.createElement('div');
    box.className = 'empty-cart';
    box.textContent = message;
    productsContainer.appendChild(box);

    subtotalProduk = 0;
    updateTotal();
    cta.disabled = true;
    cta.textContent = 'Keranjang Kosong';
  }

  async function loadCart() {
    try {
      const savedCart = localStorage.getItem(CART_KEY);
      cartItems = savedCart ? JSON.parse(savedCart) : {};

      if (!cartItems || typeof cartItems !== 'object' || Array.isArray(cartItems)) {
        cartItems = {};
      }

      const entries = Object.entries(cartItems)
        .filter(([id, qty]) => Number(id) > 0 && Number(qty) > 0);

      if (entries.length === 0) {
        showEmptyCart('Keranjang masih kosong. Silakan pilih produk terlebih dahulu.');
        return;
      }

      const ids = entries.map(([id]) => id);
      const params = new URLSearchParams();

      ids.forEach(id => params.append('ids[]', id));

      const response = await fetch(DATA_URL + '?' + params.toString(), {
        headers: { 'Accept': 'application/json' }
      });

      if (!response.ok) {
        throw new Error('Gagal mengambil data produk dari server.');
      }

      const products = await response.json();

      if (!Array.isArray(products) || products.length === 0) {
        showEmptyCart('Produk dalam keranjang tidak ditemukan.');
        return;
      }

      productsContainer.innerHTML = '';
      subtotalProduk = 0;

      const validItems = [];

      products.forEach(product => {
        const quantity = Number(cartItems[product.id] || 0);

        if (quantity <= 0) return;

        const price = Number(product.harga || 0);
        const totalItem = price * quantity;
        subtotalProduk += totalItem;

        validItems.push({
          id_product: Number(product.id),
          jumlah: quantity
        });

        const item = document.createElement('div');
        item.className = 'checkout-product';

        const info = document.createElement('div');
        info.className = 'product-info';

        const name = document.createElement('b');
        name.textContent = product.nama;

        const detail = document.createElement('small');
        detail.textContent = quantity + ' x ' + rp(price);

        info.appendChild(name);
        info.appendChild(detail);

        const priceElement = document.createElement('span');
        priceElement.className = 'product-price';
        priceElement.textContent = rp(totalItem);

        item.appendChild(info);
        item.appendChild(priceElement);

        productsContainer.appendChild(item);
      });

      if (validItems.length === 0) {
        showEmptyCart('Tidak ada produk valid di keranjang.');
        return;
      }

      document.getElementById('cartItemsInput').value = JSON.stringify(validItems);

      updateTotal();

      cta.disabled = false;
      cta.textContent = 'Lanjut Bayar QRIS →';

      hint.textContent = 'Periksa kembali produk dan alamat pesanan sebelum melanjutkan.';

    } catch (error) {
      console.error('Checkout error:', error);
      showEmptyCart('Gagal memuat keranjang. Silakan kembali ke halaman keranjang dan coba lagi.');
    }
  }

  // Pilihan metode pengambilan
  document.querySelectorAll('.checkout-content .type').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.checkout-content .type').forEach(button => {
        button.classList.remove('active');
        button.setAttribute('aria-pressed', 'false');
      });

      btn.classList.add('active');
      btn.setAttribute('aria-pressed', 'true');

      jenisPengambilan = btn.dataset.type;

      const delivery = jenisPengambilan === 'dikirim';

      document.getElementById('metodePesanan').value = jenisPengambilan;
      document.getElementById('metodePembayaran').value =
        delivery ? 'QRIS' : 'bayar_di_toko';

      document.getElementById('addrCard').style.display =
        delivery ? '' : 'none';

      document.querySelectorAll('input[name="id_alamat"]').forEach(input => {
        input.required = delivery;
        input.disabled = !delivery;
      });

      updateTotal();

      cta.textContent = delivery
        ? 'Lanjut Bayar QRIS →'
        : 'Lanjut Konfirmasi →';

      hint.textContent = delivery
        ? 'Halaman berikutnya akan menampilkan QRIS dan tombol unggah bukti transaksi.'
        : 'Pesanan akan dikonfirmasi untuk pembayaran langsung di toko.';
    });
  });

  // Validasi sebelum checkout dikirim
  document.getElementById('checkoutForm').addEventListener('submit', function (event) {
    if (Object.keys(cartItems).length === 0 || subtotalProduk <= 0) {
      event.preventDefault();
      alert('Keranjang masih kosong. Silakan pilih produk terlebih dahulu.');
      return;
    }

    if (jenisPengambilan === 'dikirim') {
      const selectedAddress = document.querySelector('input[name="id_alamat"]:checked');

      if (!selectedAddress) {
        event.preventDefault();
        alert('Silakan pilih alamat pengiriman terlebih dahulu.');
        return;
      }
    }
  });

  // Tampilkan form tambah alamat
  document.getElementById('showAddForm').addEventListener('click', () => {
    const form = document.getElementById('addAddressForm');
    form.classList.add('show');
    form.scrollIntoView({ behavior:'smooth', block:'center' });
  });

  // Tampilkan form edit alamat
  document.querySelectorAll('[data-edit]').forEach(button => {
    button.addEventListener('click', () => {
      const form = document.getElementById('editForm' + button.dataset.edit);
      form.classList.toggle('show');

      if (form.classList.contains('show')) {
        form.scrollIntoView({ behavior:'smooth', block:'center' });
      }
    });
  });

  // Tombol batal
  document.querySelectorAll('[data-close]').forEach(button => {
    button.addEventListener('click', () => {
      document.getElementById(button.dataset.close).classList.remove('show');
    });
  });

  await loadCart();
});
</script>
@endpush