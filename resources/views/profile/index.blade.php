@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-person-circle text-primary me-2"></i> Account Profile</h3>
        <p class="text-muted small mb-0">Manage your profile details, user credentials, and security password.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="kv-card text-center py-4">
            <div class="brand-logo-icon mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h4 class="fw-extrabold mb-1">{{ $user->name }}</h4>
            <div class="badge bg-primary-subtle text-primary fs-7 mb-2">{{ strtoupper($user->role) }}</div>
            <div class="text-muted small mb-3">Username: <code class="text-dark">{{ $user->username }}</code></div>
            <div class="border-top pt-3 text-muted small">
                Member since {{ $user->created_at->format('M Y') }}
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="kv-card">
            <h5 class="fw-bold mb-3 border-bottom pb-2">Update Credentials</h5>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small">Full Name *</label>
                    <input type="text" name="name" class="form-control form-control-kv" value="{{ $user->name }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Email Address</label>
                    <input type="email" name="email" class="form-control form-control-kv" value="{{ $user->email }}">
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3 text-muted fs-7 text-uppercase">Change Password</h6>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Current Password</label>
                    <input type="password" name="current_password" class="form-control form-control-kv" placeholder="******">
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <label class="form-label fw-bold small">New Password</label>
                        <input type="password" name="new_password" class="form-control form-control-kv" placeholder="******">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control form-control-kv" placeholder="******">
                    </div>
                </div>

                <button type="submit" class="btn btn-kv-primary px-4 py-2">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
