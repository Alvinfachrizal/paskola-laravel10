<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tagihan aktual per siswa.
     * Satu baris = satu tagihan untuk satu siswa.
     * Pembayarannya dicatat di tabel 'payments'.
     */
    public function up(): void
    {
        Schema::create('student_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('bill_type_id')->constrained('bill_types')->cascadeOnDelete();

            // period: diisi untuk tagihan recurring, format "YYYY-MM" (misal "2026-08").
            // Null untuk tagihan one-time seperti uang gedung atau seragam.
            $table->string('period', 7)->nullable();

            // amount bisa berbeda dari default_amount (misal siswa dapat keringanan)
            $table->decimal('amount', 12, 2);
            $table->date('due_date')->nullable();

            // Status dikelola via PHP Enum App\Enums\StudentBillStatus
            // Nilai: belum_bayar | menunggu_verifikasi | lunas | terlambat
            $table->string('status')->default('belum_bayar');

            $table->text('notes')->nullable();
            $table->timestamps();

            // Cegah duplikasi tagihan recurring: satu siswa hanya boleh 1 baris per bill_type per periode
            $table->unique(['student_id', 'bill_type_id', 'period'], 'unique_bill_per_student_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_bills');
    }
};
