@extends('layouts.pelanggan')

@section('title', 'Pengaturan Profil & Akun')

@push('head')
<style>
    :root {
        --rf-maroon-primary: #70132A;
        --rf-maroon-hover: #540D1E;
        --rf-maroon-light: #FFF0F3;
        --rf-maroon-accent: #A02344;
        --rf-border-radius: 16px;
    }

    body {
        background-color: #F8EDEF;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    }

    .profile-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 15px;
    }

    .profile-header-title {
        color: var(--rf-maroon-primary);
        font-weight: 700;
        font-size: 28px;
        margin-bottom: 4px;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }
    }

    /* --- Card Umum --- */
    .rf-card {
        background: #ffffff;
        border-radius: var(--rf-border-radius);
        box-shadow: 0 4px 20px rgba(112, 19, 42, 0.06);
        padding: 24px;
        position: relative;
    }

    /* --- Card Kiri (Sidebar Menu) --- */
    .profile-avatar-box {
        text-align: center;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }

    /* Container Avatar dengan Tombol Edit Overlay */
    .avatar-wrapper {
        position: relative;
        width: 90px;
        height: 90px;
        margin: 0 auto 12px auto;
    }

    .profile-avatar {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--rf-maroon-light);
        background-color: #eee;
    }

    .avatar-edit-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: var(--rf-maroon-primary);
        color: #fff;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        border: 2px solid #fff;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .avatar-edit-btn:hover {
        background-color: var(--rf-maroon-hover);
    }

    /* Navigasi Menu Kiri */
    .profile-nav {
        list-style: none;
        padding: 0;
        margin: 0 0 24px 0;
    }

    .profile-nav li {
        margin-bottom: 6px;
    }

    .profile-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 12px;
        color: #333;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .profile-nav a:hover, .profile-nav a.active {
        background-color: var(--rf-maroon-light);
        color: var(--rf-maroon-primary);
        font-weight: 600;
    }

    .btn-logout {
        width: 100%;
        border: 1px solid var(--rf-maroon-primary);
        background: transparent;
        color: var(--rf-maroon-primary);
        border-radius: 12px;
        padding: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-logout:hover {
        background-color: var(--rf-maroon-primary);
        color: #fff;
    }

    /* --- Card Kanan (Konten Dinamis) --- */
    .content-section-title {
        font-size: 20px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .content-section-desc {
        color: #6c757d;
        font-size: 13px;
        margin-bottom: 20px;
    }

    /* Tombol Ikon Pensil di Kanan Atas Title */
    .btn-icon-edit {
        background-color: var(--rf-maroon-light);
        color: var(--rf-maroon-primary);
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-icon-edit:hover {
        background-color: var(--rf-maroon-primary);
        color: #fff;
    }

    /* Tampilan Data Readonly */
    .info-group {
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f5f5f5;
    }

    .info-label {
        font-size: 12px;
        color: #888;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 15px;
        color: #333;
        font-weight: 500;
    }

    /* Form Control */
    .rf-form-group {
        margin-bottom: 16px;
    }

    .rf-form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
    }

    .rf-form-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #dcedc8;
        border-radius: 10px;
        font-size: 14px;
        color: #333;
        background-color: #fff;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .rf-form-input:focus {
        border-color: var(--rf-maroon-primary);
        box-shadow: 0 0 0 3px rgba(112, 19, 42, 0.1);
    }

    .btn-save-profile {
        background-color: var(--rf-maroon-primary);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background-color 0.2s;
        margin-top: 8px;
    }

    .btn-save-profile:hover {
        background-color: var(--rf-maroon-hover);
    }

    .btn-cancel-edit {
        background-color: #f1f1f1;
        color: #555;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        margin-left: 8px;
        transition: background-color 0.2s;
    }

    .btn-cancel-edit:hover {
        background-color: #e2e2e2;
    }

    /* Alamat Item Card */
    .address-card {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 16px;
        position: relative;
        transition: all 0.2s;
    }

    .address-card.selected {
        border-color: var(--rf-maroon-primary);
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(112, 19, 42, 0.04);
    }

    .badge-tag {
        font-size: 10px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-utama {
        background-color: var(--rf-maroon-primary);
        color: #fff;
    }

    .badge-secondary-tag {
        background-color: #f1f1f1;
        color: #555;
    }

    .selected-indicator {
        color: var(--rf-maroon-primary);
        font-weight: 600;
        font-size: 13px;
    }

    .action-links {
        display: flex;
        gap: 16px;
        margin-top: 14px;
        font-size: 13px;
        font-weight: 500;
    }

    .action-links a {
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .text-edit { color: #d68910; }
    .text-delete { color: #c0392b; }

    .btn-add-address {
        background-color: var(--rf-maroon-primary);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }
    .btn-add-address:hover { background-color: var(--rf-maroon-hover); }


    /* --- Modal Avatar --- */
.rf-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(26, 10, 15, 0.55);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 2000;
}

.rf-modal-overlay.open {
    display: flex;
}

.rf-modal {
    background: #fff;
    border-radius: var(--rf-border-radius);
    width: 100%;
    max-width: 380px;
    padding: 28px 24px 24px;
    text-align: center;
    position: relative;
    box-shadow: 0 20px 50px rgba(112, 19, 42, 0.25);
}

.rf-modal-close {
    position: absolute;
    top: 12px;
    right: 14px;
    background: transparent;
    border: none;
    font-size: 24px;
    line-height: 1;
    color: #888;
    cursor: pointer;
}

.rf-modal-close:hover { color: var(--rf-maroon-primary); }

.rf-modal-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 20px;
}

.modal-avatar {
    width: 170px;
    height: 170px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--rf-maroon-light);
    background: #eee;
    display: block;
    margin: 0 auto 24px;
}

.modal-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-modal-edit {
    background-color: var(--rf-maroon-primary);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.2s;
}

.btn-modal-edit:hover { background-color: var(--rf-maroon-hover); }

.btn-modal-delete {
    background: transparent;
    color: #c0392b;
    border: 1px solid #c0392b;
    border-radius: 10px;
    padding: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s;
}

.btn-modal-delete:hover {
    background-color: #c0392b;
    color: #fff;
}

.modal-hint {
    font-size: 11px;
    color: #888;
    margin-top: 14px;
}
</style>
@endpush

@section('content')
<div class="profile-container">

    <!-- Page Title -->
    <div class="mb-4">
        <h2 class="profile-header-title">Pengaturan Profil & Akun</h2>
        <p class="text-muted small">Kelola profil pribadi, daftar alamat pengiriman, dan preferensi akun Anda</p>
    </div>

    <div class="profile-layout">
        
        <!-- ================= CARD KIRI: SIDEBAR & NAVIGASI ================= -->
        <div class="rf-card">
            <!-- Avatar & Nama dengan Tombol Edit Foto yang langsung memicu input gambar -->
            <div class="profile-avatar-box">
                <div class="avatar-wrapper">
                    <img src="{{ $user->foto_profil ? asset('storage/' . $user->foto_profil) : 'https://ui-avatars.com/api/?name=' . urlencode($user->nama) . '&background=70132A&color=fff' }}" 
                         alt="Avatar" class="profile-avatar">
                    <button type="button" class="avatar-edit-btn" onclick="openAvatarModal()" title="Ganti Foto">
                        {!! \App\Support\Ikon::svg('edit', 16) !!}
                    </button>
                </div>
                <h5 class="fw-bold mb-0 text-dark">{{ $user->nama }}</h5>
                <p class="text-muted small mb-1">{{ $user->email }}</p>
                <p class="text-muted small mb-0">{{ $user->nmr_telepon }}</p>
            </div>

            <!-- Navigasi Menu Kiri -->
            <ul class="profile-nav">
                <li>
                    <a href="#biodata" data-target="biodata-section" class="nav-menu-item active">
                        <i class="bi bi-person"></i> Biodata Diri
                    </a>
                </li>
                <li>
                    <a href="#alamat" data-target="alamat-section" class="nav-menu-item">
                        <i class="bi bi-geo-alt"></i> Daftar Alamat Pengiriman
                    </a>
                </li>
                <li>
                    <a href="#sandi" data-target="sandi-section" class="nav-menu-item">
                        <i class="bi bi-lock"></i> Ubah Kata Sandi
                    </a>
                </li>
            </ul>

            <!-- Tombol Keluar Akun (Logout) -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Keluar Akun
                </button>
            </form>
        </div>

        <!-- ================= CARD KANAN: KONTEN DINAMIS ================= -->
        <div class="rf-card">
            
            <!-- 1. SECTION: BIODATA DIRI (DEFAULT ACTIVE) -->
            <div id="biodata-section" class="profile-content-pane">
                
                <!-- Sub-View 1: Tampilan Data (Read-only) -->
                <div id="biodata-view-mode">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="content-section-title">Biodata Diri</h4>
                            <p class="content-section-desc">Kelola informasi data pribadi Anda untuk kemudahan pengiriman dan komunikasi.</p>
                        </div>
                        <button type="button" class="btn-icon-edit" onclick="switchToEditMode()" title="Edit Biodata">
                            {!! \App\Support\Ikon::svg('edit', 16) !!}
                        </button>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value">{{ $user->nama }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Nomor Telepon</div>
                        <div class="info-value">{{ $user->nmr_telepon ?? 'Belum diatur' }}</div>
                    </div>
                </div>

                <!-- Sub-View 2: Form Edit Data (Hidden by default) -->
                <div id="biodata-edit-mode" style="display: none;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="content-section-title">Edit Biodata Diri</h4>
                            <p class="content-section-desc">Perbarui informasi profil dan foto Anda di bawah ini.</p>
                        </div>
                        <button type="button" class="btn-icon-edit" onclick="switchToViewMode()" title="Kembali">
                            {!! \App\Support\Ikon::svg('edit', 16) !!}
                        </button>
                    </div>

                    <form action="{{ route('pelanggan.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="rf-form-group">
                            <label class="rf-form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="rf-form-input" value="{{ $user->nama }}" required>
                        </div>
                        <div class="rf-form-group">
                            <label class="rf-form-label">Email</label>
                            <input type="email" name="email" class="rf-form-input" value="{{ $user->email }}" required>
                        </div>
                        <div class="rf-form-group">
                            <label class="rf-form-label">Nomor Telepon</label>
                            <input type="text" name="nmr_telepon" class="rf-form-input" value="{{ $user->nmr_telepon ?? '' }}">
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn-save-profile">Simpan Perubahan</button>
                            <button type="button" class="btn-cancel-edit" onclick="switchToViewMode()">Batal</button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- 2. SECTION: DAFTAR ALAMAT PENGIRIMAN -->
            <div id="alamat-section" class="profile-content-pane" style="display: none;">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="content-section-title">Daftar Alamat Pengiriman Saya</h4>
            <p class="content-section-desc">Simpan alamat rumah atau kantor untuk mempercepat checkout pesanan bunga.</p>
        </div>
        <button type="button" class="btn-add-address" onclick="openAlamatModal()">+ Tambah Alamat Baru</button>
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 16px 0;">

    @forelse($alamatList as $alamat)
        <div class="address-card {{ $alamat->is_utama ? 'selected' : '' }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex gap-2">
                    @if($alamat->is_utama)
                        <span class="badge-tag badge-utama">Alamat Utama</span>
                    @endif
                </div>

                @if($alamat->is_utama)
                    <span class="selected-indicator">✓ Dipilih</span>
                @else
                    <form action="{{ route('pelanggan.alamat-pengiriman.utama', $alamat->getKey()) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-jadikan-utama">Jadikan Utama</button>
                    </form>
                @endif
            </div>

            <h6 class="fw-bold mb-1 text-dark">
                {{ $alamat->nama_penerima }}
                <span class="text-muted fw-normal small">({{ $alamat->nomor_hp }})</span>
            </h6>
            <p class="text-secondary small mb-1">{{ $alamat->alamat_lengkap }}, {{ $alamat->kota }}</p>
            @if($alamat->catatan)
                <p class="text-muted small fst-italic mb-0">Catatan: {{ $alamat->catatan }}</p>
            @endif

            <div class="action-links">
                <a href="#" class="text-edit"
                   data-alamat="{{ json_encode([
                       'id' => $alamat->getKey(),
                       'nama_penerima' => $alamat->nama_penerima,
                       'nomor_hp' => $alamat->nomor_hp,
                       'alamat_lengkap' => $alamat->alamat_lengkap,
                       'kota' => $alamat->kota,
                       'catatan' => $alamat->catatan,
                       'is_utama' => $alamat->is_utama,
                   ]) }}"
                   onclick="event.preventDefault(); openAlamatModal(JSON.parse(this.dataset.alamat))">
                    Ubah Alamat
                </a>
                <span>|</span>
                <form action="{{ route('pelanggan.alamat-pengiriman.destroy', $alamat->getKey()) }}" method="POST"
                      onsubmit="return confirm('Hapus alamat ini?')" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-delete"
                            style="background:none;border:0;padding:0;font:inherit;cursor:pointer;">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted small">Belum ada alamat tersimpan. Klik "+ Tambah Alamat Baru" untuk menambahkan.</p>
    @endforelse
