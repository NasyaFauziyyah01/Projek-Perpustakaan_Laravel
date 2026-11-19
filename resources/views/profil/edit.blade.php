@extends('layouts.app')

@section('title', 'Edit Profil — Perpustakaan')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-3 no-print">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('beranda')) }}" class="btn-back-nav">
                    <span class="back-nav-icon"><i class="bi bi-arrow-left"></i></span>
                    <span>Kembali</span>
                </a>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3 no-print">
                <div>
                    <h1 class="page-title mb-1">Profil Saya</h1>
                    <p class="page-subtitle mb-0">
                        {{ $user->isAdmin() ? 'Kelola informasi akun Anda' : 'Kelola informasi akun dan kartu anggota digital Anda' }}
                    </p>
                </div>
                @if(! $user->isAdmin())
                    <button type="button" onclick="window.print()" class="btn btn-outline-hijau btn-sm">
                        <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF Kartu
                    </button>
                @endif
            </div>

            {{-- ===== KARTU ANGGOTA DIGITAL (KHUSUS SISWA / ANGGOTA) ===== --}}
            @if(! $user->isAdmin())
                @php
                    $nomorKartu = $user->getNomorAnggota();
                    $qrPayload = urlencode("LENTERA PUSTAKA | {$nomorKartu} | {$user->name} | " . ($user->nis_nip ?: '-'));
                @endphp
                <div class="member-id-card mb-4" id="digitalMemberCard">
                    <div class="member-card-glow member-glow-gold" aria-hidden="true"></div>
                    <div class="member-card-glow member-glow-emerald" aria-hidden="true"></div>
                    <div class="member-card-ring" aria-hidden="true"></div>
                    <div class="member-card-shimmer" aria-hidden="true"></div>

                    {{-- Header Kartu --}}
                    <div class="member-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <div class="member-card-logo">
                                <img src="{{ asset('images/logo-lentera-icon.png') }}" alt="Logo Lentera Pustaka">
                            </div>
                            <div>
                                <div class="member-card-brand">LENTERA <span>PUSTAKA</span></div>
                                <div class="member-card-type">Kartu Anggota Perpustakaan</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="member-status-pill {{ $user->isAktif() ? 'is-active' : 'is-inactive' }}">
                                <span class="status-dot"></span>
                                {{ strtoupper($user->status ?? 'aktif') }}
                            </span>
                        </div>
                    </div>

                    {{-- Body Kartu: Avatar + Identitas + QR Code --}}
                    <div class="member-card-body">
                        <div class="member-identity-group">
                            <div class="member-avatar-ring">
                                <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <div class="member-main-info">
                                <div class="member-number-badge">
                                    <i class="bi bi-upc-scan me-1"></i> {{ $nomorKartu }}
                                </div>
                                <h3 class="member-name" title="{{ $user->name }}">{{ $user->name }}</h3>
                                <div class="member-email">
                                    <i class="bi bi-envelope-fill me-1"></i>{{ $user->email }}
                                </div>
                            </div>
                        </div>

                        <div class="member-qr-box" title="QR Identitas {{ $nomorKartu }}">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&margin=4&color=162E27&bgcolor=FDFBF6&data={{ $qrPayload }}"
                                 alt="QR {{ $nomorKartu }}"
                                 width="74"
                                 height="74"
                                 onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                            <div class="member-qr-fallback d-none">
                                <i class="bi bi-qr-code"></i>
                            </div>
                            <span class="member-qr-caption">VERIFIED ID</span>
                        </div>
                    </div>

                    {{-- Footer Strip Metadata --}}
                    <div class="member-card-footer">
                        <div class="member-meta-item">
                            <span class="member-meta-label">NISN</span>
                            <span class="member-meta-value">{{ $user->nis_nip ?: 'Belum diisi' }}</span>
                        </div>
                        <div class="member-meta-item">
                            <span class="member-meta-label">Kelas</span>
                            <span class="member-meta-value">{{ $user->kelas ?: 'Belum diisi' }}</span>
                        </div>
                        <div class="member-meta-item">
                            <span class="member-meta-label">No. Telepon</span>
                            <span class="member-meta-value">{{ $user->no_hp ?: 'Belum diisi' }}</span>
                        </div>
                        <div class="member-meta-item">
                            <span class="member-meta-label">Terdaftar</span>
                            <span class="member-meta-value">
                                {{ $user->created_at ? $user->created_at->translatedFormat('d M Y') : date('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card-perpus mb-4 no-print">
                <div class="card-header">
                    <i class="bi bi-person-circle me-2"></i> Informasi Profil
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profil.update') }}" class="form-perpus">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            @if($user->isAdmin())
                                {{-- NIP / Nomor Identitas untuk Admin / Petugas --}}
                                <div class="col-md-4 mb-3">
                                    <label for="nis_nip" class="form-label">NIP / Nomor Identitas</label>
                                    <input type="text" maxlength="30"
                                           class="form-control @error('nis_nip') is-invalid @enderror"
                                           id="nis_nip" name="nis_nip" value="{{ old('nis_nip', $user->nis_nip) }}"
                                           placeholder="Contoh: 198501012010011001">
                                    @error('nis_nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Jabatan untuk Admin / Petugas --}}
                                <div class="col-md-4 mb-3">
                                    <label for="kelas" class="form-label">Jabatan</label>
                                    <input type="text" maxlength="100" class="form-control @error('kelas') is-invalid @enderror"
                                           id="kelas" name="kelas" value="{{ old('kelas', $user->kelas) }}"
                                           placeholder="Contoh: Pustakawan / Pengelola Perpustakaan">
                                    @error('kelas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                {{-- NISN untuk Siswa / Anggota --}}
                                <div class="col-md-4 mb-3">
                                    <label for="nis_nip" class="form-label">NISN</label>
                                    <input type="text" inputmode="numeric" minlength="10" maxlength="10" pattern="[0-9]{10}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                           class="form-control @error('nis_nip') is-invalid @enderror"
                                           id="nis_nip" name="nis_nip" value="{{ old('nis_nip', $user->nis_nip) }}"
                                           placeholder="10 digit NISN">
                                    @error('nis_nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Kelas untuk Siswa / Anggota --}}
                                <div class="col-md-4 mb-3">
                                    <label for="kelas" class="form-label">Kelas</label>
                                    <input type="text" maxlength="20" class="form-control @error('kelas') is-invalid @enderror"
                                           id="kelas" name="kelas" value="{{ old('kelas', $user->kelas) }}"
                                           placeholder="Contoh: XII PPLG 1">
                                    @error('kelas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-md-4 mb-3">
                                <label for="no_hp" class="form-label">No. HP</label>
                                <input type="tel" inputmode="numeric" minlength="10" maxlength="13" pattern="[0-9]{10,13}"
                                       class="form-control @error('no_hp') is-invalid @enderror"
                                       id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                                       placeholder="Contoh: 081234567890">
                                @error('no_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-emas">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            {{-- Ganti Password --}}
            <div class="card-perpus no-print">
                <div class="card-header">
                    <i class="bi bi-shield-lock me-2"></i> Ganti Password
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profil.password') }}" class="form-perpus">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="new_password" name="password" placeholder="Minimal 8 karakter" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                       name="password_confirmation" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-emas">
                            <i class="bi bi-lock me-1"></i> Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
