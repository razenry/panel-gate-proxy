<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@raznar.hosting';
        $password = 'SuperSecretAdmin123!';

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('------------------------------------------');
        $this->command->info('Superadmin created successfully!');
        $this->command->info('Email: ' . $email);
        $this->command->info('Password: ' . $password);
        $this->command->info('------------------------------------------');
    }
}
