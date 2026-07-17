<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel report_cards: rapor FINAL per siswa per mapel per semester.
 *
 * Data ini dihitung dari student_grades × grade_weights.
 * Status alurnya:
 *   draft → perlu_verifikasi → terverifikasi → published
 *
 * Status dikelola sebagai PHP Enum di kode, bukan MySQL ENUM yang kaku.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();

            // Nilai akhir yang sudah dihitung pakai bobot (0–100, 2 desimal)
            $table->decimal('final_score', 5, 2)->nullable();

            // Deskripsi capaian (opsional — untuk kurikulum merdeka)
            $table->text('description')->nullable();

            // Status alur verifikasi rapor
            // draft | perlu_verifikasi | terverifikasi | published
            $table->string('status', 30)->default('draft');

            // Wali kelas yang memverifikasi (nullable sampai diverifikasi)
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // Satu siswa hanya punya satu rapor per mapel per semester
            $table->unique(['student_id', 'subject_id', 'semester_id'], 'rc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
