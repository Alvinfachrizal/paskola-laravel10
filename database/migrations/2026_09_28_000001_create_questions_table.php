<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel bank soal pilihan ganda.
 * - Satu soal bisa dipakai di banyak ujian (relasi lewat exam_questions).
 * - in_bank = true  → soal tersimpan di bank soal guru (bisa dipakai ulang)
 * - in_bank = false → soal dibuat langsung saat menyusun ujian (tidak muncul di bank)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('image_path')->nullable(); // gambar pada soal (jpg/png/webp, max 2MB)
            $table->boolean('in_bank')->default(true); // false = soal ad-hoc saat menyusun ujian
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
