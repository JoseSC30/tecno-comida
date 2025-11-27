<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Tipo de pago: 'completo' o 'cuotas'
            $table->string('res_tipo_pago', 20)->default('completo')->after('res_estado');
            
            // Número total de cuotas (1 = pago completo, 2 = dos cuotas)
            $table->integer('res_num_cuotas')->default(1)->after('res_tipo_pago');
            
            // Monto de cada cuota
            $table->decimal('res_monto_cuota', 10, 2)->nullable()->after('res_monto');
            
            // Monto pagado hasta ahora
            $table->decimal('res_monto_pagado', 10, 2)->default(0)->after('res_monto_cuota');
            
            // Número de cuotas pagadas
            $table->integer('res_cuotas_pagadas')->default(0)->after('res_monto_pagado');
            
            // Transaction ID del segundo pago (si aplica)
            $table->string('res_transaction_id_2')->nullable()->after('res_transaction_id');
            
            // Fecha del segundo pago (si aplica)
            $table->timestamp('res_fecha_pago_2')->nullable()->after('res_transaction_id_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn([
                'res_tipo_pago',
                'res_num_cuotas',
                'res_monto_cuota',
                'res_monto_pagado',
                'res_cuotas_pagadas',
                'res_transaction_id_2',
                'res_fecha_pago_2',
            ]);
        });
    }
};
