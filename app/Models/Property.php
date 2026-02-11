<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;
    protected $fillable = ['kode_lokasi', 'nama', 'status', 'catatan'];
    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function currentLease()
    {
        return $this->hasOne(Lease::class)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_akhir', '>=', now());
    }
}
