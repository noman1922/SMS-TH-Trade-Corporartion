<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@th-trade.com'],
            [
                'name' => 'Main Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Staff User
        User::updateOrCreate(
            ['email' => 'staff@th-trade.com'],
            [
                'name' => 'General Staff',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]
        );
    }
}
