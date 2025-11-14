<?php

namespace Database\Seeders;

use App\Models\Catergory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan', 'description' => 'Produk makanan'],
            ['name' => 'Minuman', 'description' => 'Produk minuman'],
            ['name' => 'Snack', 'description' => 'Produk snack'],
            ['name' => 'Elektronik', 'description' => 'Produk elektronik'],
            ['name' => 'Peralatan Rumah Tangga', 'description' => 'Peralatan rumah tangga'],
        ];

        foreach ($categories as $category) {
            Catergory::create($category);
        }
    }
}
