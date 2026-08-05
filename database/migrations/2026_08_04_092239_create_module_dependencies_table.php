<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_dependencies', function (Blueprint $table) {
            $table->id();
            // Modul yang PUNYA dependency (yang bergantung pada modul lain)
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            // Modul yang DIBUTUHKAN (yang harus aktif agar modul di atas bisa berjalan)
            $table->foreignId('depends_on_module_id')->constrained('modules')->cascadeOnDelete();
            $table->timestamps();

            // Pastikan tidak ada duplikasi baris yang sama
            $table->unique(['module_id', 'depends_on_module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_dependencies');
    }
};
