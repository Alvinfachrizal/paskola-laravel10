<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wave_id')->constrained('ppdb_waves')->onDelete('restrict'); // FK ke gelombang
            $table->string('registration_code')->unique();   // Kode unik pendaftaran, misal: PPDB-2025-00001
            $table->string('full_name');
            $table->string('nisn')->nullable();              // NISN bisa belum ada saat daftar
            $table->date('birth_date');
            $table->string('birth_place')->nullable();
            $table->string('gender');                        // 'laki-laki' atau 'perempuan'
            $table->text('address')->nullable();
            $table->string('parent_name');
            $table->string('parent_phone');
            $table->string('email')->nullable();             // Email untuk notifikasi status
            $table->string('login_token')->nullable();       // Token login ulang (kode pendaftaran + birth_date)
            // Status dikelola via PHP Enum (lihat App\Enums\PpdbApplicantStatus)
            // Nilai: pending, verified, need_revision, payment_pending, selected, rejected, re_registered
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();         // Catatan panitia untuk revisi atau penolakan
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_applicants');
    }
};
