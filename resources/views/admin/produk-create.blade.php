@extends('layouts.admin')

@section('content')
@include('admin.produk-style')
<div class="pr">
    <div class="head">
        <div>
            <h1>Produk Baru</h1>
            <p class="sub">Tambahkan satu atau beberapa produk sekaligus. Foto boleh dikosongkan dan diunggah nanti.</p>
        </div>
    </div>
    @include('admin.produk-form-banyak')
</div>
@endsection
