<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id('mes_id');
            $table->integer('mes_numero')->unique();
            $table->integer('mes_capacidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
