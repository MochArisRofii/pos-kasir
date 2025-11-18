<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin POS',
            'email' => 'admin@pos.com',
            'nik' => '2015635786',
            'password' => Hash::make('123'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@pos.com',
            'nik' => '2015635787',
            'password' => Hash::make('123'),
            'role' => 'cashier'
        ]);
    }
}
