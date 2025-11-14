<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $today = Carbon::today();
        
        $stats = [
            'today_sales' => Transaction::whereDate('created_at', $today)
                            ->where('status', 'paid')
                            ->sum('final_amount'),
            'today_transactions' => Transaction::whereDate('created_at', $today)->count(),
            'total_products' => Product::count(),
            'low_stock_products' => Product::where('stock', '<', 10)->count()
        ];

        $recentTransactions = Transaction::with(['user', 'payments'])
            ->latest()
            ->take(10)
            ->get();

        return view('home', compact('stats', 'recentTransactions'));
    }
}
