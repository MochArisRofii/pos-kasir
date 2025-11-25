@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Produk Khusus (Prodsus)</h3>
                        <a href="{{ route('prodsus.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Prodsus
                        </a>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('barcode_download'))
                            <div class="alert alert-info">
                                <strong>Barcode berhasil digenerate!</strong>
                                <a href="{{ Storage::disk('public')->url(session('barcode_download')) }}" download
                                    class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="fas fa-download"></i> Download Barcode
                                </a>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                        <th>Stock</th>
                                        <th>Barcode</th>
                                        <th>Status</th>
                                        <th>Dibuat Oleh</th>
                                        <th>Diproses Oleh</th>
                                        <th>Tanggal Proses</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($prodsus as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->stock > 0 ? 'success' : 'danger' }}">
                                                    {{ $item->stock }}
                                                </span>
                                            </td>
                                            <td>

                                                <code>{{ $item->barcode }}</code>
                                                @if ($item->barcode_path && Storage::disk('public')->exists($item->barcode_path))
                                                    <br>
                                                    <small>
                                                        <a href="{{ route('prodsus.download-barcode', $item) }}"
                                                            class="text-primary">
                                                            <i class="fas fa-download"></i> Download Barcode
                                                        </a>
                                                    </small>
                                                @else
                                                    <br>
                                                    <small class="text-muted">Barcode belum tersedia</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $item->status == 'approved' ? 'success' : ($item->status == 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ strtoupper($item->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $item->creator->name }}</td>
                                            <td>{{ $item->processor->name ?? '-' }}</td>
                                            <td>
                                                @if ($item->processed_at)
                                                    <small class="text-muted">
                                                        {{ $item->processed_at->format('d/m/Y H:i') }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
