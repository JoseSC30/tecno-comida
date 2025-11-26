<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categorias')->insert([
            ['cat_nombre' => 'Entradas'],
            ['cat_nombre' => 'Platos Fuertes'],
            ['cat_nombre' => 'Postres'],
            ['cat_nombre' => 'Bebidas'],
            ['cat_nombre' => 'Ensaladas'],
        ]);
    }
}
