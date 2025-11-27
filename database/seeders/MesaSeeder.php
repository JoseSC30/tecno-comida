<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        $mesas = [
            // Mesas pequeñas para parejas (2 personas)
            ['mes_numero' => 1, 'mes_capacidad' => 2],
            ['mes_numero' => 2, 'mes_capacidad' => 2],
            ['mes_numero' => 3, 'mes_capacidad' => 2],
            ['mes_numero' => 4, 'mes_capacidad' => 2],
            ['mes_numero' => 5, 'mes_capacidad' => 2],
            
            // Mesas medianas para familias pequeñas (4 personas)
            ['mes_numero' => 6, 'mes_capacidad' => 4],
            ['mes_numero' => 7, 'mes_capacidad' => 4],
            ['mes_numero' => 8, 'mes_capacidad' => 4],
            ['mes_numero' => 9, 'mes_capacidad' => 4],
            ['mes_numero' => 10, 'mes_capacidad' => 4],
            ['mes_numero' => 11, 'mes_capacidad' => 4],
            
            // Mesas grandes para grupos (6 personas)
            ['mes_numero' => 12, 'mes_capacidad' => 6],
            ['mes_numero' => 13, 'mes_capacidad' => 6],
            ['mes_numero' => 14, 'mes_capacidad' => 6],
            ['mes_numero' => 15, 'mes_capacidad' => 6],
            
            // Mesas extra grandes para eventos (8 personas)
            ['mes_numero' => 16, 'mes_capacidad' => 8],
            ['mes_numero' => 17, 'mes_capacidad' => 8],
            ['mes_numero' => 18, 'mes_capacidad' => 8],
            
            // Mesas VIP para grupos grandes (10-12 personas)
            ['mes_numero' => 19, 'mes_capacidad' => 10],
            ['mes_numero' => 20, 'mes_capacidad' => 12],
        ];

        DB::table('mesas')->insert($mesas);
    }
}
