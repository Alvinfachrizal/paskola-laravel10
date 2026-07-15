<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('ppdb_applicants')->onDelete('cascade');
            // Tipe pembayaran: 'registration_fee' (biaya pendaftaran) atau 're_registration_fee' (daftar ulang)
            $table->string('payment_type');
            $table->decimal('amount', 12, 2);
            // Status: pending, paid, failed, expired — via PHP Enum App\Enums\PpdbPaymentStatus
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();  // Transfer, tunai, dll.
            $table->string('proof_path')->nullable();       // Bukti transfer yang diupload
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_payments');
    }
};
