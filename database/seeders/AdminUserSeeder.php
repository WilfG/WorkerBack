<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        $name = env('ADMIN_NAME', 'Admin');

        if (empty($email) || empty($password)) {
            $this->command->warn('ADMIN_EMAIL ou ADMIN_PASSWORD non défini. Seeder ignoré.');
            return;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $this->command->info("Admin déjà existant : {$email}");
            return;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'user_type' => 'ADMIN',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info("Admin créé : {$email}");
    }
}