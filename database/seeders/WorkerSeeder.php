<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkerSeeder extends Seeder
{
    public function run()
    {
        $workers = [
            [
                'name' => 'Jean Dupont',
                'email' => 'jean@example.com',
                'password' => Hash::make('password'),
                'user_type' => 'ARTISAN',
                'description' => 'Menuisier avec 15 ans d\'expérience',
                'hourly_rate' => 45.00,
                'years_experience' => 15,
                'phone_number' => '0123456789',
                'categories' => ['Menuiserie']
            ],
            [
                'name' => 'Marie Martin',
                'email' => 'marie@example.com',
                'password' => Hash::make('password'),
                'user_type' => 'ARTISAN',
                'description' => 'Électricienne qualifiée',
                'hourly_rate' => 50.00,
                'years_experience' => 8,
                'phone_number' => '0123456790',
                'categories' => ['Électricité']
            ]
        ];

        foreach ($workers as $workerData) {
            $categories = $workerData['categories'];
            unset($workerData['categories']);
            
            $worker = User::create($workerData);
            
            foreach ($categories as $categoryName) {
                $category = Category::where('name', $categoryName)->first();
                if ($category) {
                    $worker->categories()->attach($category->id);
                }
            }
        }
    }
}