<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExport
{
    private const MAROON = '7A1630';
    private const BLUSH  = 'FBEFF2';
    private const LINE   = 'E6CFD5';
    private const HIJAU  = '1E7A34';
    private const MERAH  = 'B3261E';

    /**
     * @param array $kolom [[judul, lebar, tipe], ...]  tipe: teks | angka | rupiah | tengah | status
     * @param iterable $baris tiap baris berupa array berurutan sesuai $kolom
     */
    public static function download(string $nama, string $judul, array $kolom, iterable $baris): StreamedResponse
    {
        $sp = new Spreadsheet();
        $ws = $sp->getActiveSheet();
        $ws->setTitle(mb_substr($judul, 0, 31));
        $ws->setShowGridlines(false);

        $jumlahKolom = count($kolom);
        $akhir = Coordinate::stringFromColumnIndex($jumlahKolom);
        $head = 4;

        $ws->mergeCells("A1:{$akhir}1");
        $ws->setCellValue('A1', $judul);
        $ws->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => self::MAROON]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(30);

        $ws->mergeCells("A2:{$akhir}2");
        $ws->setCellValue('A2', 'Royal Florist • Diekspor ' . now()->format('d/m/Y H:i'));
        $ws->getStyle('A2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '8A6B73']]]);

        foreach ($kolom as $i => $k) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $ws->setCellValue("{$col}{$head}", $k[0]);
            $ws->getColumnDimension($col)->setWidth($k[1]);
        }
        $ws->getStyle("A{$head}:{$akhir}{$head}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::MAROON]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $ws->getRowDimension($head)->setRowHeight(26);

        $r = $head;
        foreach ($baris as $row) {
            $r++;
            $row = array_values($row);

            foreach ($kolom as $i => $k) {
                $sel = Coordinate::stringFromColumnIndex($i + 1) . $r;
                $tipe = $k[2] ?? 'teks';
                $nilai = $row[$i] ?? null;

                if (in_array($tipe, ['angka', 'rupiah'], true)) {
                    if ($nilai !== null && $nilai !== '') {
                        $ws->setCellValue($sel, (float) $nilai);
                    }
                } else {
                    $ws->setCellValueExplicit($sel, (string) $nilai, DataType::TYPE_STRING);
                }

                if ($tipe === 'status') {
                    $baik = in_array(mb_strtolower((string) $nilai), ['tersedia', 'aktif'], true);
                    $ws->getStyle($sel)->getFont()->setBold(true)->getColor()->setRGB($baik ? self::HIJAU : self::MERAH);
                }
            }

            if (($r - $head) % 2 === 0) {
                $ws->getStyle("A{$r}:{$akhir}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BLUSH);
            }
        }

        $jumlahData = $r - $head;

        if ($jumlahData > 0) {
            $ws->getStyle("A" . ($head + 1) . ":{$akhir}{$r}")->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::LINE]]],
            ]);

            foreach ($kolom as $i => $k) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $area = $ws->getStyle("{$col}" . ($head + 1) . ":{$col}{$r}");
                $tipe = $k[2] ?? 'teks';

                if ($tipe === 'rupiah') {
                    $area->getNumberFormat()->setFormatCode('"Rp" #,##0');
                    $area->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                } elseif ($tipe === 'angka') {
                    $area->getNumberFormat()->setFormatCode('#,##0');
                    $area->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } elseif ($tipe === 'tengah' || $tipe === 'status') {
                    $area->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            }

            $ws->setAutoFilter("A{$head}:{$akhir}{$r}");
        } else {
            $ws->mergeCells("A" . ($head + 1) . ":{$akhir}" . ($head + 1));
            $ws->setCellValue('A' . ($head + 1), 'Belum ada data.');
            $ws->getStyle('A' . ($head + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r = $head + 1;
        }

        $ws->setCellValue('A' . ($r + 2), "Total: {$jumlahData} data");
        $ws->getStyle('A' . ($r + 2))->getFont()->setBold(true);

        $ws->freezePane('A' . ($head + 1));
        $ws->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setRowsToRepeatAtTopByStartAndEnd($head, $head);

        $file = $nama . '-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($sp) {
            (new Xlsx($sp))->save('php://output');
            $sp->disconnectWorksheets();
        }, $file, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
