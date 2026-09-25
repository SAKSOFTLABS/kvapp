<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kerala Vision Enterprise</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/kerala-vision.css') }}">

    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 440px;
            padding: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="brand-logo-icon mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.6rem; border-radius: 16px;">KV</div>
            <h3 class="fw-extrabold text-dark mb-1">Kerala Vision</h3>
            <p class="text-muted small fw-medium">Enterprise Stock & Service Management</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger rounded-3 py-2 small mb-3">
                <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success rounded-3 py-2 small mb-3">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label fw-bold small text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="username" id="username" class="form-control form-control-kv border-start-0 @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="Enter your username" required autofocus>
                </div>
                @error('username')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label fw-bold small text-secondary">Password</label>
                    <a href="javascript:void(0)" onclick="alert('Please contact system administrator to reset password.')" class="small text-primary text-decoration-none">Forgot Password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" id="password" class="form-control form-control-kv border-start-0" placeholder="Enter your password" required>
                </div>
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-muted" for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn btn-kv-primary w-100 py-2.5 fs-6 mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i> Secure Sign In
            </button>
        </form>

        <div class="bg-light p-3 rounded-3 mt-4 border text-center">
            <div class="fw-bold small text-dark mb-1"><i class="bi bi-key-fill text-warning me-1"></i> Demo Credentials</div>
            <div class="d-flex justify-content-around small text-muted">
                <div><strong>Admin:</strong> admin / admin123</div>
                <div><strong>Staff:</strong> rajesh / staff123</div>
            </div>
        </div>

        <div class="text-center mt-4 text-muted small" style="font-size: 0.78rem;">
            &copy; {{ date('Y') }} Kerala Vision Digital Services. All rights reserved.
        </div>
    </div>
</body>
</html>
