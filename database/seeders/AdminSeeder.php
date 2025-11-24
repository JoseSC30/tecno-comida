<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $vendedorRole = Role::where('name', Role::VENDEDOR)->first();
        $clienteRole = Role::where('name', Role::CLIENTE)->first();

        // Usuario Administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@comidas.com',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        // Usuario Vendedor de prueba
        User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@comidas.com',
            'password' => Hash::make('vendedor123'),
            'role_id' => $vendedorRole->id,
            'email_verified_at' => now(),
        ]);

        // Usuario Cliente de prueba
        User::create([
            'name' => 'Cliente',
            'email' => 'cliente@comidas.com',
            'password' => Hash::make('cliente123'),
            'role_id' => $clienteRole->id,
            'email_verified_at' => now(),
        ]);
    }
}
