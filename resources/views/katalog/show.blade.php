@extends('layouts.app')

@section('title', $book->judul . ' — Katalog Buku')
@section('meta_description', Str::limit($book->sinopsis, 150))

@section('content')
<section class="section-buku-terbaru position-relative" style="min-height: 82vh;">
    {{-- Aurora Mesh, Mandala Berputar, & Bintang Emas Melayang --}}
    <div class="section-glow-orbs" aria-hidden="true">
        <div class="section-glow-1"></div>
        <div class="section-glow-2"></div>
        <div class="section-decor-ring section-ring-left"></div>
        <div class="section-decor-ring section-ring-right"></div>
    </div>
    <div class="section-ambient-particles" aria-hidden="true"></div>

    <div class="container py-5 position-relative" style="z-index: 2;">
        <!-- Tombol Kembali & Breadcrumb -->
        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('katalog.index') }}" class="btn-back-nav">
                <span class="back-nav-icon"><i class="bi bi-arrow-left"></i></span>
                <span>Kembali</span>
            </a>

            <nav aria-label="breadcrumb" class="mb-0">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('katalog.index') }}" class="fw-medium text-decoration-none" style="color: var(--green-700);">Katalog</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: var(--teks-lembut);">{{ Str::limit($book->judul, 35) }}</li>
                </ol>
            </nav>
        </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-perpus alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-perpus alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-5 mb-5">
        <!-- Cover Section -->
        <div class="col-md-4 col-lg-3 text-center">
            @if($book->sampul)
                <img src="{{ asset('storage/' . $book->sampul) }}" class="img-fluid rounded-4 shadow" alt="Cover {{ $book->judul }}"
                     onerror="this.onerror=null; this.classList.add('d-none'); if(this.nextElementSibling) this.nextElementSibling.classList.remove('d-none');">
                <div class="rounded-4 book-cover-placeholder shadow d-none" style="height: 400px; width: 100%;">
                    <i class="bi bi-book" style="font-size: 5rem;"></i>
                    <span style="font-size: 1rem;">Tanpa Sampul</span>
                </div>
            @else
                <div class="rounded-4 book-cover-placeholder shadow" style="height: 400px; width: 100%;">
                    <i class="bi bi-book" style="font-size: 5rem;"></i>
                    <span style="font-size: 1rem;">Tanpa Sampul</span>
                </div>
            @endif
        </div>

        <!-- Detail Section -->
        <div class="col-md-8 col-lg-9">
            @if($book->category)
                <span class="badge px-3 py-2 rounded-pill mb-3 fs-6" style="background: rgba(212, 162, 76, 0.15); color: var(--gold-800); border: 1px solid rgba(212, 162, 76, 0.3);">{{ $book->category->nama }}</span>
            @endif
            
            <h1 class="mb-2" style="font-family: var(--font-judul); font-weight: 700; color: var(--green-900);">{{ $book->judul }}</h1>
            <p class="fs-5 mb-4" style="color: var(--teks-lembut);">{{ $book->pengarang }}</p>

            <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                @if($book->stok > 0)
                    <span class="badge badge-status badge-tersedia px-3 py-2 fs-6 shadow-sm"><i class="bi bi-check2-circle me-1"></i> Tersedia: {{ $book->stok }} Buku</span>
                @else
                    <span class="badge badge-status badge-terlambat px-3 py-2 fs-6 shadow-sm"><i class="bi bi-x-circle me-1"></i> Stok Habis</span>
                @endif
                <a href="#ulasan-buku" class="text-decoration-none review-summary-pill">
                    <i class="bi bi-star-fill text-warning"></i>
                    @if($totalUlasan > 0)
                        <strong>{{ number_format($rataRating, 1) }}</strong>
                        <span>({{ $totalUlasan }} ulasan)</span>
                    @else
                        <span>Belum ada ulasan</span>
                    @endif
                </a>
                <span style="color: var(--teks-muted);"><i class="bi bi-upc-scan me-1"></i> ISBN: {{ $book->isbn ?? '-' }}</span>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: var(--putih); border: 1px solid var(--cream-200) !important;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2 d-flex align-items-center gap-2" style="color: var(--green-900);">
                        <i class="bi bi-file-earmark-text text-warning"></i> Sinopsis Buku
                    </h5>
                    <div class="card-text" style="color: var(--teks-lembut); line-height: 1.85; text-align: justify; white-space: pre-line; font-size: 0.98rem;">{{ trim($book->sinopsis) ?: 'Belum ada sinopsis untuk buku ini.' }}</div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 bg-white rounded-4 shadow-sm h-100" style="border: 1px solid var(--cream-200);">
                        <small class="d-block mb-1" style="color: var(--teks-muted);">Penerbit</small>
                        <span class="fw-semibold" style="color: var(--green-900);">{{ $book->penerbit ?? '-' }}</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 bg-white rounded-4 shadow-sm h-100" style="border: 1px solid var(--cream-200);">
                        <small class="d-block mb-1" style="color: var(--teks-muted);">Tahun Terbit</small>
                        <span class="fw-semibold" style="color: var(--green-900);">{{ $book->tahun_terbit ?? '-' }}</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 bg-white rounded-4 shadow-sm h-100" style="border: 1px solid var(--cream-200);">
                        <small class="d-block mb-1" style="color: var(--teks-muted);">Jml Halaman</small>
                        <span class="fw-semibold" style="color: var(--green-900);">{{ $book->jumlah_halaman ?? '-' }} hlm</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 bg-white rounded-4 shadow-sm h-100" style="border: 1px solid var(--cream-200);">
                        <small class="d-block mb-1" style="color: var(--teks-muted);">Lokasi Rak</small>
                        <span class="fw-semibold" style="color: var(--green-900);">{{ $book->lokasi_rak ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ULASAN & RATING PEMBACA ===== --}}
    <hr class="my-5" style="border-color: var(--cream-300);" id="ulasan-buku">
    <div class="row g-4 mb-5">
        {{-- Kiri: Ringkasan Rating & Form Ulasan --}}
        <div class="col-lg-5">
            <div class="review-box-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="section-badge mb-1">
                            <span class="badge-dot-emas"></span>
                            Suara Pembaca
                        </span>
                        <h4 class="fw-bold mb-0" style="font-family: var(--font-judul); color: var(--green-900);">Rating & Ulasan</h4>
                    </div>
                    <div class="review-score-badge text-center">
                        <div class="score-num">{{ $totalUlasan > 0 ? number_format($rataRating, 1) : '0.0' }}</div>
                        <div class="score-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= round($rataRating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>
                        <small>{{ $totalUlasan }} ulasan</small>
                    </div>
                </div>

                @auth
                    @if($pernahPinjam)
                        <form action="{{ route('katalog.ulasan.simpan', $book) }}" method="POST" class="mt-3 pt-3" style="border-top: 1px dashed var(--cream-300);">
                            @csrf
                            <label class="form-label fw-bold mb-2" style="color: var(--green-900);">
                                {{ $ulasanSaya ? 'Perbarui Ulasan Anda' : 'Beri Rating & Ulasan Buku Ini' }}
                            </label>

                            {{-- Pilihan Bintang Interaktif (5 ke 1 untuk efek CSS sibling hover) --}}
                            @php
                                $selectedRating = (int) old('rating', $ulasanSaya?->rating ?? 5);
                            @endphp
                            <div class="star-rating-picker mb-3">
                                @for($star = 5; $star >= 1; $star--)
                                    <input type="radio" id="star-{{ $star }}" name="rating" value="{{ $star }}" {{ $selectedRating === $star ? 'checked' : '' }} required>
                                    <label for="star-{{ $star }}" title="{{ $star }} Bintang">
                                        <i class="bi bi-star-fill"></i>
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            <div class="mb-3">
                                <label for="ulasan" class="form-label small fw-semibold" style="color: var(--teks-lembut);">Pendapat Anda setelah membaca buku ini</label>
                                <textarea name="ulasan" id="ulasan" rows="3" maxlength="1000" required
                                          class="form-control @error('ulasan') is-invalid @enderror"
                                          placeholder="Ceritakan kesan, manfaat, atau bagian menarik dari buku ini...">{{ old('ulasan', $ulasanSaya?->ulasan) }}</textarea>
                                @error('ulasan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-emas w-100">
                                <i class="bi bi-send-check me-1"></i>
                                {{ $ulasanSaya ? 'Simpan Perubahan Ulasan' : 'Kirim Ulasan' }}
                            </button>
                        </form>
                    @else
                        <div class="review-info-notice mt-3">
                            <i class="bi bi-journal-check fs-4"></i>
                            <div>
                                <strong>Ingin memberikan ulasan?</strong>
                                <p class="mb-0 small">Ulasan hanya dapat ditulis oleh anggota yang sudah pernah atau sedang meminjam buku ini.</p>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="review-info-notice mt-3">
                        <i class="bi bi-person-lock fs-4"></i>
                        <div>
                            <strong>Bagikan ulasan bacaanmu!</strong>
                            <p class="mb-2 small">Silakan masuk dan pinjam buku ini untuk memberikan rating & ulasan.</p>
                            <a href="{{ route('login') }}" class="btn btn-sm btn-emas">Masuk Sekarang</a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Kanan: Daftar Ulasan Pembaca --}}
        <div class="col-lg-7">
            @if($reviews->isNotEmpty())
                <div class="d-flex flex-column gap-3">
                    @foreach($reviews as $rev)
                        <div class="review-item-card p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="review-avatar">
                                        {{ strtoupper(substr($rev->user?->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: var(--green-900); font-size: 0.95rem;">
                                            {{ $rev->user?->name ?? 'Anggota Perpustakaan' }}
                                            @if(auth()->id() === $rev->user_id)
                                                <span class="badge rounded-pill ms-1" style="background: rgba(212, 162, 76, 0.2); color: var(--gold-800); font-size: 0.68rem;">Anda</span>
                                            @endif
                                        </div>
                                        <div class="small" style="color: var(--teks-muted); font-size: 0.76rem;">
                                            {{ $rev->user?->kelas ?: 'Pembaca Terverifikasi' }} &bull; {{ $rev->updated_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <div class="review-item-stars" aria-label="Rating {{ $rev->rating }} dari 5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $rev->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </div>

                                    @auth
                                        @if(auth()->id() === $rev->user_id || auth()->user()->isAdmin())
                                            <form action="{{ route('katalog.ulasan.hapus', [$book, $rev]) }}" method="POST"
                                                  onsubmit="return confirm('Hapus ulasan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 ms-1" title="Hapus Ulasan">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>
                            </div>

                            <p class="mb-0 mt-2" style="color: var(--teks-lembut); line-height: 1.7; font-size: 0.93rem; white-space: pre-line;">{{ $rev->ulasan }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="review-empty-card p-5 text-center h-100 d-flex flex-column align-items-center justify-content-center">
                    <div class="review-empty-icon mb-3">
                        <i class="bi bi-chat-square-quote"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: var(--green-900);">Belum Ada Ulasan</h5>
                    <p class="mb-0" style="color: var(--teks-lembut); max-width: 380px; font-size: 0.9rem;">
                        Jadilah pembaca pertama yang memberikan rating dan ulasan setelah membaca buku <strong>{{ $book->judul }}</strong>!
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Buku Terkait Section -->
    @if($bukuTerkait->count() > 0)
        <hr class="my-5" style="border-color: var(--cream-300);">
        <div class="mb-4 d-flex justify-content-between align-items-end flex-wrap gap-2">
            <div>
                <h3 class="fw-bold m-0" style="font-family: var(--font-judul); color: var(--green-900);">Buku Terkait</h3>
                <p class="m-0 mt-1" style="color: var(--teks-lembut);">Koleksi lain dalam kategori yang sama</p>
            </div>
            <a href="{{ route('katalog.index', ['kategori' => $book->category_id]) }}" class="fw-medium text-decoration-none" style="color: var(--green-700);">Lihat Semua <i class="bi bi-arrow-right"></i></a>
        </div>
        
        <div class="katalog-grid">
            @foreach($bukuTerkait as $terkait)
                <x-book-card :book="$terkait" />
            @endforeach
        </div>
    @endif
    </div>
</section>
@endsection
