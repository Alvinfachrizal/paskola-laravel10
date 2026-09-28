<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Relasi many-to-many antara ujian dan soal.
 * Setiap soal bisa punya poin berbeda di ujian yang berbeda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained('questions')->cascadeOnDelete();
            $table->decimal('points', 5, 2)->default(1.00); // poin soal ini dalam ujian
            $table->timestamps();

            // Satu soal hanya bisa muncul sekali per ujian
            $table->unique(['exam_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
