<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pagos', function (Blueprint $table) {
            $table->foreignId('pag_id')->constrained('pagos', 'pag_id');
            $table->foreignId('ped_id')->constrained('pedidos', 'ped_id');
            $table->timestamp('dpa_fecha')->useCurrent();
            $table->decimal('dpa_monto', 10, 2);

            $table->primary(['pag_id', 'ped_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pagos');
    }
};
