<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('pag_id');
            $table->string('pag_metodo', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
