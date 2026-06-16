<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            ['name' => 'Administrador', 'password' => Hash::make('admin1234')]
        );
        $admin->roles()->syncWithoutDetaching(
            Role::where('name', 'admin')->first()->id
        );

        $cajero = User::firstOrCreate(
            ['email' => 'cajero@pos.com'],
            ['name' => 'Cajero Demo', 'password' => Hash::make('cajero1234')]
        );
        $cajero->roles()->syncWithoutDetaching(
            Role::where('name', 'cajero')->first()->id
        );

        $bodeguero = User::firstOrCreate(
            ['email' => 'bodeguero@pos.com'],
            ['name' => 'Bodeguero Demo', 'password' => Hash::make('bodeguero1234')]
        );
        $bodeguero->roles()->syncWithoutDetaching(
            Role::where('name', 'bodeguero')->first()->id
        );
    }
}