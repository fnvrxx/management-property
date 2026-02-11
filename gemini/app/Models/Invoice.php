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

    // Hitung sisa hari ke jatuh tempo
    public function getSisaHariAttribute()
    {
        return now()->diffInDays($this->tanggal_jatuh_tempo, false);
    }
}
