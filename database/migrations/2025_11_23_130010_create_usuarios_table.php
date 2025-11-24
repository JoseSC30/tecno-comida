<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('usu_id');
            $table->string('usu_nombre', 255);
            $table->string('usu_apellido', 255);
            $table->string('usu_email', 255)->unique();
            $table->string('usu_password', 255);
            $table->foreignId('rol_id')->constrained('roles', 'rol_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
