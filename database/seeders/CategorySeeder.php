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
                'id'   => 1,
                'name' => 'General',
            ],
            [
                'id'   => 2,
                'name' => 'Pantalón',
            ],
            [
                'id'   => 3,
                'name' => 'Camisa',
            ],
            [
                'id'   => 4,
                'name' => 'Playera',
            ],
            [
                'id'   => 5,
                'name' => 'Suéter',
            ],
        ];

        foreach ($categories as $category)
        {
            Category::updateOrCreate(['id' => $category['id']], $category);
        }
    }
}
