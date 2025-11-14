@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Products</h3>
                        <div>
                            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal"
                                data-bs-target="#importModal">
                                <i class="fas fa-file-import"></i> Import CSV
                            </button>
                            <a href="{{ route('products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Product
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                <h5>Import Error:</h5>
                                {{ session('error') }}

                                @if (app()->hasDebugModeEnabled())
                                    <hr>
                                    <small class="text-muted">
                                        Check storage/logs/laravel.log for more details
                                    </small>
                                @endif
                            </div>
                        @endif

                        <!-- Tambahkan debug info -->
                        @if ($errors->any())
                            <div class="alert alert-warning">
                                <h5>Validation Errors:</h5>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($products->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Barcode</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $product)
                                            <tr>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->category->name }}</td>
                                                <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger') }}">
                                                        {{ $product->stock }}
                                                    </span>
                                                </td>
                                                <td>{{ $product->barcode ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('products.show', $product) }}"
                                                        class="btn btn-sm btn-info">View</a>
                                                    <a href="{{ route('products.edit', $product) }}"
                                                        class="btn btn-sm btn-warning">Edit</a>
                                                    <form action="{{ route('products.destroy', $product) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No products found.
                                <a href="{{ route('products.create') }}">Add the first product</a> or
                                <a href="#" data-bs-toggle="modal" data-bs-target="#importModal">import from CSV</a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Products from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6>Format CSV yang Didukung:</h6>
                        <ul class="mb-0">
                            <li>File harus berformat .csv</li>
                            <li>Baris pertama harus berisi header</li>
                            <li>Kolom yang diperlukan: <strong>nama_produk, harga, stok, kategori</strong></li>
                            <li>Kolom opsional: barcode, deskripsi</li>
                            <li>Gunakan koma sebagai pemisah</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('products.download-template') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download"></i> Download Template CSV
                        </a>
                    </div>

                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data"
                        id="importForm">
                        @csrf
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Select File</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file"
                                accept=".csv,.xlsx,.xls" required>
                            <div class="form-text">Format yang didukung: .csv, .xlsx, .xls | Max size: 2MB</div>
                        </div>
                    </form>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr class="table-light">
                                    <th>nama_produk</th>
                                    <th>harga</th>
                                    <th>stok</th>
                                    <th>kategori</th>
                                    <th>barcode</th>
                                    <th>deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Indomie Goreng</td>
                                    <td>3500</td>
                                    <td>100</td>
                                    <td>Makanan</td>
                                    <td>1234567890123</td>
                                    <td>Mi instan rasa goreng</td>
                                </tr>
                                <tr>
                                    <td>Coca Cola</td>
                                    <td>8000</td>
                                    <td>50</td>
                                    <td>Minuman</td>
                                    <td>1234567890124</td>
                                    <td>Minuman bersoda</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="importForm" class="btn btn-success">
                        <i class="fas fa-file-import"></i> Import Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>Importing Products...</h5>
                    <p class="text-muted">Please wait while we process your file.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('importForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('excel_file');
            const file = fileInput.files[0];

            if (!file) {
                e.preventDefault();
                alert('Please select a file first!');
                return;
            }

            // Validasi ekstensi file
            const allowedExtensions = ['csv', 'xlsx', 'xls'];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!allowedExtensions.includes(fileExtension)) {
                e.preventDefault();
                alert('Please select a valid file (.csv, .xlsx, .xls). Your file: .' + fileExtension);
                return;
            }

            // Validasi size file (2MB)
            if (file.size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('File size must be less than 2MB');
                return;
            }

            // Show loading modal
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            loadingModal.show();
        });
    </script>

    <style>
        .table th {
            background-color: #f8f9fa;
        }
    </style>
@endsection
