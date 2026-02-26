<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $fillable = [
        'invoice_id',
        'jenis',
        'jumlah_bayar',
        'tanggal_bayar',
        'metode_bayar',
        'referensi',
        'catatan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_bayar'  => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
