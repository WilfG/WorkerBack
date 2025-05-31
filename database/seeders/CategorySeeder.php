<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Menuiserie', 'icon' => 'carpenter'],
            ['name' => 'Plomberie', 'icon' => 'water'],
            ['name' => 'Électricité', 'icon' => 'electrical-services'],
            ['name' => 'Maçonnerie', 'icon' => 'construction'],
            ['name' => 'Peinture', 'icon' => 'format-paint'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}