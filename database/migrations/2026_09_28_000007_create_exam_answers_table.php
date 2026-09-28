<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jawaban yang dipilih siswa untuk setiap soal.
 *
 * - option_id nullable: null = soal belum dijawab (bisa terjadi jika siswa skip soal itu)
 * - answered_at: diisi setiap kali siswa mengubah/menyimpan jawaban (upsert)
 * - Unique (participant_id, question_id): satu jawaban per soal per siswa
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participant_id')->constrained('exam_participants')->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignUuid('option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            // Satu jawaban per soal per peserta
            $table->unique(['participant_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
