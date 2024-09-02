<?php

namespace Database\Seeders;

use App\Models\Category;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id'          => 1,
                'name'        => 'General',
                'description' => 'Categoría general',
            ],
        ];

        foreach ($categories as $category)
        {
            Category::updateOrCreate(['id' => $category['id']], $category);
        }
    }
}
