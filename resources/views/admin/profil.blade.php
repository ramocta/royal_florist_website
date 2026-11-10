
@extends('layouts.admin')

@section('title', 'Profil')

@push('styles')
<style>
.profil-page {
    color: #2a1a1e;
    font-family: 'Poppins', sans-serif;
}

.profil-page .page-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}

.profil-page h1,
.profil-page h2,
.profil-page h3 {
    font-family: 'Playfair Display', serif;
    color: #7a1a33;
}

.profil-page h1 {
    font-size: 28px;
    margin-bottom: 6px;
}

.profil-page .subtitle {
    color: #6f5a5f;
    font-size: 13px;
}

.profil-page .btn {
    padding: 10px 16px;
    border: 1px solid #7a1a33;
    border-radius: 8px;
    cursor: pointer;
    font-family: inherit;
    font-size: 13px;
    text-decoration: none;
}

.profil-page .btn-fill {
    background: #7a1a33;
    color: white;
}

.profil-page .btn-line {
    background: white;
    color: #7a1a33;
}

.profil-page .layout {
    display: grid;
    grid-template-columns: 300px minmax(0, 1fr);
    gap: 22px;
}

.profil-page .card {
    background: white;
    border: 1px solid #f1dde0;
    border-radius: 14px;
    padding: 24px;
}

.profil-page .profile-card {
    text-align: center;
    height: fit-content;
}

.profil-page .photo {
    width: 90px;
    height: 90px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: #7a1a33;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    font-weight: 600;
    overflow: hidden;
}

.profil-page .photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profil-page .badge {
    display: inline-block;
    margin-top: 10px;
    padding: 5px 16px;
    background: #fce8ea;
    color: #7a1a33;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.profil-page .menu {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #f1dde0;
}

.profil-page .menu button {
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: white;
    color: #6f5a5f;
    text-align: left;
    cursor: pointer;
    font-family: inherit;
}

.profil-page .menu button.active,
.profil-page .menu button:hover {
    background: #fce8ea;
    color: #7a1a33;
}

.profil-page .stack {
    display: flex;
    flex-direction: column;
    gap: 22px;
    min-width: 0;
}

.profil-page .form-card h3 {
    font-size: 18px;
    padding-bottom: 14px;
    margin-bottom: 18px;
    border-bottom: 1px solid #f1dde0;
}

.profil-page .grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.profil-page .full {
    grid-column: 1 / -1;
}

.profil-page label {
    display: block;
    font-weight: 600;
    margin-bottom: 7px;
    font-size: 12px;
}

.profil-page input,
.profil-page textarea {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #f1dde0;
    border-radius: 8px;
    font-family: inherit;
    font-size: 13px;
    outline: none;
}

.profil-page input:focus,
.profil-page textarea:focus {
    border-color: #7a1a33;
}

.profil-page textarea {
    min-height: 90px;
    resize: vertical;
}

.profil-page .actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.profil-page .alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 13px;
}

.profil-page .success {
    background: #e8f7ed;
    color: #216b3a;
}

.profil-page .error {
    background: #fdebed;
    color: #a32636;
}

.profil-page .field-error {
    color: #c62828;
    font-size: 11px;
    margin-top: 5px;
}

.profil-page .photo-form {
    margin-top: 20px;
    text-align: left;
}

.profil-page .photo-form input {
    margin-bottom: 12px;
}

.profil-page .photo-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.profil-page .photo-actions button {
    font-size: 11px;
}

@media(max-width: 1000px) {
    .profil-page .layout {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 600px) {
    .profil-page .grid {
        grid-template-columns: 1fr;
    }

    .profil-page .full {
        grid-column: auto;
    }

    .profil-page .page-head {
        align-items: flex-start;
    }

    .profil-page .card {
        padding: 18px;
    }
}
</style>
@endpush

@section('content')
<div class="profil-page">

    <div class="page-head">
        <div>
            <h1>Profil</h1>
            <p class="subtitle">Kelola informasi akun dan kata sandi Anda.</p>
        </div>

        <button type="submit" form="formProfil" class="btn btn-fill">
            Simpan Perubahan
        </button>
    </div>

    @if(session('success'))
        <div class="alert success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert error">
            <strong>Periksa kembali data yang dimasukkan.</strong>
            <ul style="padding-left:20px; margin-top:6px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="layout">

        <section class="card profile-card">

            <div class="photo">
                @if($user->foto_profil)
                    <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="Foto profil">
                @else
                    {{ strtoupper(substr($user->nama ?? 'A', 0, 1)) }}
                @endif
            </div>

            <h2>{{ $user->nama }}</h2>
            <p style="font-size:12px; color:#6f5a5f; margin-top:5px;">
                {{ $user->email }}
            </p>

            <span class="badge">
                {{ strtoupper($user->role ?? 'ADMIN') }}
            </span>

            <div class="photo-form">
                <form action="{{ route('admin.profil.foto.update') }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    <label>Foto Profil</label>
                    <input type="file" name="foto_profil" accept="image/*">

                    <div class="photo-actions">
                        <button type="submit" class="btn btn-fill">
                            Upload Foto
                        </button>
                    </div>
                </form>

                @if($user->foto_profil)
                    <form action="{{ route('admin.profil.foto.delete') }}"
                          method="POST"
                          style="margin-top:10px;">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-line"
                                onclick="return confirm('Hapus foto profil?')">
                            Hapus Foto
                        </button>
                    </form>
                @endif
            </div>

            <div class="menu">
                <button type="button" class="active" data-target="sec-data">
                    Data Diri Saya
                </button>

                <button type="button" data-target="sec-pass">
                    Ubah Kata Sandi
                </button>
            </div>

        </section>

        <div class="stack">

            <section class="card form-card" id="sec-data">

                <h3>Informasi Data Diri Saya</h3>

                <form action="{{ route('admin.profil.update') }}"
                      method="POST"
                      id="formProfil">
                    @csrf
                    @method('PUT')

                    <div class="grid">

                        <div class="full">
                            <label>Nama Lengkap</label>
                            <input type="text"
                                   name="nama"
                                   value="{{ old('nama', $user->nama) }}"
                                   required>
                            @error('nama')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label>Email</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label>Nomor Telepon</label>
                            <input type="tel"
                                   name="nmr_telepon"
                                   value="{{ old('nmr_telepon', $user->nmr_telepon) }}"
                                   required>
                            @error('nmr_telepon')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="full">
                            <label>Alamat</label>
                            <textarea name="alamat" required>{{ old('alamat', $user->alamat) }}</textarea>
                            @error('alamat')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </form>

            </section>

            <section class="card form-card" id="sec-pass">

                <h3>Ubah Kata Sandi</h3>

                <form action="{{ route('admin.profil.password') }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid">

                        <div class="full">
                            <label>Kata Sandi Saat Ini</label>
                            <input type="password"
                                   name="password_lama"
                                   required>
                            @error('password_lama')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label>Kata Sandi Baru</label>
                            <input type="password"
                                   name="password_baru"
                                   required>
                            @error('password_baru')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label>Konfirmasi Kata Sandi Baru</label>
                            <input type="password"
                                   name="password_baru_confirmation"
                                   required>
                        </div>

                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-fill">
                            Update Password
                        </button>
                    </div>

                </form>

            </section>

        </div>
    </div>
</div>

<script>
document.querySelectorAll('.profil-page .menu button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.profil-page .menu button')
            .forEach(item => item.classList.remove('active'));

        btn.classList.add('active');

        document.getElementById(btn.dataset.target)
            .scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>
@endsection
