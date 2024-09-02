<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(['id' => 1], [
            'store_id' => 1,
            'name'     => 'admin',
            'email'    => 'admin@admin.com',
            'password' => bcrypt('admin'),
        ]);
    }
}
