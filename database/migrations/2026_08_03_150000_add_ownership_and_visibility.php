<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['user_id', 'is_active']);
        });

        Schema::table('repertoires', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->enum('visibility', ['private', 'public'])->default('private')->after('status')->index();
            $table->boolean('allow_public_download')->default(false)->after('visibility');
            $table->index(['user_id', 'visibility']);
        });

        $hasContent = DB::table('songs')->exists() || DB::table('repertoires')->exists();
        $ownerId = DB::table('users')->where('is_admin', true)->orderBy('id')->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        if ($hasContent && ! $ownerId) {
            throw new RuntimeException('No se puede asignar propietario al contenido existente: crea primero un usuario administrador.');
        }

        if ($ownerId) {
            DB::table('songs')->whereNull('user_id')->update(['user_id' => $ownerId]);
            DB::table('repertoires')->whereNull('user_id')->update(['user_id' => $ownerId]);
        }

        Schema::table('songs', fn (Blueprint $table) => $table->foreignId('user_id')->nullable(false)->change());
        Schema::table('repertoires', fn (Blueprint $table) => $table->foreignId('user_id')->nullable(false)->change());
    }

    public function down(): void
    {
        Schema::table('repertoires', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'visibility']);
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['visibility', 'allow_public_download']);
        });

        Schema::table('songs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
