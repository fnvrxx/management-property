<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesSummarySheetExport implements FromArray, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private ?array $ids = null,
        private ?int   $year = null,
    ) {}

    public function array(): array
    {
        $query = Invoice::query();

        if ($this->ids) {
            $query->whereIn('id', $this->ids);
        }
        if ($this->year) {
            $query->whereYear('tanggal_jatuh_tempo', $this->year);
        }

        $total      = (float) (clone $query)->sum('jumlah_tagihan');
        $lunas      = (float) (clone $query)->where('status_pembayaran', 'Lunas')->sum('jumlah_tagihan');
        $outstanding = (float) (clone $query)->whereIn('status_pembayaran', ['Belum Bayar', 'Terlambat'])->sum('jumlah_tagihan');

        $countTotal      = (clone $query)->count();
        $countLunas      = (clone $query)->where('status_pembayaran', 'Lunas')->count();
        $countBelumBayar = (clone $query)->where('status_pembayaran', 'Belum Bayar')->count();
        $countTerlambat  = (clone $query)->where('status_pembayaran', 'Terlambat')->count();

        $pct = $total > 0 ? round(($lunas / $total) * 100, 1) : 0;

        $label = $this->year ? "Tahun {$this->year}" : 'Semua Periode';

        return [
            ['RINGKASAN INVOICE — ' . strtoupper($label)],
            [],
            ['Keterangan',              'Jumlah Invoice', 'Total (Rp)'],
            ['Total Tagihan',           $countTotal,       $total],
            ['Sudah Lunas',             $countLunas,       $lunas],
            ['Belum Bayar',             $countBelumBayar,  $outstanding],
            ['Terlambat',               $countTerlambat,   (float) (clone $query)->where('status_pembayaran', 'Terlambat')->sum('jumlah_tagihan')],
            [],
            ['Persentase Tercapai',     '',                $pct . '%'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A5F']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}
