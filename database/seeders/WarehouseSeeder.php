<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            ['name' => 'Bodega Principal', 'location' => 'Planta baja',  'description' => 'Almacén central'],
            ['name' => 'Bodega 2',         'location' => 'Planta alta',  'description' => 'Almacén secundario'],
            ['name' => 'Mostrador',        'location' => 'Tienda',       'description' => 'Productos en exhibición'],
        ];

        foreach ($warehouses as $w) {
            Warehouse::firstOrCreate(['name' => $w['name']], $w);
        }
    }
}