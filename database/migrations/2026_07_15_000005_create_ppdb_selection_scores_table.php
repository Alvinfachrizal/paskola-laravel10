<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_selection_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('ppdb_applicants')->onDelete('cascade');
            // Tipe nilai: 'matematika', 'bahasa_indonesia', 'ipa', 'wawancara', dll. — fleksibel
            $table->string('score_type');
            $table->decimal('score_value', 5, 2);           // Nilai, misal 85.50
            $table->foreignUuid('inputted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Satu pendaftar tidak boleh punya 2 entry untuk jenis nilai yang sama
            $table->unique(['applicant_id', 'score_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_selection_scores');
    }
};
