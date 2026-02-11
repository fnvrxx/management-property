<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate';
    protected $description = 'Generate monthly invoices for active leases';

    public function handle()
    {
        $this->info('🔍 Mencari kontrak aktif...');

        // Ambil semua kontrak aktif (masih dalam periode)
        $activeLeases = Lease::where('tanggal_mulai', '<=', now())
            ->where('tanggal_akhir', '>=', now())
            ->with('invoices')
            ->get();

        if ($activeLeases->isEmpty()) {
            $this->warn('⚠️ Tidak ada kontrak aktif ditemukan.');
            return;
        }

        $generatedCount = 0;

        foreach ($activeLeases as $lease) {
            // Hitung jumlah bulan sejak mulai kontrak
            $startDate = Carbon::parse($lease->tanggal_mulai);
            $endDate = Carbon::parse($lease->tanggal_akhir);
            $currentDate = now();

            // Pastikan tidak melebihi akhir kontrak
            if ($currentDate > $endDate)
                continue;

            // Hitung bulan ke-n sejak mulai
            $monthsDiff = $startDate->diffInMonths($currentDate, false);

            // Tanggal jatuh tempo bulan ini
            $dueDate = $startDate->copy()->addMonths($monthsDiff)->endOfMonth()->startOfDay();
            // Atau jika ingin tanggal sama seperti mulai:
            // $dueDate = $startDate->copy()->addMonths($monthsDiff);

            // Format: "Februari 2026"
            $bulanTahun = $dueDate->translatedFormat('F Y');

            // Cek apakah invoice untuk bulan ini sudah ada
            $existing = $lease->invoices()->where('bulan_tahun', $bulanTahun)->first();
            if ($existing) {
                $this->info("✅ Sudah ada: {$lease->tenant->nama} - {$bulanTahun}");
                continue;
            }

            // Hitung total tagihan
            $harga = $lease->harga_sewa;
            $ppn = $harga * ($lease->ppn_persen / 100);
            $ppb = $harga * ($lease->ppb_persen / 100);

            // Tambah tagihan lainnya (jika ada)
            $tagihanLain = 0;
            if ($lease->tagihan_lainnya) {
                $items = json_decode($lease->tagihan_lainnya, true);
                if (is_array($items)) {
                    $tagihanLain = collect($items)->sum('jumlah');
                }
            }

            $total = $harga + $ppn + $ppb + $tagihanLain;

            // Buat invoice
            Invoice::create([
                'lease_id' => $lease->id,
                'bulan_tahun' => $bulanTahun,
                'tanggal_jatuh_tempo' => $dueDate,
                'jumlah_tagihan' => round($total, 2),
                'status_pembayaran' => 'Belum Bayar',
            ]);

            $this->info("🆕 Invoice dibuat: {$lease->tenant->nama} - {$bulanTahun} (Rp " . number_format($total, 0, ',', '.') . ")");
            $generatedCount++;
        }

        $this->info("✅ Selesai! {$generatedCount} invoice baru dibuat.");
    }
}
