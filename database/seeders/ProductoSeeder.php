<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = DB::table('categorias')
            ->pluck('cat_id', 'cat_nombre');

        $productos = [
            ['nombre' => 'Empanadas de Carne', 'precio' => 15.00, 'costo' => 9.00, 'categoria' => 'Entradas'],
            ['nombre' => 'Sopa de Maní', 'precio' => 12.00, 'costo' => 7.50, 'categoria' => 'Entradas'],
            ['nombre' => 'Anticuchos', 'precio' => 18.00, 'costo' => 11.00, 'categoria' => 'Entradas'],
            ['nombre' => 'Pique Macho', 'precio' => 35.00, 'costo' => 22.00, 'categoria' => 'Platos Fuertes'],
            ['nombre' => 'Silpancho', 'precio' => 30.00, 'costo' => 18.00, 'categoria' => 'Platos Fuertes'],
            ['nombre' => 'Sajta de Pollo', 'precio' => 28.00, 'costo' => 16.00, 'categoria' => 'Platos Fuertes'],
            ['nombre' => 'Fricasé', 'precio' => 32.00, 'costo' => 19.00, 'categoria' => 'Platos Fuertes'],
            ['nombre' => 'Helado de Canela', 'precio' => 10.00, 'costo' => 5.50, 'categoria' => 'Postres'],
            ['nombre' => 'Buñuelos', 'precio' => 8.00, 'costo' => 4.00, 'categoria' => 'Postres'],
            ['nombre' => 'Mocochinchi', 'precio' => 5.00, 'costo' => 2.00, 'categoria' => 'Bebidas'],
            ['nombre' => 'Api Morado', 'precio' => 6.00, 'costo' => 2.50, 'categoria' => 'Bebidas'],
            ['nombre' => 'Jugo Natural', 'precio' => 7.00, 'costo' => 3.00, 'categoria' => 'Bebidas'],
            ['nombre' => 'Ensalada Mixta', 'precio' => 12.00, 'costo' => 6.00, 'categoria' => 'Ensaladas'],
        ];

        foreach ($productos as $producto) {
            $catId = $categorias->get($producto['categoria']);

            if ($catId === null) {
                continue; // evita fallos si falta la categoría
            }

            DB::table('productos')->insert([
                'pro_nombre' => $producto['nombre'],
                'pro_precioventa' => $producto['precio'],
                'pro_costounit' => $producto['costo'],
                'cat_id' => $catId,
            ]);
        }
    }
}
