@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Transaction #{{ $transaction->transaction_code }}</h3>
                    @if($transaction->status === 'paid')
                        <span class="badge bg-success fs-6">PAID</span>
                    @elseif($transaction->status === 'pending')
                        <span class="badge bg-warning fs-6">PENDING PAYMENT</span>
                    @else
                        <span class="badge bg-danger fs-6">CANCELLED</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date:</strong> {{ $transaction->created_at->format('d/m/Y H:i') }}<br>
                            <strong>Cashier:</strong> {{ $transaction->user->name }}<br>
                            <strong>Status:</strong> 
                            <span class="badge bg-{{ $transaction->status === 'paid' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ strtoupper($transaction->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaction->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Tax (10%):</th>
                                    <td>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Total:</th>
                                    <td class="fw-bold">Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($transaction->payments->count() === 0 && $transaction->status === 'pending')
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('payments.create', $transaction) }}" class="btn btn-success btn-lg">
                                Process Payment
                            </a>
                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Cancel this transaction?')">
                                    Cancel Transaction
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($transaction->payments->count() > 0)
                        <div class="mt-4">
                            <h5>Payment Information</h5>
                            @foreach($transaction->payments as $payment)
                                <div class="border p-3 mb-3 bg-light">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Method:</strong> {{ strtoupper($payment->method) }}</p>
                                            <p><strong>Amount Paid:</strong> Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Status:</strong> 
                                                <span class="badge bg-success">
                                                    SUCCESS
                                                </span>
                                            </p>
                                            <p><strong>Payment Date:</strong> {{ $payment->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    
                                    @if($payment->method === 'cash' && $payment->amount > $transaction->final_amount)
                                        <div class="alert alert-success mt-2">
                                            <strong>Change: Rp {{ number_format($payment->amount - $transaction->final_amount, 0, ',', '.') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                            New Transaction
                        </a>
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary">
                            View All Transactions
                        </a>
                        @if($transaction->status === 'paid')
                            <a href="#" class="btn btn-outline-success" onclick="window.print()">
                                Print Receipt
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Change Display -->
@if(session('change') && session('change') > 0)
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Payment Successful</h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h4>Payment Completed Successfully!</h4>
                <div class="alert alert-success mt-3">
                    <h5 class="mb-1">Change Amount</h5>
                    <h3 class="text-success mb-0">Rp {{ number_format(session('change'), 0, ',', '.') }}</h3>
                </div>
                <p class="text-muted">Total: Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</p>
                <p class="text-muted">Paid: Rp {{ number_format(session('amount_paid'), 0, ',', '.') }}</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success btn-lg" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
});
</script>
@endif
@endsection