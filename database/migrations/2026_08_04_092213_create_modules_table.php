<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();           // 'ppdb', 'keuangan_sekolah', dst
            $table->string('name');                    // Nama tampilan: "PPDB Online"
            $table->text('description')->nullable();   // Keterangan singkat
            $table->string('icon')->default('bi-puzzle'); // Bootstrap icon class
            $table->boolean('is_core')->default(false); // true = tidak bisa dinonaktifkan
            $table->boolean('is_active')->default(true); // status on/off
            $table->integer('sort_order')->default(0); // urutan tampil di UI
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
