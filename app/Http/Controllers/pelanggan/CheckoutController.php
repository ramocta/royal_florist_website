<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        $pelanggan = auth()->user();

        return view('pelanggan.checkout', compact('pelanggan'));
    }

    public function store(Request $request)
    {
        // Proses penyimpanan pesanan akan kita buat
        // setelah struktur tabel database diperiksa.
    }
}