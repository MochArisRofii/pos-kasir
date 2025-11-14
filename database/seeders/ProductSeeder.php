<?php

namespace Database\Seeders;

use App\Models\Catergory;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Catergory::all();

        $products = [
            [
                'name' => 'Nasi Goreng Special',
                'description' => 'Nasi goreng dengan telur dan ayam',
                'price' => 25000,
                'stock' => 50,
                'barcode' => '1234567890123',
                'category_id' => $categories->where('name', 'Makanan')->first()->id
            ],
            [
                'name' => 'Es Teh Manis',
                'description' => 'Es teh manis segar',
                'price' => 8000,
                'stock' => 100,
                'barcode' => '1234567890124',
                'category_id' => $categories->where('name', 'Minuman')->first()->id
            ],
            [
                'name' => 'Keripik Kentang',
                'description' => 'Keripik kentang original',
                'price' => 12000,
                'stock' => 30,
                'barcode' => '1234567890125',
                'category_id' => $categories->where('name', 'Snack')->first()->id
            ],
            [
                'name' => 'Charger HP Type-C',
                'description' => 'Charger fast charging Type-C',
                'price' => 75000,
                'stock' => 20,
                'barcode' => '1234567890126',
                'category_id' => $categories->where('name', 'Elektronik')->first()->id
            ],
            [
                'name' => 'Panci Masak',
                'description' => 'Panci stainless steel 24cm',
                'price' => 120000,
                'stock' => 15,
                'barcode' => '1234567890127',
                'category_id' => $categories->where('name', 'Peralatan Rumah Tangga')->first()->id
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
