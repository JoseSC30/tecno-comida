<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_menus', function (Blueprint $table) {
            $table->foreignId('men_id')->constrained('menus', 'men_id');
            $table->foreignId('pro_id')->constrained('productos', 'pro_id');

            $table->primary(['men_id', 'pro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_menus');
    }
};
