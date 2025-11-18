<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CashierController extends Controller
{
    public function showInitializeForm()
    {
        // Jika sudah inisialisasi, redirect ke home
        if (Session::get('cashier_initialized')) {
            return redirect()->route('home');
        }

        return view('cashier.initialize');
    }

    public function initialize(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|size:10'
        ]);

        // Cek jika NIK match dengan user yang login
        if ($request->nik !== Auth::user()->nik) {
            return redirect()->back()
                ->with('error', 'NIK tidak sesuai dengan akun yang login.')
                ->withInput();
        }

        // Set session inisialisasi
        Session::put('cashier_initialized', true);
        Session::put('cashier_initialized_at', now());
        Session::put('cashier_name', Auth::user()->name);

        return redirect()->route('home')
            ->with('success', 'Kasir berhasil diinisialisasi. Selamat bekerja!');
    }

    public function deinitialize()
    {
        Session::forget('cashier_initialized');
        Session::forget('cashier_initialized_at');
        Session::forget('cashier_name');

        return redirect()->route('home')
            ->with('success', 'Kasir berhasil logout dari sesi.');
    }
}
