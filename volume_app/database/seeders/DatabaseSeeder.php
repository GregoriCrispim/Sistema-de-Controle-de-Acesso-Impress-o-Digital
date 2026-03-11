<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@alunobem.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'active' => true,
            ]
        );

        // Operator user
        User::firstOrCreate(
            ['email' => 'operador@alunobem.com'],
            [
                'name' => 'Operador Cantina',
                'password' => Hash::make('operador123'),
                'role' => 'operator',
                'active' => true,
            ]
        );

        // Company user
        User::firstOrCreate(
            ['email' => 'empresa@alunobem.com'],
            [
                'name' => 'Empresa Cantina',
                'password' => Hash::make('empresa123'),
                'role' => 'company',
                'active' => true,
            ]
        );

        // Fiscal user
        User::firstOrCreate(
            ['email' => 'fiscal@alunobem.com'],
            [
                'name' => 'Fiscal Escolar',
                'password' => Hash::make('fiscal123'),
                'role' => 'fiscal',
                'active' => true,
            ]
        );

        // Management user
        User::firstOrCreate(
            ['email' => 'gestao@alunobem.com'],
            [
                'name' => 'Gestão Escolar',
                'password' => Hash::make('gestao123'),
                'role' => 'management',
                'active' => true,
            ]
        );

        // Default system settings
        $settings = [
            'canteen_start_time' => '10:00',
            'canteen_end_time' => '15:00',
            'meal_value' => '15.00',
            'manual_limit_percent' => '30',
        ];

        foreach ($settings as $key => $value) {
            SystemSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->call(TestDataSeeder::class);
    }
}
