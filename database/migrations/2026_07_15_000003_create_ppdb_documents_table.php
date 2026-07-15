<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('ppdb_applicants')->onDelete('cascade');
            // Tipe dokumen: 'kk', 'akta_lahir', 'foto', 'ijazah', 'skhun', dll.
            $table->string('doc_type');
            $table->string('file_path');                   // Path file yang diupload di storage
            $table->string('original_name')->nullable();   // Nama file asli dari user
            // Status: pending, valid, invalid — dikelola via PHP Enum App\Enums\PpdbDocumentStatus
            $table->string('status')->default('pending');
            $table->text('rejection_notes')->nullable();   // Catatan alasan penolakan dari panitia
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_documents');
    }
};
