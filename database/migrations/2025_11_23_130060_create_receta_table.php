<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receta', function (Blueprint $table) {
            $table->foreignId('pro_id')->constrained('productos', 'pro_id');
            $table->foreignId('ins_id')->constrained('insumos', 'ins_id');
            $table->decimal('rec_cantidad', 12, 2);
            $table->string('rec_unidad', 120);

            $table->primary(['pro_id', 'ins_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receta');
    }
};
