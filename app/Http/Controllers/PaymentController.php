<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function create(Transaction $transaction)
    {
        return view('payments.create', compact('transaction'));
    }

    public function store(Request $request, Transaction $transaction)
    {
        $request->validate([
            'method' => 'required|in:cash',
            'amount_numeric' => 'required|numeric|min:' . $transaction->final_amount
        ]);

        // Gunakan amount_numeric dari hidden input
        $amount = (int) $request->amount_numeric;

        // Validasi final amount
        if ($amount < $transaction->final_amount) {
            return redirect()->back()
                ->with('error', 'Amount paid must be greater than or equal to total amount!')
                ->withInput();
        }

        // Buat payment
        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'method' => $request->method,
            'amount' => $amount,
            'status' => 'success'
        ]);

        // Update status transaction menjadi paid
        $transaction->update(['status' => 'paid']);

        // Hitung kembalian
        $change = $amount - $transaction->final_amount;

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Payment processed successfully.')
            ->with('change', $change)
            ->with('amount_paid', $amount);
    }

    // NONAKTIFKAN SEMENTARA FUNCTION QRIS
    /*
    private function generateQrisData(Transaction $transaction, Payment $payment)
    {
        // Simulasi data QRIS
        $qrisData = [
            'transaction_id' => $transaction->id,
            'amount' => $payment->amount,
            'merchant_name' => config('app.name', 'POS Kasir'),
            'merchant_city' => 'Jakarta',
            'timestamp' => now()->toISOString(),
        ];

        return json_encode($qrisData);
    }

    public function checkQrisStatus(Payment $payment)
    {
        // Check payment status
        if ($payment->method === 'qris' && $payment->status === 'pending') {
            // Simulate payment confirmation
            if (now()->diffInSeconds($payment->created_at) > 10) {
                $payment->update(['status' => 'success']);
                $payment->transaction->update(['status' => 'paid']);

                return response()->json([
                    'status' => 'success',
                    'transaction_status' => 'paid',
                    'message' => 'Payment successful!'
                ]);
            }
        }

        return response()->json([
            'status' => $payment->status,
            'transaction_status' => $payment->transaction->status,
            'message' => 'Payment is still pending'
        ]);
    }
    */
}
