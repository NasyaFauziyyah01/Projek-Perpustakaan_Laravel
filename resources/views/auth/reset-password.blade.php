@extends('layouts.app')

@section('title', 'Verifikasi OTP & Reset Password — Perpustakaan Sekolah')
@section('meta_description', 'Masukkan kode OTP 6 digit dari email Anda dan buat password baru untuk akun Lentera Pustaka.')
@section('hide_footer', true)

@section('content')
<div class="auth-wrapper">
    <a href="{{ route('password.request') }}" class="btn-back-nav auth-back-top">
        <span class="back-nav-icon"><i class="bi bi-arrow-left"></i></span>
        <span>Ganti Email</span>
    </a>

    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('images/logo-lentera-icon.png') }}" alt="Logo Lentera Pustaka" class="auth-logo-img mb-3">
            <h2>Reset Password</h2>
            <p>Masukkan kode OTP 6 digit yang telah dikirim ke <strong>{{ $email }}</strong></p>
        </div>

        <form method="POST" action="{{ route('password.reset.update') }}" class="form-perpus">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email) }}">

            {{-- Kode OTP --}}
            <div class="mb-3">
                <label for="otp" class="form-label">
                    <i class="bi bi-shield-check me-1"></i> Kode OTP (6 Digit)
                </label>
                <input type="text"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       minlength="6"
                       maxlength="6"
                       pattern="[0-9]{6}"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)"
                       class="form-control text-center fw-bold @error('otp') is-invalid @enderror"
                       style="letter-spacing: 0.45em; font-size: 1.25rem; font-family: monospace;"
                       id="otp"
                       name="otp"
                       value="{{ old('otp') }}"
                       placeholder="000000"
                       required
                       autofocus>
                @error('otp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text" style="font-size: 0.78rem;">
                    Kode OTP berlaku selama 10 menit. Cek kotak masuk atau folder Spam Gmail Anda.
                </div>
            </div>

            {{-- Password Baru --}}
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i> Password Baru
                </label>
                <div class="input-group">
                    <input type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           id="password"
                           name="password"
                           placeholder="Minimal 8 karakter"
                           required>
                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="toggleField('password', 'toggleIcon1')">
                        <i class="bi bi-eye" id="toggleIcon1"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">
                    <i class="bi bi-lock-fill me-1"></i> Konfirmasi Password Baru
                </label>
                <div class="input-group">
                    <input type="password"
                           class="form-control"
                           id="password_confirmation"
                           name="password_confirmation"
                           placeholder="Ulangi password baru"
                           required>
                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="toggleField('password_confirmation', 'toggleIcon2')">
                        <i class="bi bi-eye" id="toggleIcon2"></i>
                    </button>
                </div>
            </div>

            {{-- Tombol Simpan Password Baru --}}
            <button type="submit" class="btn btn-emas w-100 py-2 mb-3">
                <i class="bi bi-check-circle me-1"></i> Simpan Password Baru
            </button>
        </form>

        {{-- Form Kirim Ulang OTP --}}
        <form method="POST" action="{{ route('password.otp.send') }}" class="text-center">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email) }}">
            <span style="font-size: 0.86rem; color: var(--teks-lembut);">Tidak menerima kode?</span>
            <button type="submit" class="btn btn-link p-0 ms-1 fw-semibold text-decoration-none" style="font-size: 0.86rem; color: var(--green-800); vertical-align: baseline;">
                Kirim ulang OTP
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleField(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>
@endpush

