<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categorias', 'cat_descripcion')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->string('cat_descripcion', 500)->nullable()->after('cat_nombre');
            });
        }

        if (! Schema::hasColumn('categorias', 'created_at')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable()->after('cat_descripcion');
            });
        }

        if (! Schema::hasColumn('categorias', 'updated_at')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }
    }

    public function down(): void
    {
        foreach (['updated_at', 'created_at', 'cat_descripcion'] as $column) {
            if (Schema::hasColumn('categorias', $column)) {
                Schema::table('categorias', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
