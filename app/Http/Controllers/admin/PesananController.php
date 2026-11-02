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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        $pesanan = $this->queryPesanan($request)
            ->orderByDesc('id_order')
            ->paginate(5)
            ->withQueryString(); // filter tidak hilang saat pindah halaman

        return view('admin.pesanan-index', compact('pesanan', 'stat'));
    }

    // Export Excel. Ikut filter pencarian/status yang sedang aktif di daftar.
    // - Kalau library phpoffice/phpspreadsheet sudah terpasang -> file .xlsx (lebih rapi)
    // - Kalau belum -> otomatis pakai file .xls (XML Spreadsheet), tanpa library apa pun
    public function export(Request $request)
    {
        $label = [
            'menunggu_pembayaran' => 'Menunggu Pembayaran', 'menunggu_dikonfirmasi' => 'Menunggu Dikonfirmasi',
            'diproses' => 'Diproses', 'siap_diambil' => 'Siap Kirim', 'selesai' => 'Selesai', 'dibatalkan' => 'Batal',
        ];
        $metode = ['bayar_di_toko' => 'Tunai', 'QRIS' => 'QRIS', 'transfer_bank' => 'Transfer Bank'];
        $bayar  = ['belum_dibayar' => 'Belum Dibayar', 'proses' => 'Proses', 'lunas' => 'Lunas'];

        $judul = [
            'No', 'ID Pesanan', 'Tanggal Order', 'Pelanggan', 'No. Telepon', 'Alamat',
            'Item Produk', 'Tgl Pengiriman', 'Metode Pembayaran', 'Status Pembayaran',
            'Status Pesanan', 'Total (Rp)',
        ];

        $data = [];
        $rows = $this->queryPesanan($request)->orderByDesc('id_order')->get();
        foreach ($rows as $i => $o) {
            $item = $o->items
                ->map(fn ($it) => ($it->product->nama_produk ?? '-') . ' x' . $it->jumlah)
                ->implode('; ');

            $data[] = [
                $i + 1,
                (string) $o->no_invoice,
                $o->dibuat_pada ? $o->dibuat_pada->format('d-m-Y H:i') : '-',
                (string) ($o->user->nama ?? '-'),
                (string) ($o->user->nmr_telepon ?? '-'),
                (string) ($o->user->alamat ?? '-'),
                $item,
                $o->jadwal_pengambilan ? $o->jadwal_pengambilan->format('d-m-Y H:i') : '-',
                $metode[$o->metode_pembayaran] ?? $o->metode_pembayaran,
                $bayar[$o->status_pembayaran] ?? $o->status_pembayaran,
                $label[$o->status_pesanan] ?? $o->status_pesanan,
                (int) $o->total_harga,
            ];
        }

        $tanggal = now()->format('Y-m-d');

        return class_exists(Spreadsheet::class)
            ? $this->unduhXlsx($judul, $data, "data-pesanan-{$tanggal}.xlsx")
            : $this->unduhXml($judul, $data, "data-pesanan-{$tanggal}.xls");
    }

    // File .xlsx lewat PhpSpreadsheet
    private function unduhXlsx(array $judul, array $data, string $namaFile)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pesanan');

        $sheet->fromArray($judul, null, 'A1');
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7A1C34']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->freezePane('A2'); // baris judul tetap terlihat saat scroll

        $r = 2;
        foreach ($data as $baris) {
            $telepon = $baris[4];
            $baris[4] = null;
            $sheet->fromArray($baris, null, "A{$r}");
            // Paksa jadi teks supaya angka 0 di depan nomor telepon tidak hilang
            $sheet->setCellValueExplicit("E{$r}", $telepon, DataType::TYPE_STRING);
            $r++;
        }

        if ($r > 2) {
            $sheet->getStyle('L2:L' . ($r - 1))->getNumberFormat()->setFormatCode('#,##0');
        }
        foreach (range('A', 'L') as $kolom) {
            $sheet->getColumnDimension($kolom)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $namaFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // File .xls (XML Spreadsheet 2003): bisa dibuka Excel, tanpa library tambahan
    private function unduhXml(array $judul, array $data, string $namaFile)
    {
        $esc = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $lebar = [35, 80, 100, 130, 100, 220, 260, 100, 120, 120, 130, 90];

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
              . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= '<Styles>'
              . '<Style ss:ID="h"><Font ss:Bold="1" ss:Color="#FFFFFF"/>'
              . '<Interior ss:Color="#7A1C34" ss:Pattern="Solid"/></Style>'
              . '<Style ss:ID="n"><NumberFormat ss:Format="#,##0"/></Style>'
              . '</Styles>';
        $xml .= '<Worksheet ss:Name="Data Pesanan"><Table>';
        foreach ($lebar as $w) {
            $xml .= '<Column ss:Width="' . $w . '"/>';
        }

        $xml .= '<Row>';
        foreach ($judul as $j) {
            $xml .= '<Cell ss:StyleID="h"><Data ss:Type="String">' . $esc($j) . '</Data></Cell>';
        }
        $xml .= '</Row>';

        foreach ($data as $baris) {
            $xml .= '<Row>';
            foreach ($baris as $k => $v) {
                if ($k === 0) {
                    $xml .= '<Cell><Data ss:Type="Number">' . (int) $v . '</Data></Cell>';
                } elseif ($k === 11) {
                    $xml .= '<Cell ss:StyleID="n"><Data ss:Type="Number">' . (int) $v . '</Data></Cell>';
                } else {
                    $xml .= '<Cell><Data ss:Type="String">' . $esc($v) . '</Data></Cell>';
                }
            }
            $xml .= '</Row>';
        }
        $xml .= '</Table></Worksheet></Workbook>';

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $namaFile . '"',
        ]);
    }

    // Query dasar daftar pesanan, dipakai bersama oleh index dan export
    private function queryPesanan(Request $request)
    {
        return Order::with(['user', 'items.product'])
            ->when($request->q, fn ($query, $q) => $query->where(function ($w) use ($q) {
                $w->where('no_invoice', 'like', "%{$q}%")
                  ->orWhereHas('user', fn ($u) => $u->where('nama', 'like', "%{$q}%"));
            }))
            ->when($request->status, fn ($query, $s) => $query->where('status_pesanan', $s));
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