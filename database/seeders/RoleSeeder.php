<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',      'description' => 'Acceso total al sistema'],
            ['name' => 'cajero',     'description' => 'Puede realizar ventas y consultar productos'],
            ['name' => 'bodeguero',  'description' => 'Gestiona entradas y salidas de bodega'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}