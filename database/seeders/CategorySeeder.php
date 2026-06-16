<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Herramientas',
            'Materiales de construcción',
            'Plomería',
            'Electricidad',
            'Pintura',
            'Tornillería',
            'Jardinería',
            'Seguridad',
            'Limpieza',
            'Varios',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}