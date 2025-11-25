@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Proses Produk Khusus</h3>
                    </div>
                    <div class="card-body">
                        @if ($prodsus->count() > 0)
                            @foreach ($prodsus as $item)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5>{{ $item->name }}</h5>
                                                <p class="text-muted">{{ $item->description }}</p>
                                                <div class="row">
                                                    <div class="col-3">
                                                        <strong>Harga:</strong><br>
                                                        Rp {{ number_format($item->price, 0, ',', '.') }}
                                                    </div>
                                                    <div class="col-3">
                                                        <strong>Stok:</strong><br>
                                                        <span
                                                            class="badge bg-{{ $item->stock > 0 ? 'success' : 'warning' }}">
                                                            {{ $item->stock }}
                                                        </span>
                                                    </div>
                                                    <div class="col-3">
                                                        <strong>Barcode:</strong><br>
                                                        <code>{{ $item->barcode }}</code>
                                                    </div>
                                                    <div class="col-3">
                                                        <strong>Dibuat oleh:</strong><br>
                                                        {{ $item->creator->name }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <form class="process-form" data-id="{{ $item->id }}">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label for="barcode_file_{{ $item->id }}" class="form-label">
                                                            Upload Barcode PNG
                                                        </label>
                                                        <input type="file" class="form-control barcode-file"
                                                            id="barcode_file_{{ $item->id }}" name="barcode_file"
                                                            accept=".png" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="notes_{{ $item->id }}"
                                                            class="form-label">Catatan</label>
                                                        <textarea class="form-control" id="notes_{{ $item->id }}" name="notes" rows="2" placeholder="Opsional"></textarea>
                                                    </div>
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-success btn-approve"
                                                            data-action="approve">
                                                            <i class="fas fa-check"></i> Setujui
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-reject"
                                                            data-action="reject">
                                                            <i class="fas fa-times"></i> Tolak
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5>Tidak ada produk khusus yang perlu diproses</h5>
                                <p class="text-muted">Semua produk telah diproses.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle approve/reject buttons
            document.querySelectorAll('.btn-approve, .btn-reject').forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('.process-form');
                    const prodsusId = form.dataset.id;
                    const action = this.dataset.action;
                    const fileInput = form.querySelector('.barcode-file');
                    const notesInput = form.querySelector('textarea[name="notes"]');

                    // Validasi file
                    if (!fileInput.files[0]) {
                        alert('Harap pilih file barcode PNG terlebih dahulu!');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('barcode_file', fileInput.files[0]);
                    formData.append('action', action);
                    formData.append('notes', notesInput.value);
                    formData.append('_token', '{{ csrf_token() }}');

                    // Show loading
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    this.disabled = true;

                    // Debug log
                    console.log('Sending request to:', `/prodsus/${prodsusId}/process`);

                    fetch(`/prodsus/${prodsusId}/process`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            console.log('Response content-type:', response.headers.get(
                                'content-type'));

                            // Cek jika response adalah HTML (berarti error)
                            const contentType = response.headers.get('content-type');
                            if (contentType && contentType.includes('text/html')) {
                                return response.text().then(html => {
                                    throw new Error(
                                        'Server returned HTML instead of JSON. Possible authentication error.'
                                        );
                                });
                            }

                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                location.reload();
                            } else {
                                throw new Error(data.message || 'Unknown error occurred');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan: ' + error.message);
                            this.innerHTML = originalText;
                            this.disabled = false;
                        });
                });
            });
        });
    </script>
@endsection
