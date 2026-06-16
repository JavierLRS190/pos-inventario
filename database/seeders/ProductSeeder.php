<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category' => 'Herramientas',              'name' => 'Martillo 16oz',          'sku' => 'HER-001', 'price' => 120.00, 'cost' => 75.00,  'stock' => 25],
            ['category' => 'Herramientas',              'name' => 'Desarmador plano 6"',     'sku' => 'HER-002', 'price' => 35.00,  'cost' => 18.00,  'stock' => 40],
            ['category' => 'Herramientas',              'name' => 'Pinzas de presión',       'sku' => 'HER-003', 'price' => 89.00,  'cost' => 50.00,  'stock' => 15],
            ['category' => 'Materiales de construcción','name' => 'Cemento 50kg',            'sku' => 'CON-001', 'price' => 185.00, 'cost' => 140.00, 'stock' => 60],
            ['category' => 'Materiales de construcción','name' => 'Varilla 3/8" x 6m',      'sku' => 'CON-002', 'price' => 95.00,  'cost' => 68.00,  'stock' => 80],
            ['category' => 'Plomería',                  'name' => 'Tubo PVC 1/2" x 3m',     'sku' => 'PLO-001', 'price' => 42.00,  'cost' => 25.00,  'stock' => 50],
            ['category' => 'Plomería',                  'name' => 'Llave de paso 1/2"',      'sku' => 'PLO-002', 'price' => 65.00,  'cost' => 38.00,  'stock' => 30],
            ['category' => 'Electricidad',              'name' => 'Foco LED 10W',            'sku' => 'ELE-001', 'price' => 48.00,  'cost' => 28.00,  'stock' => 100],
            ['category' => 'Electricidad',              'name' => 'Cable calibre 12 (metro)','sku' => 'ELE-002', 'price' => 22.00,  'cost' => 13.00,  'stock' => 200],
            ['category' => 'Pintura',                   'name' => 'Pintura blanca 4L',       'sku' => 'PIN-001', 'price' => 210.00, 'cost' => 155.00, 'stock' => 20],
            ['category' => 'Pintura',                   'name' => 'Brocha 3"',               'sku' => 'PIN-002', 'price' => 38.00,  'cost' => 20.00,  'stock' => 35],
            ['category' => 'Tornillería',               'name' => 'Tornillo 1/4" x 1" (100)', 'sku' => 'TOR-001','price' => 28.00, 'cost' => 14.00,  'stock' => 150],
            ['category' => 'Tornillería',               'name' => 'Taquete Fisher #8 (50)',  'sku' => 'TOR-002', 'price' => 32.00,  'cost' => 18.00,  'stock' => 120],
            ['category' => 'Limpieza',                  'name' => 'Escoba de plástico',      'sku' => 'LIM-001', 'price' => 55.00,  'cost' => 32.00,  'stock' => 3],
            ['category' => 'Varios',                    'name' => 'Cinta canela 2"',         'sku' => 'VAR-001', 'price' => 18.00,  'cost' => 9.00,   'stock' => 60],
        ];

        foreach ($products as $p) {
            $category = Category::where('name', $p['category'])->first();
            Product::firstOrCreate(
                ['sku' => $p['sku']],
                [
                    'category_id' => $category->id,
                    'name'        => $p['name'],
                    'price'       => $p['price'],
                    'cost'        => $p['cost'],
                    'stock'       => $p['stock'],
                    'stock_min'   => 5,
                    'unit'        => 'pieza',
                    'active'      => true,
                ]
            );
        }
    }
}