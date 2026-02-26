<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class InvoicesExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        private ?array $ids = null,
        private ?int   $year = null,
    ) {}

    public function query(): Builder
    {
        $query = Invoice::with(['lease.tenant', 'lease.property'])
            ->orderBy('tanggal_jatuh_tempo', 'asc');

        if ($this->ids) {
            $query->whereIn('id', $this->ids);
        }

        if ($this->year) {
            $query->whereYear('tanggal_jatuh_tempo', $this->year);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Penyewa',
            'Lokasi',
            'Bulan/Tahun',
            'Jatuh Tempo',
            'Jumlah Tagihan (Rp)',
            'Status',
            'Tanggal Bayar',
            'Catatan',
        ];
    }

    public function map($invoice): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $invoice->lease?->tenant?->nama ?? '-',
            $invoice->lease?->property?->kode_lokasi ?? '-',
            $invoice->bulan_tahun,
            $invoice->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-',
            (float) $invoice->jumlah_tagihan,
            $invoice->status_pembayaran,
            $invoice->tanggal_bayar?->format('d/m/Y') ?? '-',
            $invoice->catatan_pembayaran ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Baris header (baris 1) — bold, background biru tua, teks putih
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A5F']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function title(): string
    {
        return 'Data Invoice';
    }
}
