<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pusat modul ujian: satu baris = satu siswa dalam satu ujian.
 *
 * Kolom penting:
 *   code           → kode unik 8 karakter per siswa (dipakai untuk masuk ujian)
 *   question_order → JSON array berisi urutan id soal yang sudah diacak (dibuat sekali, TIDAK pernah diubah)
 *   started_at     → diisi saat siswa pertama kali masuk dengan kode yang valid
 *   finished_at    → diisi saat siswa submit atau waktu habis
 *   score          → dihitung server saat finalisasi
 *   status         → dikontrol via PHP Enum ExamParticipantStatus
 *   grade_status   → dikontrol via PHP Enum ExamGradeStatus
 *   allowed_from/until → jendela waktu khusus ujian susulan (null = tidak ada susulan)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('code', 8)->unique();          // kode unik per siswa
            $table->json('question_order')->nullable();   // urutan soal JSON, dibuat sekali
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();   // 0.00 – 100.00
            // ExamParticipantStatus: 'belum_mulai'|'mengerjakan'|'selesai'|'waktu_habis'
            $table->string('status')->default('belum_mulai');
            // Jendela waktu ujian susulan (null = tidak punya jendela susulan)
            $table->timestamp('allowed_from')->nullable();
            $table->timestamp('allowed_until')->nullable();
            // ExamGradeStatus: 'menunggu_approve'|'approved'
            $table->string('grade_status')->default('menunggu_approve');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Satu siswa hanya bisa terdaftar sekali per ujian
            $table->unique(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_participants');
    }
};
