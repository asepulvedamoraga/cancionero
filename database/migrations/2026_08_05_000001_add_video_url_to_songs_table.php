<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table): void {
            $table->string('video_url', 2048)->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table): void {
            $table->dropColumn('video_url');
        });
    }
};