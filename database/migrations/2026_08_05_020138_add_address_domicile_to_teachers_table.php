<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Rename kolom lama
        Schema::table('teachers', function (Blueprint $table) {
            $table->renameColumn('address', 'address_ktp');
        });

        // Step 2: Tambah kolom baru setelah rename selesai
        Schema::table('teachers', function (Blueprint $table) {
            $table->text('address_domicile')->nullable()->after('address_ktp');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('address_domicile');
            $table->renameColumn('address_ktp', 'address');
        });
    }
};
