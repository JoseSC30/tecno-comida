<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('productos', 'pro_descripcion')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->text('pro_descripcion')->nullable()->after('pro_nombre');
            });
        }

        if (! Schema::hasColumn('productos', 'pro_imagen')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('pro_imagen')->nullable()->after('pro_costounit');
            });
        }

        if (! Schema::hasColumn('productos', 'pro_disponible')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->boolean('pro_disponible')->default(true)->after('pro_imagen');
            });
        }

        if (! Schema::hasColumn('productos', 'created_at')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable()->after('pro_disponible');
            });
        }

        if (! Schema::hasColumn('productos', 'updated_at')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }
    }

    public function down(): void
    {
        foreach (['updated_at', 'created_at', 'pro_disponible', 'pro_imagen', 'pro_descripcion'] as $column) {
            if (Schema::hasColumn('productos', $column)) {
                Schema::table('productos', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
