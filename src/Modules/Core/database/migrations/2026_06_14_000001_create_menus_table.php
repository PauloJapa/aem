<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('menus')
                  ->nullOnDelete();
            $table->string('label', 80);
            $table->string('icon', 60)->default('pi pi-circle');
            $table->string('rota', 120)->nullable();
            $table->string('permission', 120)->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'ordem', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
