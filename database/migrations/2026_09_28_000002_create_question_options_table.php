<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pilihan jawaban untuk setiap soal.
 *
 * Aturan validasi (diterapkan di service/controller, bukan di DB):
 *   - Minimal 2, maksimal 5 pilihan per soal
 *   - Tepat 1 pilihan yang is_correct = true per soal
 *   - Setiap pilihan harus punya option_text ATAU image_path (atau keduanya)
 *   - Urutan A/B/C/D tetap ditentukan kolom 'position' (tidak diacak)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('question_id')->constrained('questions')->cascadeOnDelete();
            $table->text('option_text')->nullable();      // teks pilihan jawaban
            $table->string('image_path')->nullable();     // gambar pilihan jawaban (opsional)
            $table->boolean('is_correct')->default(false); // ⚠️ JANGAN dikirim ke browser siswa
            $table->unsignedTinyInteger('position');      // 1=A, 2=B, 3=C, 4=D, 5=E
            $table->timestamps();

            // Urutan position unik per soal
            $table->unique(['question_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
