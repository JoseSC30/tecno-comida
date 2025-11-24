<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id('pro_id');
            $table->string('pro_nombre', 255);
            $table->text('pro_descripcion')->nullable();
            $table->decimal('pro_precioventa', 10, 2);
            $table->decimal('pro_costounit', 10, 2)->default(0);
            $table->string('pro_imagen')->nullable();
            $table->boolean('pro_disponible')->default(true);
            $table->foreignId('cat_id')->constrained('categorias', 'cat_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
