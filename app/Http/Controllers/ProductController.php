<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Imports\ProductsImport;
use App\Models\Catergory;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Catergory::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:products',
            'category_id' => 'required|exists:categories,id'
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Catergory::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'category_id' => 'required|exists:categories,id'
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $templatePath = storage_path('app/templates/products_template.csv');

        // Ensure directory exists
        $templateDir = dirname($templatePath);
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Create CSV template dengan delimiter koma yang benar
        $csvContent = "nama_produk,harga,stok,kategori,barcode,deskripsi\n" .
            "Indomie Goreng,3500,100,Makanan,1234567890123,\"Mi instan rasa goreng\"\n" .
            "Coca Cola,8000,50,Minuman,1234567890124,\"Minuman bersoda\"\n" .
            "Chitato,12000,30,Snack,1234567890125,\"Keripik kentang\"";

        file_put_contents($templatePath, $csvContent);

        return response()->download($templatePath, 'products_template.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    public function import(Request $request)
    {
        Log::info('=== IMPORT PROCESS STARTED ===');

        $request->validate([
            'excel_file' => 'required|file|max:2048'
        ]);

        $file = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['xlsx', 'xls', 'csv'];

        if (!in_array($extension, $allowedExtensions)) {
            Log::error('Invalid file extension: ' . $extension);
            return redirect()->back()
                ->with('error', 'File harus berformat: .xlsx, .xls, atau .csv. File Anda: .' . $extension);
        }

        Log::info('File details:');
        Log::info('- Name: ' . $file->getClientOriginalName());
        Log::info('- Extension: ' . $extension);
        Log::info('- MIME: ' . $file->getMimeType());
        Log::info('- Size: ' . $file->getSize());

        try {
            $import = new ProductsImport;

            Log::info('Starting Excel import...');
            Excel::import($import, $file);

            Log::info('=== IMPORT COMPLETED SUCCESSFULLY ===');

            return redirect()->route('products.index')
                ->with('success', 'Products imported successfully!');

        } catch (\Exception $e) {
            Log::error('=== IMPORT FAILED ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            return redirect()->route('products.index')
                ->with('error', 'Error importing products: ' . $e->getMessage());
        }
    }
}
