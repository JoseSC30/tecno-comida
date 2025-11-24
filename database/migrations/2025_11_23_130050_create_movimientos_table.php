<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id('mov_id');
            $table->string('mov_tipo', 20);
            $table->decimal('mov_cantidad', 12, 2);
            $table->string('mov_unidad', 120);
            $table->timestamp('mov_fecha')->useCurrent();
            $table->foreignId('ins_id')->constrained('insumos', 'ins_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
