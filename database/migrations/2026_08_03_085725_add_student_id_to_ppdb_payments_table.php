<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Patch: Tambah kolom student_id (nullable FK ke students) di tabel ppdb_payments.
     * Kolom ini terisi setelah pendaftar lolos daftar ulang dan data siswa resmi dibuat.
     * Data ppdb_payments yang sudah ada TIDAK terpengaruh (nullable).
     */
    public function up(): void
    {
        Schema::table('ppdb_payments', function (Blueprint $table) {
            // students.id adalah UUID, bukan BIGINT, jadi harus pakai foreignUuid
            $table->foreignUuid('student_id')
                ->nullable()
                ->after('applicant_id')
                ->constrained('students')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_payments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};

