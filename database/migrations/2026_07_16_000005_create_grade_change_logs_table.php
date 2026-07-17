<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel grade_change_logs: audit trail perubahan nilai.
 *
 * Setiap kali guru mapel merevisi nilai di student_grades,
 * perubahan dicatat di sini — termasuk siapa yang mengubah,
 * kapan, nilai lama, dan nilai baru.
 *
 * Ini memenuhi requirement "riwayat perubahan" dari prompt,
 * tanpa perlu install package spatie/laravel-activitylog
 * (lebih ringan, lebih sederhana).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_change_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // FK ke nilai yang diubah
            $table->foreignUuid('student_grade_id')->constrained('student_grades')->cascadeOnDelete();

            // Siapa yang mengubah
            $table->foreignUuid('changed_by')->constrained('users')->cascadeOnDelete();

            // Nilai sebelum dan sesudah diubah
            $table->decimal('old_score', 5, 2)->nullable();
            $table->decimal('new_score', 5, 2);

            // Alasan perubahan (wajib diisi saat revisi setelah verifikasi wali kelas)
            $table->text('reason')->nullable();

            $table->timestamps();

            // Index untuk mempercepat query log per nilai
            $table->index('student_grade_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_change_logs');
    }
};
