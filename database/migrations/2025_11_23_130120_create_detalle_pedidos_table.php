<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id('dpe_id');
            $table->foreignId('ped_id')->constrained('pedidos', 'ped_id');
            $table->foreignId('pro_id')->constrained('productos', 'pro_id');
            $table->unsignedInteger('dpe_cantidad');
            $table->decimal('dpe_preciounit', 10, 2);
            $table->decimal('dpe_subtotal', 10, 2);
            $table->timestamps();

            $table->unique(['ped_id', 'pro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};
