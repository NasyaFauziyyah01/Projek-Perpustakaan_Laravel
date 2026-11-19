@extends('layouts.app')

@section('title', 'Lupa Password — Perpustakaan Sekolah')
@section('meta_description', 'Reset password akun Lentera Pustaka Anda menggunakan kode OTP yang dikirim ke email.')
@section('hide_footer', true)

@section('content')
<div class="auth-wrapper">
    <a href="{{ route('login') }}" class="btn-back-nav auth-back-top">
        <span class="back-nav-icon"><i class="bi bi-arrow-left"></i></span>
        <span>Kembali ke Login</span>
    </a>

    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('images/logo-lentera-icon.png') }}" alt="Logo Lentera Pustaka" class="auth-logo-img mb-3">
            <h2>Lupa Password</h2>
            <p>Masukkan email terdaftar Anda untuk menerima kode OTP 6 digit di Gmail</p>
        </div>

        <form method="POST" action="{{ route('password.otp.send') }}" class="form-perpus">
            @csrf

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-1"></i> Email Terdaftar
                </label>
                <input type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="nama@gmail.com"
                       required
                       autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tombol Kirim OTP --}}
            <button type="submit" class="btn btn-emas w-100 py-2 mb-3">
                <i class="bi bi-send me-1"></i> Kirim Kode OTP ke Email
            </button>

            <p class="text-center mb-0" style="font-size: 0.9rem; color: var(--teks-lembut);">
                Sudah ingat password?
                <a href="{{ route('login') }}" class="fw-semibold" style="color: var(--green-800);">Masuk di sini</a>
            </p>
        </form>
    </div>
</div>
@endsection

