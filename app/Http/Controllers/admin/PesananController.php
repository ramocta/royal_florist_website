<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    private const STATUS_PESANAN = [
        'menunggu_pembayaran', 'menunggu_dikonfirmasi', 'diproses',
        'siap_diambil', 'selesai', 'dibatalkan',
    ];

    // READ: daftar + statistik + pencarian + filter status
    public function index(Request $request)
    {
        $stat = [
            'total'    => Order::whereMonth('dibuat_pada', now()->month)
                               ->whereYear('dibuat_pada', now()->year)->count(),
            'diproses' => Order::where('status_pesanan', 'diproses')->count(),
            'siap'     => Order::where('status_pesanan', 'siap_diambil')->count(),
            'selesai'  => Order::where('status_pesanan', 'selesai')
                               ->whereDate('diubah_pada', today())->count(),
        ];

        $pesanan = Order::with(['user', 'items.product'])
            ->when($request->q, fn ($query, $q) => $query->where(function ($w) use ($q) {
                $w->where('no_invoice', 'like', "%{$q}%")
                  ->orWhereHas('user', fn ($u) => $u->where('nama', 'like', "%{$q}%"));
            }))
            ->when($request->status, fn ($query, $s) => $query->where('status_pesanan', $s))
            ->orderByDesc('id_order')
            ->paginate(5)
            ->withQueryString(); // filter tidak hilang saat pindah halaman

        return view('admin.pesanan-index', compact('pesanan', 'stat'));
    }

    // CREATE: tampilkan form
    public function create()
    {
        $produk    = Product::where('is_active', true)->orderBy('nama_produk')
                            ->get(['id_product', 'nama_produk', 'harga', 'stok']);

        return view('admin.pesanan-create', compact('produk'));
    }

    // CREATE: simpan
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'               => 'required|string|max:100',
            'nmr_telepon'        => 'required|string|max:15',
            'alamat'             => 'required|string',
            'metode_pembayaran'  => ['required', Rule::in(['bayar_di_toko', 'QRIS', 'transfer_bank'])],
            'status_pembayaran'  => ['required', Rule::in(['belum_dibayar', 'proses', 'lunas'])],
            'status_pesanan'     => ['required', Rule::in(self::STATUS_PESANAN)],
            'jadwal_pengambilan' => 'nullable|date',
            'items'              => 'required|array|min:1',
            'items.*.id_product' => ['required', 'distinct', Rule::exists('products', 'id_product')],
            'items.*.jumlah'     => 'required|integer|min:1',
        ], [
            'required' => ':attribute wajib diisi.',
            'exists'   => ':attribute tidak valid.',
            'in'       => ':attribute tidak valid.',
            'integer'  => ':attribute harus berupa angka.',
            'min'      => ':attribute minimal :min.',
            'max'      => ':attribute maksimal :max karakter.',
            'string'   => ':attribute harus berupa teks.',
            'distinct' => 'Produk tidak boleh dipilih dua kali.',
            'date'     => 'Format tanggal tidak valid.',
        ], [
            'nama'               => 'Nama pelanggan',
            'nmr_telepon'        => 'No. telepon',
            'alamat'             => 'Alamat',
            'metode_pembayaran'  => 'Metode pembayaran',
            'status_pembayaran'  => 'Status pembayaran',
            'status_pesanan'     => 'Status pesanan',
            'jadwal_pengambilan' => 'Jadwal pengiriman',
            'items'              => 'Item produk',
            'items.*.id_product' => 'Produk',
            'items.*.jumlah'     => 'Jumlah',
        ]);

        $order = DB::transaction(function () use ($data) {
            $pelanggan = $this->cariAtauBuatPelanggan($data);

            $total = 0;
            $baris = [];

            foreach ($data['items'] as $i => $item) {
                $produk = Product::lockForUpdate()->find($item['id_product']);

                if ($produk->stok < $item['jumlah']) {
                    throw ValidationException::withMessages([
                        "items.$i.jumlah" => "Stok {$produk->nama_produk} tinggal {$produk->stok}.",
                    ]);
                }

                $produk->decrement('stok', $item['jumlah']);
                $total += $produk->harga * $item['jumlah'];
                $baris[] = [
                    'id_product'   => $produk->id_product,
                    'jumlah'       => $item['jumlah'],
                    'harga_satuan' => $produk->harga, // harga dikunci saat pesanan dibuat
                ];
            }

            $order = Order::create([
                'no_invoice'         => $this->buatInvoice(),
                'id_user'            => $pelanggan->id_user,
                'metode_pembayaran'  => $data['metode_pembayaran'],
                'status_pembayaran'  => $data['status_pembayaran'],
                'status_pesanan'     => $data['status_pesanan'],
                'total_harga'        => $total,
                'dibayar_pada'       => $data['status_pembayaran'] === 'lunas' ? now() : null,
                'jadwal_pengambilan' => $data['jadwal_pengambilan'] ?? null,
            ]);

            $order->items()->createMany($baris);

            OrderStatusHistory::create([
                'id_order'       => $order->id_order,
                'status_pesanan' => $order->status_pesanan,
                'catatan'        => 'Pesanan dibuat oleh admin.',
                'diubah_oleh'    => auth()->id(), // null kalau login belum dipasang
            ]);

            return $order;
        });

        return redirect()->route('admin.pesanan.index')
            ->with('success', "Pesanan #{$order->no_invoice} berhasil dibuat.");
    }

    // Pelanggan dicari dari nama + no. telepon. Kalau belum ada, dibuat otomatis sebagai pelanggan.
    // Tabel orders butuh id_user, sedangkan kolom email dan password di tabel users wajib diisi,
    // jadi untuk pelanggan baru diisi email sementara dan password acak (tidak untuk login).
    private function cariAtauBuatPelanggan(array $data): User
    {
        $pelanggan = User::where('role', 'pelanggan')
            ->where('nama', $data['nama'])
            ->where('nmr_telepon', $data['nmr_telepon'])
            ->first();

        if ($pelanggan) {
            // Pelanggan lama: perbarui alamat dengan yang terbaru
            $pelanggan->update(['alamat' => $data['alamat']]);
            return $pelanggan;
        }

        $pelanggan = new User([
            'nama'        => $data['nama'],
            'nmr_telepon' => $data['nmr_telepon'],
            'alamat'      => $data['alamat'],
            'email'       => 'pelanggan-' . Str::lower(Str::random(10)) . '@royalflorist.local',
            'password'    => Str::random(32), // di-hash otomatis oleh cast 'hashed'
        ]);
        $pelanggan->role = 'pelanggan'; // role tidak fillable, diisi manual di server
        $pelanggan->save();

        return $pelanggan;
    }

    // No. invoice: ORD-0001, ORD-0002, ... (kolom no_invoice maksimal 20 karakter)
    private function buatInvoice(): string
    {
        $n = (int) Order::max('id_order') + 1;

        do {
            $kode = 'ORD-' . str_pad($n++, 4, '0', STR_PAD_LEFT);
        } while (Order::where('no_invoice', $kode)->exists());

        return $kode;
    }
}
