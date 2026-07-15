<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_reregistrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->unique()->constrained('ppdb_applicants')->onDelete('cascade'); // 1-ke-1
            // Status: pending, completed — via PHP Enum App\Enums\PpdbReregistrationStatus
            $table->string('status')->default('pending');
            // FK ke tabel students SETELAH pendaftar resmi jadi siswa
            // Nullable karena siswa baru dibuat setelah daftar ulang selesai
            $table->foreignUuid('student_id')->nullable()->constrained('students')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_reregistrations');
    }
};
