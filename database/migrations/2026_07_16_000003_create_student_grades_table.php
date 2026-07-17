<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel student_grades: nilai MENTAH per siswa per mapel per semester.
 *
 * Ini adalah TITIK PERTEMUAN antara modul LMS dan Rapor:
 * - Nilai dari tugas/kuis/ujian online LMS → source='lms', diisi otomatis
 * - Nilai UTS/UAS/praktik kertas → source='manual', diisi guru secara manual
 *
 * Satu baris = satu komponen nilai satu siswa di satu mapel satu semester.
 * Contoh: Budi | Matematika | Sem Ganjil | tugas | 85 | lms
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();

            // FK opsional ke lms_assignments (hanya diisi jika source='lms')
            $table->foreignUuid('assignment_id')->nullable()->constrained('lms_assignments')->nullOnDelete();

            // Komponen nilai (harus sama dengan pilihan di grade_weights)
            $table->enum('component_type', [
                'tugas', 'kuis', 'ujian_online', 'uts', 'uas', 'praktik',
            ]);

            // Nilai 0–100
            $table->decimal('score', 5, 2); // misal: 87.50

            // Asal nilai: 'lms' (otomatis dari sistem) atau 'manual' (diinput guru)
            $table->enum('source', ['lms', 'manual'])->default('manual');

            // Siapa yang menginput (guru mapel atau sistem)
            $table->foreignUuid('inputted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable(); // catatan opsional dari guru

            $table->timestamps();

            // Satu siswa hanya punya satu nilai per komponen per mapel per semester
            // (jika LMS punya banyak tugas, nilai yang masuk ke sini adalah rata-ratanya)
            $table->unique(['student_id', 'subject_id', 'semester_id', 'component_type'], 'sg_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
