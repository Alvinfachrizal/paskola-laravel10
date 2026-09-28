<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kelas-kelas yang diikutsertakan dalam sebuah ujian.
 * Satu ujian bisa ditujukan ke banyak kelas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_classes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_classes');
    }
};
