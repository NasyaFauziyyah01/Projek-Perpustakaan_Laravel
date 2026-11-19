<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        $kategoriList = Category::orderBy('nama', 'asc')->get();

        $booksQuery = Book::with('category')
            ->search($request->q)
            ->kategori($request->kategori)
            ->tersedia($request->tersedia);

        // Sorting
        if ($request->urutan == 'z-a') {
            $booksQuery->orderBy('judul', 'desc');
        } elseif ($request->urutan == 'a-z') {
            $booksQuery->orderBy('judul', 'asc');
        } else {
            $booksQuery->orderBy('created_at', 'desc'); // default Terbaru
        }

        $books = $booksQuery->paginate(10)->withQueryString();

        return view('katalog.index', compact('books', 'kategoriList'));
    }

    public function show(Book $book)
    {
        $book->load('category');
        $bukuTerkait = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $reviews = collect();
        if (Schema::hasTable('reviews')) {
            $reviews = $book->reviews()
                ->with('user')
                ->orderByDesc('updated_at')
                ->get();
        }

        $rataRating = $reviews->isNotEmpty() ? round((float) $reviews->avg('rating'), 1) : 0.0;
        $totalUlasan = $reviews->count();

        $pernahPinjam = auth()->check()
            && Loan::where('user_id', auth()->id())
                ->where('book_id', $book->id)
                ->exists();

        $ulasanSaya = auth()->check()
            ? $reviews->firstWhere('user_id', auth()->id())
            : null;

        return view('katalog.show', compact(
            'book',
            'bukuTerkait',
            'reviews',
            'rataRating',
            'totalUlasan',
            'pernahPinjam',
            'ulasanSaya',
        ));
    }

    public function simpanUlasan(Request $request, Book $book)
    {
        $user = auth()->user();

        $pernahPinjam = Loan::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->exists();

        if (! $pernahPinjam) {
            return back()->with('error', 'Anda hanya dapat memberikan ulasan pada buku yang pernah atau sedang Anda pinjam.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'ulasan' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'rating.required' => 'Silakan pilih rating bintang (1 sampai 5).',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'ulasan.required' => 'Isi ulasan tidak boleh kosong.',
            'ulasan.min' => 'Ulasan minimal 3 karakter.',
            'ulasan.max' => 'Ulasan maksimal 1000 karakter.',
        ]);

        Review::updateOrCreate(
            [
                'user_id' => $user->id,
                'book_id' => $book->id,
            ],
            [
                'rating' => $validated['rating'],
                'ulasan' => trim($validated['ulasan']),
            ]
        );

        return redirect()
            ->to(route('katalog.show', $book).'#ulasan-buku')
            ->with('success', 'Ulasan Anda berhasil disimpan! Terima kasih telah berbagi pendapat.');
    }

    public function hapusUlasan(Book $book, Review $review)
    {
        $user = auth()->user();

        if ($review->book_id !== $book->id || ($review->user_id !== $user->id && ! $user->isAdmin())) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus ulasan ini.');
        }

        $review->delete();

        return redirect()
            ->to(route('katalog.show', $book).'#ulasan-buku')
            ->with('success', 'Ulasan berhasil dihapus.');
    }

    public function pinjam(Request $request, Book $book)
    {
        $user = auth()->user();

        // Cek jika belum login
        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk meminjam buku.');
        }

        // Cek status user
        if ($user->status !== 'aktif') {
            return back()->with('error', 'Akun Anda sedang dinonaktifkan. Anda tidak dapat meminjam buku.');
        }

        // Cek apakah user sudah meminjam buku ini dan belum dikembalikan
        $alreadyBorrowed = Loan::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'dipinjam')
            ->exists();

        if ($alreadyBorrowed) {
            return back()->with('error', 'Anda sudah meminjam buku ini dan belum mengembalikannya.');
        }

        if ($book->stok <= 0) {
            return back()->with('error', 'Mohon maaf, stok buku sedang habis.');
        }

        try {
            DB::transaction(function () use ($book, $user) {
                // Lock the row for update to prevent race conditions
                $lockedBook = Book::where('id', $book->id)->lockForUpdate()->first();

                if ($lockedBook->stok <= 0) {
                    throw new \Exception('Stok habis');
                }

                $lockedBook->decrement('stok');

                Loan::create([
                    'user_id' => $user->id,
                    'book_id' => $lockedBook->id,
                    'tanggal_pinjam' => now()->toDateString(),
                    'batas_kembali' => now()->addDays(7)->toDateString(),
                    'status' => 'dipinjam',
                ]);
            });

            return redirect()->route('anggota.peminjaman')->with('success', 'Buku berhasil dipinjam! Silakan ambil di perpustakaan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal meminjam buku. Silakan coba lagi. Error: '.$e->getMessage());
        }
    }
}