</div>

            <div id="sandi-section" class="profile-content-pane" style="display: none;">
                    <form action="{{ route('pelanggan.profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if($errors->updatePassword->any())
                        <div style="background:#fdecea;color:#b71c1c;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;">
                            <ul style="margin:0;padding-left:18px;">
                                @foreach($errors->updatePassword->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="rf-form-group">
                        <label class="rf-form-label">Password Lama</label>
                        <input type="password" name="current_password" class="rf-form-input" autocomplete="current-password" required>
                    </div>
                    <div class="rf-form-group">
                        <label class="rf-form-label">Password Baru</label>
                        <input type="password" name="password" class="rf-form-input" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                    </div>
                    <div class="rf-form-group">
                        <label class="rf-form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="rf-form-input" placeholder="Ulangi password baru" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn-save-profile">Perbarui Kata Sandi</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div style="background:#e8f5e9;color:#1b5e20;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:14px;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background:#fdecea;color:#b71c1c;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:14px;">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ================= MODAL AVATAR ================= --}}
<div class="rf-modal-overlay" id="avatarModal" onclick="if(event.target === this) closeAvatarModal()">
    <div class="rf-modal" role="dialog" aria-modal="true" aria-labelledby="avatarModalTitle">
        <button type="button" class="rf-modal-close" onclick="closeAvatarModal()" aria-label="Tutup">&times;</button>

        <h5 class="rf-modal-title" id="avatarModalTitle">Foto Profil</h5>

        <img src="{{ $user->foto_profil ? asset('storage/' . $user->foto_profil) : 'https://ui-avatars.com/api/?name=' . urlencode($user->nama) . '&background=70132A&color=fff&size=256' }}"
             alt="Foto Profil" class="modal-avatar">

        <div class="modal-actions">
            {{-- Edit foto: pilih file lalu otomatis terkirim --}}
            <form action="{{ route('pelanggan.profile.update') }}" method="POST" enctype="multipart/form-data" id="avatarUploadForm">
                @csrf
                @method('PUT')
                {{-- Dikirim ulang agar validasi nama/email di controller tetap lolos --}}
                <input type="hidden" name="nama" value="{{ $user->nama }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                <input type="hidden" name="nmr_telepon" value="{{ $user->nmr_telepon }}">
                <input type="file" name="foto_profil" id="avatarFileInput" accept="image/*" hidden>

                <button type="button" class="btn-modal-edit" onclick="document.getElementById('avatarFileInput').click()">
                    Edit Foto
                </button>
            </form>

            {{-- Hapus foto: hanya muncul kalau sudah punya foto --}}
            @if($user->foto_profil)
                <form action="{{ route('pelanggan.profile.deletePhoto') }}" method="POST"
                      onsubmit="return confirm('Hapus foto profil Anda?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-modal-delete">Hapus Foto</button>
                </form>
            @endif
        </div>

        <p class="modal-hint">Format: JPG, PNG, WEBP (Maks. 2MB)</p>
    </div>
