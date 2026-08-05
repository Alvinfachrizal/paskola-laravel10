<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('doc_ijazah_sd')->nullable()->after('status');
            $table->string('doc_ijazah_smp')->nullable()->after('doc_ijazah_sd');
            $table->string('doc_ijazah_sma')->nullable()->after('doc_ijazah_smp');
            $table->string('doc_ijazah_s1')->nullable()->after('doc_ijazah_sma');
            $table->string('doc_npwp')->nullable()->after('doc_ijazah_s1');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'doc_ijazah_sd',
                'doc_ijazah_smp',
                'doc_ijazah_sma',
                'doc_ijazah_s1',
                'doc_npwp'
            ]);
        });
    }
};
