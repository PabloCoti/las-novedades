<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            [
                'id'      => 1,
                'name'    => 'Central',
                'address' => 'Central',
                'phone'   => '12341234',
            ]
        ];

        foreach ($stores as $store)
        {
            Store::updateOrCreate(['id' => $store['id']], $store);
        }
    }
}