</div>

<div class="rf-modal-overlay" id="alamatModal" onclick="if(event.target === this) closeAlamatModal()">
    <div class="rf-modal" style="max-width: 480px; text-align: left;" role="dialog" aria-modal="true">
        <button type="button" class="rf-modal-close" onclick="closeAlamatModal()" aria-label="Tutup">&times;</button>
        <h5 class="rf-modal-title" id="alamatModalTitle" style="text-align:center;">Tambah Alamat Baru</h5>

        <form id="alamatForm" method="POST" action="{{ route('pelanggan.alamat-pengiriman.store') }}">
            @csrf
            <input type="hidden" name="_method" id="alamatMethod" value="PUT" disabled>
            <input type="hidden" name="form_alamat" value="1">
            <input type="hidden" name="alamat_id" id="alamat_id" value="{{ old('alamat_id') }}">

            @if(old('form_alamat') && $errors->any())
                <div style="background:#fdecea;color:#b71c1c;padding:10px 14px;border-radius:10px;margin-bottom:14px;font-size:13px;">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="rf-form-group">
                <label class="rf-form-label">Nama Penerima</label>
                <input type="text" name="nama_penerima" id="f_nama" class="rf-form-input" value="{{ old('nama_penerima') }}" required>
            </div>
            <div class="rf-form-group">
                <label class="rf-form-label">Nomor HP</label>
                <input type="text" name="nomor_hp" id="f_hp" class="rf-form-input" value="{{ old('nomor_hp') }}" required>
            </div>
            <div class="rf-form-group">
                <label class="rf-form-label">Alamat Lengkap</label>
                <textarea name="alamat_lengkap" id="f_alamat" class="rf-form-input" rows="3" required>{{ old('alamat_lengkap') }}</textarea>
            </div>
            <div class="rf-form-group">
                <label class="rf-form-label">Kota</label>
                <input type="text" name="kota" id="f_kota" class="rf-form-input" value="{{ old('kota') }}" required>
            </div>
            <div class="rf-form-group">
                <label class="rf-form-label">Catatan / Patokan (opsional)</label>
                <input type="text" name="catatan" id="f_catatan" class="rf-form-input" value="{{ old('catatan') }}">
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:16px;cursor:pointer;">
                <input type="hidden" name="is_utama" value="0">
                <input type="checkbox" name="is_utama" id="f_utama" value="1" {{ old('is_utama') ? 'checked' : '' }}>
                Jadikan alamat utama
            </label>

            <button type="submit" class="btn-save-profile">Simpan Alamat</button>
            <button type="button" class="btn-cancel-edit" onclick="closeAlamatModal()">Batal</button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Fungsi Switch Mode View dan Edit Biodata
    function switchToEditMode() {
        document.getElementById('biodata-view-mode').style.display = 'none';
        document.getElementById('biodata-edit-mode').style.display = 'block';
    }

    function switchToViewMode() {
        document.getElementById('biodata-edit-mode').style.display = 'none';
        document.getElementById('biodata-view-mode').style.display = 'block';
    }

