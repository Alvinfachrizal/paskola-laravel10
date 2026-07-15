<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_waves', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Nama gelombang, misal "Gelombang 1"
            $table->integer('quota');                        // Kuota penerimaan siswa baru
            $table->date('start_date');                      // Tanggal mulai pendaftaran
            $table->date('end_date');                        // Tanggal tutup pendaftaran
            $table->decimal('registration_fee', 12, 2)->default(0); // Biaya pendaftaran. 0 = gratis
            $table->boolean('is_active')->default(true);     // Apakah gelombang ini sedang aktif
            $table->text('description')->nullable();         // Keterangan tambahan (opsional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_waves');
    }
};
