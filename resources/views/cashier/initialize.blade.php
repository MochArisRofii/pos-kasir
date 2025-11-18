<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inisialisasi Kasir - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .init-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .init-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .init-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .init-body {
            padding: 2rem;
        }
        .user-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .btn-init {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-init:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
    </style>
</head>
<body>
    <div class="init-container">
        <div class="init-card">
            <div class="init-header">
                <div class="init-logo">
                    <i class="fas fa-cash-register fa-3x"></i>
                </div>
                <h3 class="mt-3">Inisialisasi Kasir</h3>
                <p class="mb-0">Verifikasi identitas untuk memulai sesi kasir</p>
            </div>
            <div class="init-body">
                <div class="user-info">
                    <h6><i class="fas fa-user me-2"></i>Informasi User</h6>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Nama:</small>
                            <br>
                            <strong>{{ Auth::user()->name }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Role:</small>
                            <br>
                            <strong class="text-capitalize">{{ Auth::user()->role }}</strong>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('cashier.initialize.submit') }}">
                    @csrf

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="nik" class="form-label">Masukkan NIK untuk Verifikasi</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('nik') is-invalid @enderror" 
                                   id="nik" 
                                   name="nik" 
                                   placeholder="16 digit NIK" 
                                   required 
                                   autofocus
                                   maxlength="16">
                        </div>
                        <div class="form-text">
                            Masukkan NIK Anda untuk memverifikasi identitas sebagai kasir.
                        </div>
                        @error('nik')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-init w-100 mb-3">
                        <i class="fas fa-play-circle me-2"></i>Mulai Sesi Kasir
                    </button>

                    <div class="text-center">
                        <a href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                           class="text-decoration-none">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </div>
                </form>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Format NIK input (hanya angka, max 16 digit)
        document.getElementById('nik').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16);
        });

        // Auto focus pada input NIK
        document.getElementById('nik').focus();
    </script>
</body>
</html>