function openAvatarModal() {
    document.getElementById('avatarModal').classList.add('open');
}

function closeAvatarModal() {
    document.getElementById('avatarModal').classList.remove('open');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAvatarModal();
});

document.getElementById('avatarFileInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran foto maksimal 2MB.');
        this.value = '';
        return;
    }

    document.getElementById('avatarUploadForm').submit();
});

document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelectorAll('.nav-menu-item');
    const panes = document.querySelectorAll('.profile-content-pane');

    function showPane(targetId) {
        panes.forEach(p => p.style.display = (p.id === targetId) ? 'block' : 'none');
        navLinks.forEach(l => l.classList.toggle('active', l.dataset.target === targetId));
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            showPane(this.dataset.target);
            history.replaceState(null, '', this.getAttribute('href'));
        });
    });

    const fromHash = document.querySelector(`.nav-menu-item[href="${location.hash}"]`);
    showPane(fromHash ? fromHash.dataset.target : 'biodata-section');

    @if($errors->any() && !$errors->updatePassword->any())
        switchToEditMode(); // validasi biodata gagal: buka lagi form edit
    @endif
});

const alamatUpdateUrl = @json(route('pelanggan.alamat-pengiriman.update', ['id' => '__ID__']));
const alamatStoreUrl  = @json(route('pelanggan.alamat-pengiriman.store'));

function openAlamatModal(data = null) {
    const form = document.getElementById('alamatForm');
    const method = document.getElementById('alamatMethod');

    if (data) {
        document.getElementById('alamatModalTitle').textContent = 'Ubah Alamat';
        form.action = alamatUpdateUrl.replace('__ID__', data.id);
        method.disabled = false;
        document.getElementById('alamat_id').value = data.id;
        document.getElementById('f_nama').value = data.nama_penerima ?? '';
        document.getElementById('f_hp').value = data.nomor_hp ?? '';
        document.getElementById('f_alamat').value = data.alamat_lengkap ?? '';
        document.getElementById('f_kota').value = data.kota ?? '';
        document.getElementById('f_catatan').value = data.catatan ?? '';
        document.getElementById('f_utama').checked = !!data.is_utama;
    } else {
        document.getElementById('alamatModalTitle').textContent = 'Tambah Alamat Baru';
        form.action = alamatStoreUrl;
        method.disabled = true;
        form.reset();
        document.getElementById('alamat_id').value = '';
    }

    document.getElementById('alamatModal').classList.add('open');
}

function closeAlamatModal() {
    document.getElementById('alamatModal').classList.remove('open');
}
</script>
@endpush