<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jenis tagihan yang bisa dibuat admin.
     * Contoh: SPP (recurring), Uang Gedung (initial), Seragam (initial).
     * Dibuat fleksibel (data master) agar bisa disesuaikan tiap sekolah.
     */
    public function up(): void
    {
        Schema::create('bill_types', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');                          // Nama tagihan, contoh: "SPP Juli 2026"
            $table->text('description')->nullable();
            $table->boolean('is_recurring')->default(false); // true = tagihan berulang (SPP tiap bulan)
            $table->boolean('is_initial_bill')->default(false); // true = auto-generate saat siswa baru masuk
            $table->decimal('default_amount', 12, 2)->default(0); // Nominal default, bisa di-override per siswa
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_types');
    }
};
