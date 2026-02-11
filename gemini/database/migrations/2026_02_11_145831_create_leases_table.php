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
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');
            $table->string('periode'); // e.g., "1 tahun"
            $table->decimal('harga_sewa', 15, 2);
            $table->decimal('ppn_persen', 5, 2)->default(11.00); // bisa di-edit
            $table->decimal('ppb_persen', 5, 2)->default(0.00); // opsional
            $table->json('tagihan_lainnya')->nullable(); // custom biaya
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
