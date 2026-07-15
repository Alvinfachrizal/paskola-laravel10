<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_uniform_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->unique()->constrained('ppdb_applicants')->onDelete('cascade'); // 1-ke-1
            $table->string('gender');                        // 'laki-laki' atau 'perempuan'
            // Hanya relevan jika gender = perempuan. null jika laki-laki (backend set otomatis)
            $table->boolean('pakai_kerudung')->default(false);
            // 'rok' atau 'celana'. Backend set 'celana' otomatis jika gender = laki-laki
            $table->string('jenis_bawahan')->nullable();
            // Ukuran seragam: S, M, L, XL, XXL — WAJIB diisi
            $table->string('ukuran');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_uniform_orders');
    }
};
