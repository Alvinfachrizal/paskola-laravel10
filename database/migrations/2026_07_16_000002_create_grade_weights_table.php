<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel grade_weights: bobot komponen nilai per mapel per semester.
 *
 * Guru mapel mengatur sendiri berapa persen bobot tiap komponen.
 * Aturan wajib: TOTAL weight_percent untuk satu subject_id + semester_id
 * harus = 100%. Validasi dilakukan di Form Request, bukan di DB.
 *
 * Contoh:
 *  - Matematika Sem Ganjil: tugas=20%, kuis=10%, ujian_online=20%, uts=25%, uas=25%  → total 100%
 *  - B.Indonesia Sem Ganjil: tugas=30%, uts=30%, uas=40%                              → total 100%
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_weights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();

            // Komponen nilai yang didukung
            $table->enum('component_type', [
                'tugas',        // Nilai tugas harian dari LMS
                'kuis',         // Kuis online dari LMS
                'ujian_online', // Ujian online dari LMS
                'uts',          // Ujian Tengah Semester (kertas, input manual)
                'uas',          // Ujian Akhir Semester (kertas, input manual)
                'praktik',      // Nilai praktik/praktikum (input manual)
            ]);

            // Persentase bobot, misal: 25 (berarti 25%)
            // Total semua component_type untuk subject_id + semester_id yang sama HARUS = 100
            $table->unsignedTinyInteger('weight_percent');

            $table->timestamps();

            // Satu mapel + semester tidak boleh punya dua bobot untuk komponen yang sama
            $table->unique(['subject_id', 'semester_id', 'component_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_weights');
    }
};
