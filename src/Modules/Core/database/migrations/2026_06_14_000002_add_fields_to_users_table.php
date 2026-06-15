<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefone', 20)->nullable()->after('name');
            $table->string('avatar_url')->nullable()->after('telefone');
            $table->boolean('ativo')->default(true)->after('email');
            $table->json('permissions_bloqueadas')->nullable()->after('ativo');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['telefone', 'avatar_url', 'ativo', 'permissions_bloqueadas']);
        });
    }
};
