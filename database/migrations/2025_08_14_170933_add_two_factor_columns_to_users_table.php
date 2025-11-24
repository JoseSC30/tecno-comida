<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se deja sin efecto porque no existe la tabla `users`.
    }

    public function down(): void
    {
        // No hay columnas que revertir.
    }
};
