@extends('layouts.admin')

@section('title', 'Edit Staff')

@section('content')
    @include('admin.karyawan-form', [
        'judul'    => 'Edit Staff',
        'action'   => route('admin.karyawan.update', $karyawan),
        'method'   => 'PUT',
        'karyawan' => $karyawan,
    ])
@endsection
