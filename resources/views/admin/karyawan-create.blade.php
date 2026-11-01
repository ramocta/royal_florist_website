@extends('layouts.admin')

@section('title', 'Tambah Staff')

@section('content')
    @include('admin.karyawan-form', [
        'judul'    => 'Tambah Staff',
        'action'   => route('admin.karyawan.store'),
        'method'   => 'POST',
        'karyawan' => null,
    ])
@endsection
