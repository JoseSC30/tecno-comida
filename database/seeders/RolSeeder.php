<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['rol_nombre' => 'Administrador'],
            ['rol_nombre' => 'Cajero'],
            ['rol_nombre' => 'Cocinero'],
            ['rol_nombre' => 'Mesero'],
            ['rol_nombre' => 'Cliente'],
        ]);
    }
}
