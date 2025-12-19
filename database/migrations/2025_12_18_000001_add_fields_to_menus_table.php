<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->text('men_descripcion')->nullable()->after('men_nombre');
            $table->string('men_estado', 20)->default('activo')->after('men_ffin');
            $table->decimal('men_descuento', 5, 2)->default(0)->after('men_estado');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['men_descripcion', 'men_estado', 'men_descuento']);
        });
    }
};
