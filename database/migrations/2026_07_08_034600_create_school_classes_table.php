<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignUuid('major_id')->nullable()->constrained('majors')->nullOnDelete();
            $table->string('name');
            $table->integer('grade');
            $table->foreignUuid('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('room_number')->nullable();
            $table->integer('max_students')->default(36);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
