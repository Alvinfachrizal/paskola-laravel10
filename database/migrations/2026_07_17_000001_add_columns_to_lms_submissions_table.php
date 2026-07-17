<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom yang dibutuhkan di lms_submissions:
 * - file_path   → path file yang disimpan di storage (sebelumnya file_url)
 * - notes       → catatan dari siswa saat mengumpulkan
 * - graded_at   → kapan guru memberi nilai
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_submissions', function (Blueprint $table) {
            // Tambah file_path sebagai alias yang lebih standar (file_url tetap ada)
            $table->string('file_path')->nullable()->after('file_url');
            $table->text('notes')->nullable()->after('text_content');
            $table->timestamp('graded_at')->nullable()->after('feedback');
        });
    }

    public function down(): void
    {
        Schema::table('lms_submissions', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'notes', 'graded_at']);
        });
    }
};
