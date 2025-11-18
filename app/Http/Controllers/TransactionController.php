<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::with(['user', 'items.product', 'payments'])->latest()->get();
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        // Cek jika user adalah kasir dan sudah inisialisasi
        if (Auth::user()->role === 'cashier' && !Session::get('cashier_initialized')) {
            return redirect()->route('cashier.initialize')
                ->with('error', 'Silakan inisialisasi kasir terlebih dahulu.');
        }

        // Load semua produk, batasi tampilan di frontend
        $products = Product::where('stock', '>', 0)->get();

        return view('transactions.create', compact('products'));
    }

    public function store(Request $request)
    {
        // Cek jika user adalah kasir dan sudah inisialisasi
        if (Auth::user()->role === 'cashier' && !Session::get('cashier_initialized')) {
            return response()->json([
                'success' => false,
                'message' => 'Kasir belum diinisialisasi.'
            ], 403);
        }

        // ... sisa kode store method yang sudah ada
        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'total_amount' => 0,
                'tax' => 0,
                'discount' => 0,
                'final_amount' => 0,
                'status' => 'pending'
            ]);

            $totalAmount = 0;
            $items = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ]);

                // Update stock
                $product->decrement('stock', $item['quantity']);
            }

            // Calculate final amount
            $tax = $totalAmount * 0.1;
            $finalAmount = $totalAmount + $tax;

            $transaction->update([
                'total_amount' => $totalAmount,
                'tax' => $tax,
                'final_amount' => $finalAmount
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'redirect_url' => route('transactions.show', $transaction)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    
    }

    // PERBAIKAN: Update method show
    public function show(Transaction $transaction)
    {
        // Gunakan with() untuk eager loading
        $transaction->load(['items.product', 'user', 'payments']);
        return view('transactions.show', compact('transaction'));
    }

    public function destroy(Transaction $transaction)
    {
        DB::beginTransaction();

        try {
            // Restore stock
            foreach ($transaction->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            $transaction->delete();

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('transactions.index')
                ->with('error', 'Failed to delete transaction.');
        }
    }
}
