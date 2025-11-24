<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id('res_id');
            $table->date('res_fecha');
            $table->time('res_hora');
            $table->foreignId('usu_id')->constrained('usuarios', 'usu_id');
            $table->foreignId('mes_id')->constrained('mesas', 'mes_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
