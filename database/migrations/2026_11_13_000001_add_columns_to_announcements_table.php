<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('is_pinned');
            $table->timestamp('expires_at')->nullable()->after('published_at');
            $table->string('priority')->default('normal')->after('expires_at'); // normal | urgent
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'expires_at', 'priority']);
        });
    }
};

