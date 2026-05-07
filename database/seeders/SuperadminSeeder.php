<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@raznar.id'],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'first_name' => 'Super',
                'last_name' => 'Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('RaznarAdmin2024!'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
