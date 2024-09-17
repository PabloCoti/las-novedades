<?php

namespace Database\Seeders;

use App\Models\Color;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            [
                'id'   => 1,
                'name' => 'Rojo',
                'hex'  => '#FF0000'
            ],
            [
                'id'   => 2,
                'name' => 'Verde',
                'hex'  => '#00FF00'
            ],
            [
                'id'   => 3,
                'name' => 'Azul',
                'hex'  => '#0000FF'
            ],
            [
                'id'   => 4,
                'name' => 'Amarillo',
                'hex'  => '#FFFF00'
            ],
            [
                'id'   => 5,
                'name' => 'Morado',
                'hex'  => '#800080'
            ],
            [
                'id'   => 6,
                'name' => 'Anaranjado',
                'hex'  => '#FFA500'
            ],
            [
                'id'   => 7,
                'name' => 'Rosado',
                'hex'  => '#FFC0CB'
            ],
            [
                'id'   => 8,
                'name' => 'Negro',
                'hex'  => '#000000'
            ],
            [
                'id'   => 9,
                'name' => 'Blanco',
                'hex'  => '#FFFFFF'
            ],
            [
                'id'   => 10,
                'name' => 'Gris',
                'hex'  => '#808080'
            ],
        ];

        foreach ($colors as $color)
        {
            Color::updateOrCreate(['id' => $color['id']], $color);
        }
    }
}
