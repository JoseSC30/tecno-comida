<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id('men_id');
            $table->string('men_nombre', 255);
            $table->string('men_tipo', 255)->nullable();
            $table->date('men_fini')->nullable();
            $table->date('men_ffin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
