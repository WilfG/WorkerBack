<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
class UserSeeder extends Seeder
{
    public function run()
    {
        // create a default admin user
        User::create([
            'name' => 'Admin',
            'email' => 'wilfriedhount@gmail.com',
            'password' => bcrypt('password')
        ]);
        // User::factory(10)->create();
    }
}