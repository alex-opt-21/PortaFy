<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profile', function (Blueprint $table) {

    // NUEVOS CAMPOS PARA HU4
    $table->string('titulo')->nullable()->after('usuario_id');
    $table->text('skills')->nullable()->after('biografia');

    $table->string('github')->nullable()->after('skills');
    $table->string('linkedin')->nullable()->after('github');

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
    $table->dropColumn([
        'titulo',
        'skills',
        'github',
        'linkedin'
    ]);
});
    }
};
