<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory;
    protected $fillable = ['nama', 'kontak', 'email', 'alamat'];
    public function leases()
    {
        return $this->hasMany(Lease::class);
    }
}
