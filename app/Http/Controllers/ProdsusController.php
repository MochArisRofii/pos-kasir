<?php

namespace App\Http\Controllers;

use App\Models\Catergory;
use App\Models\Product;
use App\Models\Prodsus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Milon\Barcode\DNS1D;

class ProdsusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('home')
                ->with('error', 'Akses ditolak.');
        }

        $prodsus = Prodsus::with(['creator', 'processor'])->latest()->get();
        return view('prodsus.index', compact('prodsus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('home')
                ->with('error', 'Akses ditolak.');
        }

        return view('prodsus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('home')
                ->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // Buat prodsus
        $prodsus = Prodsus::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'created_by' => Auth::id(),
            'status' => 'pending'
        ]);

        // Generate barcode
        $barcode = $prodsus->generateBarcode();
        
        // Generate barcode PNG dan dapatkan path
        $barcodePath = $this->generateBarcodeImage($barcode, $prodsus->id);
        
        // Update prodsus dengan barcode dan path
        $prodsus->update([
            'barcode' => $barcode,
            'barcode_path' => $barcodePath
        ]);

        Log::info('Prodsus created successfully', [
            'prodsus_id' => $prodsus->id,
            'name' => $prodsus->name,
            'barcode' => $barcode
        ]);

        return redirect()->route('prodsus.index')
            ->with('success', 'Prodsus berhasil dibuat. Barcode: ' . $barcode)
            ->with('barcode_download', $barcodePath);
    }

    /**
     * Halaman proses prodsus untuk kasir
     */
    public function processIndex()
    {
        if (Auth::user()->role !== 'cashier') {
            return redirect()->route('home')
                ->with('error', 'Akses ditolak.');
        }

        $prodsus = Prodsus::pending()->latest()->get();
        return view('prodsus.process', compact('prodsus'));
    }

    /**
     * Proses approval prodsus oleh kasir
     */
    public function process(Request $request, $id)
    {
        if (Auth::user()->role !== 'cashier') {
            Log::warning('Unauthorized access attempt to process prodsus', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role,
                'required_role' => 'cashier'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya kasir yang dapat memproses prodsus.'
            ], 403);
        }

        try {
            // Cari prodsus berdasarkan ID
            $prodsus = Prodsus::find($id);
            
            if (!$prodsus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data prodsus tidak ditemukan.'
                ], 404);
            }

            // Validasi
            $validator = Validator::make($request->all(), [
                'barcode_file' => 'required|file|mimes:png|max:2048',
                'action' => 'required|in:approve,reject',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first()
                ], 422);
            }

            // Simpan file barcode
            $file = $request->file('barcode_file');
            $filename = 'barcode-' . $prodsus->barcode . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('prodsus/barcodes', $filename, 'public');

            // Update prodsus
            $prodsus->update([
                'barcode_path' => $path,
                'status' => $request->action == 'approve' ? 'approved' : 'rejected',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'notes' => $request->notes
            ]);

            Log::info('Prodsus status updated', [
                'prodsus_id' => $prodsus->id,
                'status' => $prodsus->status,
                'action' => $request->action
            ]);

            // Jika approved, tambahkan ke products
            if ($request->action == 'approve') {
                Log::info('Memanggil addToProducts untuk prodsus ID: ' . $prodsus->id);
                $this->addToProducts($prodsus);
                Log::info('Selesai memanggil addToProducts untuk prodsus ID: ' . $prodsus->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prodsus berhasil di' . ($request->action == 'approve' ? 'setujui' : 'tolak')
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing prodsus: ' . $e->getMessage(), [
                'prodsus_id' => $id,
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate barcode image as PNG (FIXED)
     */
    private function generateBarcodeImage($barcode, $prodsusId)
    {
        $dns1d = new DNS1D();
        
        // Generate PNG langsung
        $barcodePng = $dns1d->getBarcodePNG($barcode, 'C128', 2, 60, [0, 0, 0], true);
        
        $filename = 'barcode-' . $barcode . '.png';
        $path = 'prodsus/barcodes/' . $filename;

        // Decode base64 PNG dan simpan
        $pngData = base64_decode($barcodePng);
        Storage::disk('public')->put($path, $pngData);

        Log::info('Barcode PNG generated', [
            'barcode' => $barcode,
            'path' => $path
        ]);

        return $path;
    }

    /**
     * Tambahkan ke products table - FIXED VERSION
     */
    private function addToProducts(Prodsus $prodsus)
    {
        try {
            Log::info('Memulai addToProducts untuk prodsus: ' . $prodsus->id);

            // PERBAIKI: Gunakan Category bukan Catergory
            $category = Catergory::firstOrCreate(
                ['name' => 'Produk Khusus'],
                [
                    'description' => 'Produk khusus roti, dimsum, siomay, sosis',
                    'is_active' => true
                ]
            );

            Log::info('Category ID: ' . $category->id);

            // Cek apakah product dengan barcode yang sama sudah ada
            $existingProduct = Product::where('barcode', $prodsus->barcode)->first();
            
            if ($existingProduct) {
                Log::info('Product sudah ada, melakukan update: ' . $existingProduct->id);
                
                // Update product yang sudah ada
                $existingProduct->update([
                    'name' => $prodsus->name,
                    'description' => $prodsus->description,
                    'price' => $prodsus->price,
                    'stock' => $prodsus->stock,
                    'category_id' => $category->id,
                    'is_active' => true,
                    'updated_at' => now()
                ]);

                Log::info('Product updated successfully', [
                    'product_id' => $existingProduct->id,
                    'prodsus_id' => $prodsus->id
                ]);
            } else {
                Log::info('Membuat product baru untuk prodsus: ' . $prodsus->id);

                // Generate PLU yang unik
                $latestProduct = Product::orderBy('id', 'desc')->first();
                $newId = $latestProduct ? $latestProduct->id + 1 : 1;
                $plu = 'PK-' . str_pad($newId, 4, '0', STR_PAD_LEFT);

                // Buat product baru
                $product = Product::create([
                    'name' => $prodsus->name,
                    'description' => $prodsus->description,
                    'price' => $prodsus->price,
                    'stock' => $prodsus->stock,
                    'barcode' => $prodsus->barcode,
                    'plu' => $plu,
                    'category_id' => $category->id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Log::info('Product baru dibuat dengan ID: ' . $product->id, [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode
                ]);
            }

            Log::info('SUKSES: Prodsus berhasil ditambahkan ke products', [
                'prodsus_id' => $prodsus->id,
                'prodsus_name' => $prodsus->name
            ]);

        } catch (\Exception $e) {
            Log::error('ERROR dalam addToProducts: ' . $e->getMessage(), [
                'prodsus_id' => $prodsus->id,
                'error_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Download barcode
     */
    public function downloadBarcode(Prodsus $prodsus)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('home')
                ->with('error', 'Akses ditolak.');
        }

        if (!$prodsus->barcode_path || !Storage::disk('public')->exists($prodsus->barcode_path)) {
            return redirect()->back()
                ->with('error', 'File barcode tidak ditemukan.');
        }

        return Storage::disk('public')->download($prodsus->barcode_path);
    }

    /**
     * Debug method untuk memproses ulang prodsus yang approved
     */
    public function reprocessApproved()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        $approvedProdsus = Prodsus::where('status', 'approved')->get();
        $results = [];
        
        foreach ($approvedProdsus as $prodsus) {
            try {
                $this->addToProducts($prodsus);
                $results[] = "SUCCESS: " . $prodsus->name;
            } catch (\Exception $e) {
                $results[] = "ERROR: " . $prodsus->name . " - " . $e->getMessage();
            }
        }
        
        return response()->json($results);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}