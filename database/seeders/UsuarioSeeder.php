<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('rol_id', 'rol_nombre');

        $usuarios = [
            ['nombre' => 'Sofía', 'apellido' => 'López', 'email' => 'admin@comidas.com', 'password' => 'admin123', 'rol' => 'Administrador'],
            ['nombre' => 'Carlos', 'apellido' => 'Rojas', 'email' => 'cajero@comidas.com', 'password' => 'cajero123', 'rol' => 'Cajero'],
            ['nombre' => 'Elena', 'apellido' => 'Flores', 'email' => 'cocinero@comidas.com', 'password' => 'cocinero123', 'rol' => 'Cocinero'],
            ['nombre' => 'Miguel', 'apellido' => 'Quispe', 'email' => 'mesero@comidas.com', 'password' => 'mesero123', 'rol' => 'Mesero'],
            ['nombre' => 'Lucía', 'apellido' => 'Guzmán', 'email' => 'cliente@comidas.com', 'password' => 'cliente123', 'rol' => 'Cliente'],
        ];

        foreach ($usuarios as $usuario) {
            $rolId = $roles->get($usuario['rol']);

            if ($rolId === null) {
                continue;
            }

            DB::table('usuarios')->insert([
                'usu_nombre' => $usuario['nombre'],
                'usu_apellido' => $usuario['apellido'],
                'usu_email' => $usuario['email'],
                'usu_password' => Hash::make($usuario['password']),
                'rol_id' => $rolId,
            ]);
        }
    }
}
