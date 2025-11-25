@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>
                    {{-- Di bagian status info --}}
                    @if (Auth::user()->role === 'cashier')
                        @if (Session::get('cashier_initialized'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Kasir Terinisialisasi</strong> -
                                Sesi dimulai: {{ Session::get('cashier_initialized_at') }} |
                                Kasir: {{ Session::get('cashier_name') }}

                                {{-- PERBAIKAN: Gunakan form dengan method POST --}}
                                {{-- Bisa menggunakan link GET --}}
                                <a href="{{ route('cashier.deinitialize.get') }}" class="btn btn-sm btn-outline-danger ms-2"
                                    onclick="return confirm('Yakin ingin mengakhiri sesi kasir?')">
                                    <i class="fas fa-stop-circle me-1"></i>Akhiri Sesi
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Kasir Belum Terinisialisasi</strong> -
                                <a href="{{ route('cashier.initialize') }}" class="alert-link">
                                    Klik di sini untuk inisialisasi
                                </a>
                            </div>
                        @endif
                    @endif

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h5 class="card-title">Today's Sales</h5>
                                        <h3 class="card-text">Rp {{ number_format($stats['today_sales'], 0, ',', '.') }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h5 class="card-title">Today's Transactions</h5>
                                        <h3 class="card-text">{{ $stats['today_transactions'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-info">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Products</h5>
                                        <h3 class="card-text">{{ $stats['total_products'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                        <h5 class="card-title">Low Stock Products</h5>
                                        <h3 class="card-text">{{ $stats['low_stock_products'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Recent Transactions</h5>
                                    </div>
                                    <div class="card-body">
                                        @if ($recentTransactions->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Code</th>
                                                            <th>Date</th>
                                                            <th>Amount</th>
                                                            <th>Status</th>
                                                            <th>Payment Method</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($recentTransactions as $transaction)
                                                            <tr>
                                                                <td>
                                                                    <a
                                                                        href="{{ route('transactions.show', $transaction) }}">
                                                                        {{ $transaction->transaction_code }}
                                                                    </a>
                                                                </td>
                                                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}
                                                                </td>
                                                                <td>Rp
                                                                    {{ number_format($transaction->final_amount, 0, ',', '.') }}
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="badge bg-{{ $transaction->status === 'paid' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                                                        {{ strtoupper($transaction->status) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    @if ($transaction->payments->count() > 0)
                                                                        {{ strtoupper($transaction->payments->first()->method) }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted">No transactions found.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Quick Actions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            @if (Auth::user()->role === 'cashier')
                                                <a href="{{ route('transactions.create') }}"
                                                    class="btn btn-primary btn-lg">
                                                    New Transaction
                                                </a>
                                            @endif
                                            @if (Auth::user()->role === 'admin')
                                                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                                                    Manage Products
                                                </a>
                                            @endif
                                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary">
                                                View All Transactions
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
