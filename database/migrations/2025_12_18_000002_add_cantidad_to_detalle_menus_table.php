<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_menus', function (Blueprint $table) {
            $table->unsignedInteger('det_cantidad')->default(1)->after('pro_id');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_menus', function (Blueprint $table) {
            $table->dropColumn('det_cantidad');
        });
    }
};