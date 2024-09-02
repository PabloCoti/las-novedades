<?php

namespace Database\Seeders;

use App\Models\Size;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = [
            [
                'id' => 1,
                'name' => 'XS',
                'description' => 'Extra small',
            ],
            [
                'id' => 2,
                'name' => 'S',
                'description' => 'Small',
            ],
            [
                'id' => 3,
                'name' => 'M',
                'description' => 'Medium',
            ],
            [
                'id' => 4,
                'name' => 'L',
                'description' => 'Large',
            ],
            [
                'id' => 5,
                'name' => 'XL',
                'description' => 'Extra large',
            ],
        ];

        foreach ($sizes as $size)
        {
            Size::updateOrCreate(['id' => $size['id']], $size);
        }
    }
}
