<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumos', function (Blueprint $table) {
            $table->id('ins_id');
            $table->string('ins_nombre', 120);
            $table->string('ins_unidad', 50);
            $table->decimal('ins_stock', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
