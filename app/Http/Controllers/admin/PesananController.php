<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Pelanggan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        'menunggu_pembayaran', 'menunggu_dikonfirmasi', 'dikonfirmasi', 'diproses',
        'dalam_pengiriman', 'siap_diambil', 'selesai', 'dibatalkan',
    ];

    private const METODE = ['bayar_di_toko', 'QRIS', 'transfer_bank', 'DP'];

    // READ: daftar + statistik + pencarian + filter status
    public function index(Request $request)
    {
        Pelanggan::sinkronDariUser(); // pelanggan baru dari website otomatis masuk ke tabel Pelanggan

        $stat = [
            'total'    => Order::whereMonth('dibuat_pada', now()->month)
                               ->whereYear('dibuat_pada', now()->year)->count(),
            'diproses' => Order::where('status_pesanan', 'diproses')->count(),
            'siap'     => Order::where('status_pesanan', 'siap_diambil')->count(),
            'selesai'  => Order::where('status_pesanan', 'selesai')
                               ->whereDate('diubah_pada', today())->count(),
        ];

        // Daftar = semua pesanan + pelanggan yang belum pernah memesan.
        // Dua sumber itu digabung (UNION) lalu dipaginasi, supaya pelanggan baru langsung muncul di sini.
        $status = $request->status;
        $kata   = $request->q;

        $sumberOrder = $this->queryPesanan($request)->toBase()
            ->select([DB::raw("'order' as tipe"), 'id_order as ref_id', 'dibuat_pada as tgl']);

        $sumberPelanggan = Pelanggan::query()
            ->whereNotExists(fn ($w) => $w->select(DB::raw(1))->from('orders')
                ->whereColumn('orders.id_user', 'pelanggan.id_user'))
            ->when($kata, fn ($w, $k) => $w->where(fn ($x) => $x
                ->where('nama', 'like', "%{$k}%")
                ->orWhere('email', 'like', "%{$k}%")
                ->orWhere('kontak', 'like', "%{$k}%")))
            ->toBase()
            ->select([DB::raw("'pelanggan' as tipe"), 'id as ref_id', 'created_at as tgl']);

        $tanggal = $this->tanggalFilter($request);

        if ($status === 'belum_memesan') {
            // filter: hanya pelanggan yang belum memesan (tidak punya tanggal pengiriman, jadi kosong kalau tanggal dipilih)
            $sumber = $tanggal ? $sumberPelanggan->whereRaw('1 = 0') : $sumberPelanggan;
        } elseif ($status || $tanggal) {
            $sumber = $sumberOrder;                     // filter status / tanggal: hanya pesanan
        } else {
            $sumber = $sumberOrder->unionAll($sumberPelanggan);
        }

        $pesanan = DB::query()->fromSub($sumber, 'u')
            ->orderByDesc('tgl')->orderByDesc('ref_id')
            ->paginate(5)
            ->withQueryString(); // filter tidak hilang saat pindah halaman

        // Ambil data lengkap hanya untuk baris di halaman ini
        $baris   = $pesanan->getCollection();
        $orders  = Order::with(['pelanggan', 'user', 'items.product'])
            ->whereIn('id_order', $baris->where('tipe', 'order')->pluck('ref_id'))
            ->get()->keyBy('id_order');
        $semuaPelanggan = Pelanggan::whereIn('id', $baris->where('tipe', 'pelanggan')->pluck('ref_id'))
            ->get()->keyBy('id');

        $pesanan->setCollection($baris->map(fn ($r) => $r->tipe === 'order'
            ? ['order' => $orders[$r->ref_id] ?? null, 'pelanggan' => null]
            : ['order' => null, 'pelanggan' => $semuaPelanggan[$r->ref_id] ?? null]
        )->filter(fn ($r) => $r['order'] || $r['pelanggan'])->values());

        return view('admin.pesanan-index', compact('pesanan', 'stat', 'tanggal'));
    }

    // Export Excel. Ikut filter pencarian/status yang sedang aktif di daftar.
    // - Kalau library phpoffice/phpspreadsheet sudah terpasang -> file .xlsx (lebih rapi)
    // - Kalau belum -> otomatis pakai file .xls (XML Spreadsheet), tanpa library apa pun
    public function export(Request $request)
    {
        $label = [
            'menunggu_pembayaran' => 'Menunggu Pembayaran', 'menunggu_dikonfirmasi' => 'Menunggu Dikonfirmasi',
            'dikonfirmasi' => 'Dikonfirmasi', 'diproses' => 'Diproses', 'dalam_pengiriman' => 'Dalam Pengiriman',
            'siap_diambil' => 'Siap Kirim', 'selesai' => 'Selesai', 'dibatalkan' => 'Batal',
        ];
        $metode = ['bayar_di_toko' => 'Tunai', 'QRIS' => 'QRIS', 'transfer_bank' => 'Transfer Bank', 'DP' => 'DP'];
        $bayar  = ['belum_dibayar' => 'Belum Dibayar', 'proses' => 'Proses', 'lunas' => 'Lunas'];

        $judul = [
            'No', 'ID Pesanan', 'Tanggal Order', 'Nama Pelanggan', 'Email', 'Kontak', 'Kategori', 'Item Produk',
            'Tgl Pengiriman', 'Metode Pembayaran', 'Status Pembayaran', 'Status Pesanan',
            'Total (Rp)', 'Jumlah DP (Rp)', 'Sisa Pembayaran (Rp)',
        ];

        $data = [];
        $rows = $this->queryPesanan($request)->orderByDesc('id_order')->get();
        foreach ($rows as $i => $o) {
            $item = $o->items
                ->map(fn ($it) => ($it->product->nama_produk ?? '-') . ' x' . $it->jumlah)
                ->implode('; ');

            $total = (int) $o->total_harga;
            $dp    = (int) $o->jumlah_dp;
            $sisa  = $o->status_pembayaran === 'lunas' ? 0 : $total - $dp;

            $data[] = [
                $i + 1,
                (string) $o->no_invoice,
                $o->dibuat_pada ? $o->dibuat_pada->format('d-m-Y H:i') : '-',
                $o->nama_pelanggan,
                $o->email_pelanggan,
                (string) $o->kontak_pelanggan,
                $o->kategori_pelanggan ?? '-',
                $item,
                $o->jadwal_pengambilan ? $o->jadwal_pengambilan->format('d-m-Y H:i') : '-',
                $metode[$o->metode_pembayaran] ?? $o->metode_pembayaran,
                $bayar[$o->status_pembayaran] ?? $o->status_pembayaran,
                $label[$o->status_pesanan] ?? $o->status_pesanan,
                $total,
                $dp,
                $sisa,
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
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7A1C34']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->freezePane('A2'); // baris judul tetap terlihat saat scroll

        $r = 2;
        foreach ($data as $baris) {
            $kontak = $baris[5];
            $baris[5] = null;
            $sheet->fromArray($baris, null, "A{$r}");
            // Paksa jadi teks supaya angka 0 di depan nomor telepon tidak hilang
            $sheet->setCellValueExplicit("F{$r}", $kontak, DataType::TYPE_STRING);
            $r++;
        }

        if ($r > 2) {
            $sheet->getStyle('M2:O' . ($r - 1))->getNumberFormat()->setFormatCode('#,##0');
        }
        foreach (range('A', 'O') as $kolom) {
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
        $lebar = [35, 80, 100, 130, 170, 100, 70, 260, 100, 120, 120, 130, 90, 90, 110];

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
                } elseif ($k >= 12) { // Total, Jumlah DP, Sisa
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
        return Order::with(['pelanggan', 'user', 'items.product'])
            ->when($request->q, fn ($query, $q) => $query->where(function ($w) use ($q) {
                $w->where('no_invoice', 'like', "%{$q}%")
                  ->orWhereHas('pelanggan', fn ($p) => $p->where('nama', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('kontak', 'like', "%{$q}%"))
                  ->orWhereHas('user', fn ($u) => $u->where('nama', 'like', "%{$q}%"));
            }))
            ->when($request->status, fn ($query, $s) => $query->where('status_pesanan', $s))
            ->when($this->tanggalFilter($request), fn ($query, $t) => $query->whereDate('jadwal_pengambilan', $t));
    }

    // Filter kalender di topbar: tanggal pengiriman (format Y-m-d). Kosong atau tidak valid = tanpa filter.
    private function tanggalFilter(Request $request): ?string
    {
        $t = (string) $request->query('tanggal');

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $t) ? $t : null;
    }

    // CREATE: tampilkan form
    public function create()
    {
        // Hanya produk yang berstatus Tersedia (aktif, dan stok > 0 untuk jenis yang dihitung per stok)
        $produk = Product::with('subcategory')->where('is_active', true)->orderBy('nama_produk')->get()
            ->filter(fn ($p) => $p->status === 'Tersedia')
            ->values();

        return view('admin.pesanan-create', compact('produk'));
    }

    // CREATE: simpan
    public function store(Request $request)
    {
        $data = $request->validate([
            'metode_pembayaran'  => ['required', Rule::in(self::METODE)],
            'jumlah_dp'          => ['nullable', 'required_if:metode_pembayaran,DP', 'integer', 'min:1'],
            'status_pembayaran'  => ['required', Rule::in(['belum_dibayar', 'proses', 'lunas'])],
            'status_pesanan'     => ['required', Rule::in(self::STATUS_PESANAN)],
            'jadwal_pengambilan' => 'nullable|date',
            'items'              => 'required|array|min:1',
            'items.*.id_product' => ['required', 'distinct', Rule::exists('products', 'id_product')],
            'items.*.jumlah'     => 'required|integer|min:1',
        ], [
            'required'    => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi untuk pembayaran DP.',
            'exists'      => ':attribute tidak valid.',
            'in'          => ':attribute tidak valid.',
            'integer'     => ':attribute harus berupa angka.',
            'min'         => ':attribute minimal :min.',
            'distinct'    => 'Produk tidak boleh dipilih dua kali.',
            'date'        => 'Format tanggal tidak valid.',
        ], [
            'metode_pembayaran'  => 'Metode pembayaran',
            'jumlah_dp'          => 'Jumlah DP',
            'status_pembayaran'  => 'Status pembayaran',
            'status_pesanan'     => 'Status pesanan',
            'jadwal_pengambilan' => 'Jadwal pengiriman',
            'items'              => 'Item produk',
            'items.*.id_product' => 'Produk',
            'items.*.jumlah'     => 'Jumlah',
        ]);

        $order = DB::transaction(function () use ($data) {
            $total = 0;
            $baris = [];

            foreach ($data['items'] as $i => $item) {
                $produk = Product::with('subcategory')->lockForUpdate()->find($item['id_product']);

                if ($produk->status !== 'Tersedia') {
                    throw ValidationException::withMessages([
                        "items.$i.id_product" => "{$produk->nama_produk} sedang tidak tersedia.",
                    ]);
                }

                // Stok hanya dihitung untuk jenis produk yang memakai stok (Buket Fresh Flower)
                if ($produk->pakai_stok) {
                    if ($produk->stok < $item['jumlah']) {
                        throw ValidationException::withMessages([
                            "items.$i.jumlah" => "Stok {$produk->nama_produk} tinggal {$produk->stok}.",
                        ]);
                    }
                    $produk->decrement('stok', $item['jumlah']);
                }

                $total += $produk->harga * $item['jumlah'];
                $baris[] = [
                    'id_product'   => $produk->id_product,
                    'jumlah'       => $item['jumlah'],
                    'harga_satuan' => $produk->harga, // harga dikunci saat pesanan dibuat
                ];
            }

            // Pembayaran DP: nominal harus lebih kecil dari total, status pembayaran otomatis "proses" (belum lunas)
            $dp = null;
            $statusBayar = $data['status_pembayaran'];
            if ($data['metode_pembayaran'] === 'DP') {
                $dp = (int) $data['jumlah_dp'];
                if ($dp >= $total) {
                    throw ValidationException::withMessages([
                        'jumlah_dp' => 'Jumlah DP harus lebih kecil dari total harga (Rp ' . number_format($total, 0, ',', '.') . ').',
                    ]);
                }
                $statusBayar = 'proses';
            }

            $order = Order::create([
                'no_invoice'         => $this->buatInvoice(),
                'id_user'            => null, // pesanan dari admin (di toko), tanpa akun pelanggan
                'metode_pembayaran'  => $data['metode_pembayaran'],
                'status_pembayaran'  => $statusBayar,
                'status_pesanan'     => $data['status_pesanan'],
                'total_harga'        => $total,
                'jumlah_dp'          => $dp,
                'dibayar_pada'       => ($statusBayar === 'lunas' || $dp) ? now() : null,
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