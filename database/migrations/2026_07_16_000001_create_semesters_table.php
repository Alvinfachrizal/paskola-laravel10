<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel semesters: menyimpan data semester ganjil/genap
 * per tahun ajaran. Setiap nilai & rapor TERIKAT ke semester ini.
 *
 * Contoh: Tahun Ajaran 2025/2026 → Semester Ganjil (1) & Genap (2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();

            // semester ke-1 (ganjil) atau ke-2 (genap)
            $table->unsignedTinyInteger('term'); // 1 atau 2

            // Tahun ajaran, misal: 2025 (artinya 2025/2026)
            $table->unsignedSmallInteger('academic_year');

            // Label tampilan: "Ganjil 2025/2026"
            $table->string('name', 50);

            // Tanggal mulai & selesai semester
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Apakah ini semester yang sedang aktif?
            $table->boolean('is_active')->default(false);

            $table->timestamps();

            // Satu sekolah tidak boleh punya dua semester yang sama
            $table->unique(['school_id', 'academic_year', 'term']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
