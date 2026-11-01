@extends('layouts.admin')

@section('content')
@include('admin.produk-style')
<div class="pr">
    <div class="head">
        <div>
            <h1>Produk Baru</h1>
            <p class="sub">Isi data produk yang akan tampil di katalog toko.</p>
        </div>
    </div>
    <div class="card">
        @include('admin.produk-form', ['action' => route('admin.produk.store')])
    </div>
</div>
@endsection
