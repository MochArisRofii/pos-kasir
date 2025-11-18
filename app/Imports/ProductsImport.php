<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Catergory;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithCustomCsvSettings
{
    private $headers = [];
    private $importedCount = 0;

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_character' => '\\',
            'input_encoding' => 'UTF-8',
        ];
    }

    public function model(array $row)
    {
        // Debug: Simpan header pertama kali
        if (empty($this->headers)) {
            $this->headers = array_keys($row);
            Log::info('=== HEADERS FOUND ===');
            Log::info('Raw headers: ', $this->headers);
            Log::info('Raw row: ', $row);
        }

        // Jika header masih gabung, coba parse manual
        if (count($this->headers) === 1 && str_contains($this->headers[0], 'nama_produk')) {
            Log::warning('Header appears to be combined, attempting manual parsing');
            return $this->parseCombinedRow($row);
        }

        // Normalize header names - case insensitive
        $normalizedRow = [];
        foreach ($row as $key => $value) {
            $normalizedKey = strtolower(trim($key));
            $normalizedRow[$normalizedKey] = $value;
        }

        Log::info('Normalized row: ', $normalizedRow);

        return $this->createProduct($normalizedRow);
    }

    private function parseCombinedRow(array $row)
    {
        $combinedKey = array_keys($row)[0];
        $combinedValue = $row[$combinedKey];
        
        Log::info('Parsing combined row: ' . $combinedValue);

        // Split the combined value by comma
        $values = str_getcsv($combinedValue, ',', '"', '\\');
        
        // Expected fields order (tambah PLU)
        $expectedFields = ['nama_produk', 'harga', 'stok', 'kategori', 'barcode', 'plu', 'deskripsi'];
        
        if (count($values) >= 4) { // Minimal required fields
            $parsedRow = [];
            foreach ($expectedFields as $index => $field) {
                $parsedRow[$field] = $values[$index] ?? null;
            }
            
            Log::info('Parsed row: ', $parsedRow);
            return $this->createProduct($parsedRow);
        }
        
        Log::warning('Failed to parse combined row');
        return null;
    }

    private function createProduct(array $normalizedRow)
    {
        // Check if required fields exist in normalized data
        if (!isset($normalizedRow['nama_produk']) || empty(trim($normalizedRow['nama_produk'] ?? '')) ||
            !isset($normalizedRow['harga']) || empty(trim($normalizedRow['harga'] ?? '')) ||
            !isset($normalizedRow['stok']) || empty(trim($normalizedRow['stok'] ?? '')) ||
            !isset($normalizedRow['kategori']) || empty(trim($normalizedRow['kategori'] ?? ''))) {
            
            Log::warning('Skipping row - missing required fields');
            Log::warning('Available fields: ' . implode(', ', array_keys($normalizedRow)));
            return null;
        }

        // Clean the data
        $categoryName = trim($normalizedRow['kategori']);
        $productName = trim($normalizedRow['nama_produk']);
        
        Log::info('Processing: ' . $productName . ' - ' . $categoryName);

        // Cari category berdasarkan nama, jika tidak ada buat baru
        $category = Catergory::where('name', $categoryName)->first();
        
        if (!$category) {
            // Buat category baru
            $category = Catergory::create([
                'name' => $categoryName,
                'description' => 'Imported from CSV'
            ]);
            Log::info('New category created: ' . $category->name . ' (ID: ' . $category->id . ')');
        }

        // Handle barcode - generate jika kosong
        $barcode = isset($normalizedRow['barcode']) ? trim($normalizedRow['barcode']) : null;
        if (empty($barcode)) {
            $barcode = 'BRC-' . time() . '-' . rand(1000, 9999);
        }

        // Handle PLU - generate jika kosong
        $plu = isset($normalizedRow['plu']) ? trim($normalizedRow['plu']) : null;
        if (empty($plu)) {
            $plu = Product::generatePLU();
        } else {
            // Validasi PLU harus 6-8 digit angka
            $plu = preg_replace('/[^0-9]/', '', $plu);
            if (strlen($plu) < 6 || strlen($plu) > 8) {
                $plu = Product::generatePLU();
            }
        }

        // Cek duplikat product
        $existingProduct = Product::where('name', $productName)
            ->orWhere('barcode', $barcode)
            ->orWhere('plu', $plu)
            ->first();

        if ($existingProduct) {
            Log::warning('Product already exists, skipping: ' . $productName);
            return null;
        }

        // Buat product
        $product = new Product([
            'name' => $productName,
            'description' => isset($normalizedRow['deskripsi']) ? trim($normalizedRow['deskripsi']) : null,
            'price' => (int) $normalizedRow['harga'],
            'stock' => (int) $normalizedRow['stok'],
            'barcode' => $barcode,
            'plu' => $plu, // Tambahkan PLU
            'category_id' => $category->id,
        ]);

        // Simpan product dan cek hasilnya
        $saved = $product->save();
        
        if ($saved) {
            $this->importedCount++;
            Log::info('✅ Product SUCCESSFULLY saved: ' . $product->name . ' (ID: ' . $product->id . ', PLU: ' . $product->plu . ')');
        } else {
            Log::error('❌ FAILED to save product: ' . $product->name);
        }

        return $product;
    }

    public function rules(): array
    {
        return [
            // Tetap nonaktif sementara
        ];
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    

    public function customValidationMessages()
    {
        return [
            // '*.nama_produk.required' => 'Nama produk wajib diisi',
            // '*.harga.required' => 'Harga wajib diisi',
            // '*.harga.numeric' => 'Harga harus berupa angka',
            // '*.stok.required' => 'Stok wajib diisi',
            // '*.stok.integer' => 'Stok harus berupa angka bulat',
            // '*.kategori.required' => 'Kategori wajib diisi',
        ];
    }
}