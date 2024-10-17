<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'id'    => 1,
                'name'  => 'C/F',
                'email' => '',
                'phone' => '',
            ]
        ];

        foreach ($customers as $customer)
        {
            Customer::updateOrCreate(['id' => $customer['id']], $customer);
        }
    }
}
