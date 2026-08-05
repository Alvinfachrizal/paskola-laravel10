<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat pembayaran untuk setiap tagihan siswa.
     * Satu student_bill bisa punya beberapa payments (misal cicilan atau bayar ulang setelah ditolak).
     * Untuk MVP: hanya metode manual (upload bukti transfer).
     * Kolom gateway_reference disiapkan untuk integrasi payment gateway di masa depan.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_bill_id')->constrained('student_bills')->cascadeOnDelete();

            // Metode bayar: sementara hanya 'manual'
            // 'gateway' disiapkan untuk Midtrans/Xendit nanti
            $table->string('method')->default('manual');

            // Bukti transfer yang diupload siswa/ortu (path ke file)
            $table->string('proof_file')->nullable();

            // Referensi dari payment gateway (dikosongkan untuk MVP manual)
            $table->string('gateway_reference')->nullable();

            $table->decimal('amount_paid', 12, 2);

            // Status dikelola via PHP Enum App\Enums\PaymentStatus
            // Nilai: pending | verified | rejected
            $table->string('status')->default('pending');

            // Admin/bendahara yang memverifikasi pembayaran ini
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->text('rejection_reason')->nullable(); // Alasan jika ditolak
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
