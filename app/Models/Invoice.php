<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;
    protected $fillable = [
        'lease_id',
        'bulan_tahun',
        'tanggal_jatuh_tempo',
        'jumlah_tagihan',
        'status_pembayaran',
        'tanggal_bayar',
        'catatan_pembayaran',
    ];

    // Opsional: definisikan cast jika perlu
    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
        'jumlah_tagihan' => 'decimal:2',
    ];
    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    // Total yang sudah dibayar via riwayat pembayaran
    public function getTotalTerbayarAttribute(): float
    {
        return (float) $this->paymentHistories()->sum('jumlah_bayar');
    }

    // Sisa yang belum dibayar
    public function getSisaTagihanAttribute(): float
    {
        return max(0, (float) $this->jumlah_tagihan - $this->total_terbayar);
    }

    // Hitung sisa hari ke jatuh tempo (null jika sudah Lunas)
    public function getSisaHariAttribute(): ?int
    {
        if ($this->status_pembayaran === 'Lunas') {
            return null;
        }
        return (int) today()->diffInDays($this->tanggal_jatuh_tempo, false);
    }
}
