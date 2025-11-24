<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id('ped_id');
            $table->string('ped_estado', 50);
            $table->decimal('ped_total', 10, 2)->default(0);
            $table->text('ped_notas')->nullable();
            $table->timestamp('ped_fecha')->useCurrent();
            $table->foreignId('usu_id')->constrained('usuarios', 'usu_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
