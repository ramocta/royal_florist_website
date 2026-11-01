@extends('layouts.admin')

@section('content')
@include('admin.produk-style')
<div class="pr">
    <div class="head">
        <div>
            <h1>Edit Produk</h1>
            <p class="sub">{{ $produk->sku }} &middot; {{ $produk->nama_produk }}</p>
        </div>
    </div>
    <div class="card">
        @include('admin.produk-form', ['action' => route('admin.produk.update', $produk)])
    </div>
</div>
@endsection
