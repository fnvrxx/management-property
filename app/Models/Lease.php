<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    /** @use HasFactory<\Database\Factories\LeaseFactory> */
    use HasFactory;
    protected $fillable = [
        'tenant_id',
        'property_id',
        'tanggal_mulai',
        'tanggal_akhir',
        'periode',
        'harga_sewa',
        'ppn_persen',
        'ppb_persen',
        'tagihan_lainnya',
        'catatan',
    ];
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
