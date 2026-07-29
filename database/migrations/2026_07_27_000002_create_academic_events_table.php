<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('event_categories')->cascadeOnDelete();
            // class_id nullable: NULL = seluruh sekolah, diisi = khusus 1 kelas
            $table->foreignUuid('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->timestamps();

            // Index untuk query isHoliday() agar cepat
            $table->index(['start_date', 'end_date']);
            $table->index(['class_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_events');
    }
};
