<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Lease;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueProjectionWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    /**
     * Tentukan tahun proyeksi untuk sebuah kontrak.
     *
     * Aturan:
     *   - Kontrak mulai Jan–Jun → masuk proyeksi tahun mulai
     *   - Kontrak mulai Jul–Des → digeser ke proyeksi tahun berikutnya
     */
    protected function getProjectionYear(Lease $lease): int
    {
        $start = Carbon::instance($lease->tanggal_mulai);

        return $start->month >= 7
            ? $start->year + 1
            : $start->year;
    }

    protected function getStats(): array
    {
        $year      = now()->year;
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd   = Carbon::create($year, 12, 31)->endOfDay();

        $projection   = 0.0;
        $leaseCount   = 0;

        $leases = Lease::all();
        foreach ($leases as $lease) {
            // Lewati kontrak yang tahun proyeksinya bukan tahun ini
            if ($this->getProjectionYear($lease) !== $year) {
                continue;
            }

            $leaseStart = Carbon::instance($lease->tanggal_mulai);
            $leaseEnd   = Carbon::instance($lease->tanggal_akhir);

            // Irisan antara periode kontrak dan tahun fiskal ini
            $overlapStart = $leaseStart->max($yearStart);
            $overlapEnd   = $leaseEnd->min($yearEnd);

            if ($overlapStart->gt($overlapEnd)) {
                continue;
            }

            // Hitung jumlah bulan overlap
            $months = 0;
            $cursor = $overlapStart->copy()->startOfMonth();
            while ($cursor->lte($overlapEnd)) {
                $months++;
                $cursor->addMonth();
            }

            $harga    = (float) $lease->harga_sewa;
            $ppn      = $harga * ($lease->ppn_persen / 100);
            $ppb      = $harga * ($lease->ppb_persen / 100);
            $lain     = collect($lease->tagihan_lainnya ?? [])->sum('jumlah');
            $perBulan = $harga + $ppn + $ppb + $lain;

            $projection += $perBulan * $months;
            $leaseCount++;
        }

        // Sudah Masuk: invoice Lunas dalam tahun ini
        $collected = (float) Invoice::where('status_pembayaran', 'Lunas')
            ->whereYear('tanggal_jatuh_tempo', $year)
            ->sum('jumlah_tagihan');

        // Belum Masuk: invoice Belum Bayar + Terlambat dalam tahun ini
        $outstanding = (float) Invoice::whereIn('status_pembayaran', ['Belum Bayar', 'Terlambat'])
            ->whereYear('tanggal_jatuh_tempo', $year)
            ->sum('jumlah_tagihan');

        $outstandingCount = Invoice::whereIn('status_pembayaran', ['Belum Bayar', 'Terlambat'])
            ->whereYear('tanggal_jatuh_tempo', $year)
            ->count();

        $pct = $projection > 0
            ? round(($collected / $projection) * 100, 1)
            : 0;

        return [
            Stat::make("Proyeksi Pemasukan {$year}", 'Rp ' . number_format($projection, 0, ',', '.'))
                ->description("{$leaseCount} kontrak (mulai Jan–Jun {$year} atau lanjutan dari tahun sebelumnya)")
                ->color('primary'),

            Stat::make('Sudah Masuk', 'Rp ' . number_format($collected, 0, ',', '.'))
                ->description("{$pct}% dari proyeksi tercapai")
                ->color('success'),

            Stat::make('Belum Masuk', 'Rp ' . number_format($outstanding, 0, ',', '.'))
                ->description("{$outstandingCount} invoice outstanding")
                ->color($outstandingCount > 0 ? 'danger' : 'gray'),
        ];
    }
}
