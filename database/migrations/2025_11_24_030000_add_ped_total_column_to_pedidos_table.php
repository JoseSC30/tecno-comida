<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pedidos', 'ped_total')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->decimal('ped_total', 10, 2)->default(0);
            });
        }

        if (Schema::hasColumn('pedidos', 'total') && Schema::hasColumn('pedidos', 'ped_total')) {
            DB::table('pedidos')->update([
                'ped_total' => DB::raw('COALESCE(total, ped_total, 0)')
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pedidos', 'total') && Schema::hasColumn('pedidos', 'ped_total')) {
            DB::table('pedidos')->update([
                'total' => DB::raw('COALESCE(total, ped_total, 0)')
            ]);
        }

        if (Schema::hasColumn('pedidos', 'ped_total')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropColumn('ped_total');
            });
        }
    }
};
