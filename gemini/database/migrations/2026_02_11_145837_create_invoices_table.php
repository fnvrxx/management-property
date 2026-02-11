<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->onDelete('cascade');
            $table->string('bulan_tahun'); // e.g., "Februari 2026"
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('jumlah_tagihan', 15, 2);
            $table->enum('status_pembayaran', ['Belum Bayar', 'Lunas', 'Terlambat'])->default('Belum Bayar');
            $table->date('tanggal_bayar')->nullable();
            $table->text('catatan_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
