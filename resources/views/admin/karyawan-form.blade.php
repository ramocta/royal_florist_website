{{--
  Form yang dipakai bersama oleh halaman Tambah dan Edit.
  Variabel yang diterima:
    $judul    judul halaman
    $action   URL tujuan form
    $method   'POST' (tambah) atau 'PUT' (edit)
    $karyawan data staff (null kalau tambah)
--}}
@php
    $k = $karyawan ?? null;
    $method = $method ?? 'POST';
    $input = 'w-full rounded-xl border border-pinkline bg-white px-4 py-2.5 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon';
@endphp

<div class="max-w-xl">
    <a href="{{ route('admin.karyawan.index') }}" class="text-sm text-maroon hover:underline">&larr; Kembali ke daftar staff</a>
    <h1 class="mt-2 font-display text-3xl font-semibold text-maroon">{{ $judul }}</h1>

    <form method="POST" action="{{ $action }}" class="mt-6 space-y-4 rounded-2xl border border-pinkline p-6">
        @csrf
        @if ($method !== 'POST') @method($method) @endif

        <div>
            <label for="nama" class="mb-1 block text-sm font-medium text-maroon">Nama</label>
            <input id="nama" type="text" name="nama" value="{{ old('nama', $k?->nama) }}" class="{{ $input }}">
            @error('nama') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-maroon">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $k?->email) }}" class="{{ $input }}">
            @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="nmr_telepon" class="mb-1 block text-sm font-medium text-maroon">No. Telepon</label>
            <input id="nmr_telepon" type="text" name="nmr_telepon" value="{{ old('nmr_telepon', $k?->nmr_telepon) }}" class="{{ $input }}">
            @error('nmr_telepon') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="alamat" class="mb-1 block text-sm font-medium text-maroon">Alamat</label>
            <textarea id="alamat" name="alamat" rows="3" class="{{ $input }}">{{ old('alamat', $k?->alamat) }}</textarea>
            @error('alamat') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-maroon">Status</label>
            <select id="status" name="status" class="{{ $input }}">
                @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('status', $k?->status ?? 'aktif') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-maroon">Password</label>
            <input id="password" type="password" name="password" class="{{ $input }}"
                   placeholder="{{ $k ? 'Kosongkan jika tidak diganti' : 'Minimal 8 karakter' }}">
            @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-maroon">Ulangi password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="{{ $input }}">
        </div>

        <button class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-medium text-white hover:opacity-90">Simpan</button>
    </form>
</div>
