<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('res_estado', 50)->default('pendiente')->after('res_hora');
            $table->decimal('res_monto', 10, 2)->nullable()->after('res_estado');
            $table->string('res_transaction_id')->nullable()->after('res_monto');
            $table->integer('res_personas')->default(1)->after('res_transaction_id');
            $table->text('res_notas')->nullable()->after('res_personas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn([
                'res_estado',
                'res_monto',
                'res_transaction_id',
                'res_personas',
                'res_notas',
                'created_at',
                'updated_at',
            ]);
        });
    }
};
