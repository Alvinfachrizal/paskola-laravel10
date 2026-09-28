<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel ujian utama yang dibuat guru.
 *
 * - status disimpan sebagai string (dikontrol lewat PHP Enum ExamStatus)
 * - show_score dikontrol lewat PHP Enum ExamShowScore
 * - Guru hanya bisa membuat ujian untuk mapel + kelas yang dia ajar
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_at');                       // jadwal mulai ujian
            $table->timestamp('end_at');                         // jadwal berakhir ujian
            $table->unsignedSmallInteger('duration_minutes');    // durasi pengerjaan (menit)
            // ExamShowScore: 'after_submit' | 'after_approve'
            $table->string('show_score')->default('after_approve');
            // ExamStatus: 'draft' | 'published' | 'closed'
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
