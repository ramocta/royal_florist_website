
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pilih Metode Pesanan – Royal Florist</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
  --maroon:#8a1f43;
  --dark:#651529;
  --blush:#fdeef1;
  --line:#e9c3cd;
  --text:#2a1a1e;
}

* { box-sizing:border-box; margin:0; padding:0; }

body {
  font-family:'Inter',sans-serif;
  color:var(--text);
  font-size:13px;
  min-height:100vh;
  display:flex;
  flex-direction:column;
  background:linear-gradient(#fdf3f5,#f3dbe2);
}

h1,h2,h3,.logo,.total { font-family:'Playfair Display',serif; }

header,footer {
  background:linear-gradient(#8a2347,#651529);
  color:white;
  display:flex;
  align-items:center;
  justify-content:space-between;
}

header { height:66px; padding:0 48px; }
.logo { font-size:21px; font-weight:600; }
.steps { display:flex; gap:18px; align-items:center; color:#f3d6dd; }
.steps .on { color:white; font-weight:700; }

.pill {
  background:white;
  color:var(--maroon);
  border:0;
  border-radius:999px;
  padding:9px 28px;
  cursor:pointer;
}

main {
  flex:1;
  width:100%;
  max-width:1260px;
  margin:auto;
  padding:34px 24px 60px;
}

h1 { font-size:32px; color:var(--maroon); }
.sub { color:var(--maroon); margin:2px 0 20px; }

.cols {
  display:grid;
  grid-template-columns:1fr 385px;
  gap:35px;
  align-items:start;
}

.card {
  background:white;
  border-radius:16px;
  padding:20px 23px;
  margin-bottom:19px;
}

.card h3 { font-size:17px; margin-bottom:10px; }

.types {
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:21px;
}

.type {
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

.type.active,.addr,.pay {
  border:2px solid var(--maroon);
  background:var(--blush);
}

.type b { display:block; }
.type small { color:#7a5a62; font-size:10.5px; }
.type .txt { flex:1; }

.radio {
  width:18px;
  height:18px;
  border-radius:50%;
  border:1px solid var(--line);
}

.type.active .radio {
  background:var(--maroon);
  box-shadow:inset 0 0 0 5px var(--maroon),inset 0 0 0 7px white;
}

.addr { border-radius:10px; padding:14px 17px; }
.addr .row { display:flex; align-items:center; gap:14px; margin-bottom:12px; }
.tag,.badge {
  background:var(--maroon);
  color:white;
  font-weight:700;
  border-radius:4px;
  padding:4px 14px;
  font-size:10px;
}

.ghost {
  margin-left:auto;
  background:white;
  color:var(--maroon);
  border:1px solid var(--maroon);
  border-radius:999px;
  padding:6px 26px;
  cursor:pointer;
}

.addr small { display:block; margin-top:6px; color:#6f5a5f; }

.note {
  background:#fdf0c4;
  border:1px solid #f1dc92;
  border-radius:8px;
  padding:13px 20px;
  color:#7a5a00;
  display:flex;
  gap:28px;
  margin-bottom:16px;
}

.pay {
  border-radius:10px;
  padding:14px 20px;
  display:flex;
  gap:20px;
}

.pay .ico { font-size:28px; }
.pay h4 { font-size:15px; margin-bottom:3px; }
.pay .meta { display:flex; justify-content:space-between; gap:16px; color:#6f5a5f; }
.pay .meta b { color:var(--maroon); white-space:nowrap; }

.badge {
  display:inline-block;
  margin-top:8px;
  padding:5px 28px;
  border-radius:999px;
}

.sum { padding:0; overflow:hidden; }
.sum h2 {
  background:linear-gradient(#8a2347,#651529);
  color:white;
  font-size:17px;
  padding:20px 23px;
}

.sum .body { padding:20px 23px 24px; }
.line { display:flex; justify-content:space-between; color:var(--maroon); margin-bottom:12px; }
.line b { color:var(--text); }

hr { border:0; border-top:1px solid var(--line); margin:14px 0; }

.total { font-size:30px; font-weight:700; color:var(--maroon); margin:2px 0 14px; }

.cta {
  width:100%;
  height:50px;
  border:0;
  border-radius:999px;
  color:white;
  font:600 14px 'Inter',sans-serif;
  background:linear-gradient(#9a2750,#6b1630);
  cursor:pointer;
}

.hint { text-align:center; font-size:11px; color:#6f5a5f; margin-top:14px; line-height:1.6; }

footer { height:66px; padding:0 70px; font-size:11px; }
footer .logo { font-size:15px; }

button:focus-visible { outline:2px solid var(--maroon); outline-offset:2px; }

@media(max-width:900px) {
  .cols { grid-template-columns:1fr; }
  .steps { display:none; }
  header,footer { padding:0 20px; }
  .types { grid-template-columns:1fr; }
  .note,.pay .meta { flex-direction:column; gap:6px; }
}
</style>
</head>

<body>
<header>
  <div class="logo">🌸 Royal Florist</div>
  <nav class="steps">
    <span>1. Keranjang</span>→
    <span class="on">2. Metode Pesanan</span>→
    <span>3. Konfirmasi</span>
  </nav>
  <button class="pill">👤 Profil Saya</button>
</header>

<main>
  <h1>Pilih Metode Pengambilan / Pengiriman</h1>
  <p class="sub">Silakan tentukan apakah pesanan dikirim ke alamat Anda atau diambil langsung di toko kami</p>

  @if (session('success'))
    <div class="card" style="color: green;">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="card" style="color: red;">
      <ul>
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

    <div class="cols">
      <div>
        <section class="card">
          <h3>Tipe Layanan Pesanan:</h3>
          <div class="types">
            <button type="button" class="type active" data-type="dikirim" aria-pressed="true">
              🚚
              <span class="txt"><b>Dikirim Kurir Florist</b><small>Wajib Bayar QRIS Sblm Diproses</small></span>
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
          <div class="addr">
            <div class="row">
              <span class="tag">UTAMA</span>
              <b>{{ $pelanggan->nama ?? '-' }}</b>
              <span>({{ $pelanggan->nmr_telepon ?? '-' }})</span>
              <button type="button" class="ghost">Ubah Alamat</button>
            </div>
            <p>{{ $pelanggan->alamat ?? '-' }}</p>
            <small>Catatan: Pagar putih dekat minimarket</small>
          </div>
        </section>

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
          <div class="line"><span>Subtotal Produk (3 Item)</span><b>Rp 865.000</b></div>
          <div class="line"><span>Biaya Kurir Toko</span><b id="ship">Rp 20.000</b></div>
          <hr>
          <h3 style="font-size:15px">Total Wajib Bayar</h3>
          <div class="total" id="total">Rp 885.000</div>
          <button type="submit" class="cta" id="cta">Lanjut Bayar QRIS →</button>
          <p class="hint">📲 Halaman berikutnya akan menampilkan QRIS<br>&amp; tombol unggah bukti transaksi.</p>
        </div>
      </aside>
    </div>
  </form>
</main>

<footer>
  <div class="logo">Royal Florist</div>
  <span>© 2026 Royal Florist. All Rights Reserved.</span>
</footer>

<script>
const SUBTOTAL = 865000, COURIER = 20000;
const rp = n => 'Rp ' + n.toLocaleString('id-ID');

document.querySelectorAll('.type').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.type').forEach(b => {
      b.classList.remove('active');
      b.setAttribute('aria-pressed', 'false');
    });

    btn.classList.add('active');
    btn.setAttribute('aria-pressed', 'true');

    const delivery = btn.dataset.type === 'dikirim';
    const ship = delivery ? COURIER : 0;

    document.getElementById('metodePesanan').value = btn.dataset.type;

    document.getElementById('metodePembayaran').value =
      delivery ? 'QRIS' : 'bayar_di_toko';

    document.getElementById('addrCard').style.display =
      delivery ? '' : 'none';

    document.getElementById('ship').textContent = rp(ship);
    document.getElementById('total').textContent = rp(SUBTOTAL + ship);

    document.getElementById('cta').textContent =
      delivery ? 'Lanjut Bayar QRIS →' : 'Lanjut Konfirmasi →';
  });
});
</script>
</body>
</html>
