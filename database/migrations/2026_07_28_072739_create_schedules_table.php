<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('time_slot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('room_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->timestamps();

            // Constraint: Satu slot waktu untuk satu guru hanya boleh 1 kali
            $table->unique(['time_slot_id', 'teacher_id', 'semester_id']);
            
            // Constraint: Satu slot waktu untuk satu kelas hanya boleh 1 kali
            $table->unique(['time_slot_id', 'class_id', 'semester_id']);
            
            // Constraint: Satu slot waktu untuk satu ruangan hanya boleh 1 kali
            $table->unique(['time_slot_id', 'room_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
