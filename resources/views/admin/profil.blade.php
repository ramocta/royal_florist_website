<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profil & Pengaturan Akun – Royal Florist</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
:root {
  --maroon:#7a1a33;
  --dark:#651529;
  --blush:#fce8ea;
  --soft:#fdf3f4;
  --line:#f1dde0;
  --text:#2a1a1e;
  --muted:#6f5a5f;
  --white:#fff;
}

* { box-sizing:border-box; margin:0; padding:0; }

body {
  font-family:'Poppins',sans-serif;
  color:var(--text);
  background:var(--white);
  font-size:13px;
}

button,input { font-family:inherit; }

.topbar {
  height:68px;
  background:var(--blush);
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:0 32px;
  position:fixed;
  inset:0 0 auto;
  z-index:10;
}

.brand { color:var(--maroon); }
.brand b { font:600 24px 'Playfair Display'; }
.top-right,.user { display:flex; align-items:center; gap:15px; }

.sidebar {
  position:fixed;
  top:68px;
  bottom:0;
  width:230px;
  background:var(--maroon);
  padding:26px 14px;
}

.sidebar nav,.menu,.stack {
  display:flex;
  flex-direction:column;
  gap:8px;
}

.sidebar a {
  color:#f7dfe3;
  text-decoration:none;
  padding:10px 16px;
  border-radius:10px;
}

.sidebar a.active {
  background:white;
  color:var(--maroon);
}

main {
  margin-left:230px;
  padding:98px 32px 40px;
  background:var(--soft);
  min-height:100vh;
}

.page-head {
  display:flex;
  justify-content:space-between;
  margin-bottom:22px;
}

h1,.form-card h3 {
  font-family:'Playfair Display',serif;
  color:var(--maroon);
}

.btn {
  padding:11px 15px;
  border:1px solid var(--maroon);
  border-radius:8px;
  cursor:pointer;
}

.btn-fill {
  background:var(--maroon);
  color:white;
}

.btn-line {
  background:white;
  color:var(--maroon);
}

.layout {
  display:grid;
  grid-template-columns:360px 1fr;
  gap:24px;
}

.card {
  background:white;
  border:1px solid var(--line);
  border-radius:16px;
}

.profile {
  padding:34px 24px;
  text-align:center;
}

.photo {
  width:72px;
  height:72px;
  margin:0 auto 20px;
  border-radius:50%;
  background:var(--maroon);
}

.badge {
  display:inline-block;
  margin-top:10px;
  padding:4px 16px;
  background:var(--blush);
  color:var(--maroon);
  border-radius:20px;
}

.menu {
  margin-top:22px;
  padding-top:14px;
  border-top:1px solid var(--line);
}

.menu button {
  padding:14px;
  border:0;
  border-radius:10px;
  background:white;
  text-align:left;
  cursor:pointer;
}

.menu button.active {
  background:var(--blush);
  color:var(--maroon);
}

.stack { gap:24px; }

.form-card { padding:24px; }

.form-card h3 {
  font-size:17px;
  padding-bottom:14px;
  margin-bottom:16px;
  border-bottom:1px solid var(--line);
}

.grid {
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:16px;
}

.full { grid-column:1/-1; }

label {
  display:block;
  font-weight:600;
  margin-bottom:6px;
}

input {
  width:100%;
  height:42px;
  padding:10px;
  border:1px solid var(--line);
  border-radius:8px;
}

.actions {
  display:flex;
  justify-content:flex-end;
  margin-top:22px;
}

.msg { margin-top:8px; }

@media(max-width:1000px) {
  .layout { grid-template-columns:1fr; }
}

@media(max-width:760px) {
  .sidebar { display:none; }
  main { margin:0; padding:90px 16px; }
  .grid { grid-template-columns:1fr; }
  .page-head { flex-direction:column; gap:14px; }
}
</style>
</head>

<body>

<header class="topbar">
  <div class="brand">
    <b>Royal Florist</b>
    <small>Fresh Flower • Special Moment</small>
  </div>
  <div class="top-right">
    <span>16 - 22 September 2026</span>
    <div class="user">Admin</div>
  </div>
</header>

<aside class="sidebar">
  <nav>
    <a href="#">Dashboard</a>
    <a href="#">Laporan Penjualan</a>
    <a href="#">Pelanggan</a>
    <a href="#">Pesanan</a>
    <a href="#">Produk</a>
    <a href="#">Stok</a>
    <a href="#">Manajemen Staff</a>
    <a href="#" class="active">Profil & Pengaturan Saya</a>
  </nav>
</aside>

<main>
  <div class="page-head">
    <div>
        <h1>Profil & Pengaturan Akun Saya</h1>
        <p>Kelola informasi akun dan kata sandi Anda.</p>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">

        <a href="{{ route('admin.dashboard') }}"
           class="btn btn-line"
           style="text-decoration:none; display:inline-block;">
            ← Kembali ke Dashboard
        </a>

        <button type="submit" form="formProfil" class="btn btn-fill">
            Simpan Perubahan
        </button>

    </div>
</div>

  <div class="layout">
    <section class="card profile">
      <div class="photo"></div>
      <h2 id="fullName">Siska Amelia</h2>
      <p>siska.amelia@royalflorist.com</p>
      <span class="badge">ADMIN TOKO</span>

      <div class="menu">
        <button class="active" data-target="sec-data">Data Diri Saya</button>
        <button data-target="sec-pass">Ubah Kata Sandi</button>
      </div>
    </section>

    <div class="stack">
      <section class="card form-card" id="sec-data">
        <h3>Informasi Data Diri Saya</h3>
        <div class="grid">
          <div>
            <label>Nama Depan</label>
            <input id="first" value="Siska">
          </div>
          <div>
            <label>Nama Belakang</label>
            <input id="last" value="Amelia">
          </div>
          <div>
            <label>Email</label>
            <input type="email" value="siska.amelia@royalflorist.com">
          </div>
          <div>
            <label>Nomor Telepon</label>
            <input type="tel" value="+62 812-3456-7890">
          </div>
          <div class="full">
            <label>Jabatan</label>
            <input value="Penanggung Jawab Penjualan & Operasional">
          </div>
        </div>
      </section>

      <section class="card form-card" id="sec-pass">
        <h3>Ubah Kata Sandi</h3>
        <div class="grid">
          <div class="full">
            <label>Kata Sandi Saat Ini</label>
            <input type="password">
          </div>
          <div>
            <label>Kata Sandi Baru</label>
            <input id="newPass" type="password">
          </div>
          <div>
            <label>Konfirmasi Kata Sandi</label>
            <input id="confPass" type="password">
          </div>
        </div>
        <div class="msg" id="passMsg"></div>
        <div class="actions">
          <button class="btn btn-line" id="passBtn">Update Password</button>
        </div>
      </section>
    </div>
  </div>
</main>

<script>
document.querySelectorAll('.menu button').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.menu button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(btn.dataset.target)
      .scrollIntoView({behavior:'smooth'});
  });
});

document.getElementById('saveBtn').addEventListener('click', function() {
  const name = document.getElementById('first').value + ' ' +
               document.getElementById('last').value;
  document.getElementById('fullName').textContent = name.trim();
  this.textContent = 'Tersimpan';
});

document.getElementById('passBtn').addEventListener('click', () => {
  const n = document.getElementById('newPass').value;
  const c = document.getElementById('confPass').value;
  const msg = document.getElementById('passMsg');

  if (n.length < 8) {
    msg.textContent = 'Kata sandi minimal 8 karakter.';
  } else if (n !== c) {
    msg.textContent = 'Konfirmasi kata sandi tidak sama.';
  } else {
    msg.textContent = 'Kata sandi berhasil diperbarui.';
  }
});
</script>

</body>
</html>