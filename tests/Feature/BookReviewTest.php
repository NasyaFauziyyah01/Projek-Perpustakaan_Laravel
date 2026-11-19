<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookReviewTest extends TestCase
{
    use RefreshDatabase;

    private function createBook(): Book
    {
        $category = Category::create(['nama' => 'Fiksi']);

        return Book::create([
            'category_id' => $category->id,
            'judul' => 'Bumi Manusia',
            'pengarang' => 'Pramoedya Ananta Toer',
            'penerbit' => 'Hasta Mitra',
            'tahun_terbit' => 1980,
            'stok' => 5,
        ]);
    }

    public function test_anggota_yang_sudah_meminjam_bisa_memberi_dan_mengupdate_ulasan(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $book = $this->createBook();

        Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->subDays(5)->toDateString(),
            'batas_kembali' => now()->addDays(2)->toDateString(),
            'status' => 'dikembalikan',
            'tanggal_kembali' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('katalog.ulasan.simpan', $book), [
            'rating' => 5,
            'ulasan' => 'Buku karya sastra yang sangat menginspirasi!',
        ]);

        $response->assertRedirect(route('katalog.show', $book).'#ulasan-buku');
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'ulasan' => 'Buku karya sastra yang sangat menginspirasi!',
        ]);

        // Update ulasan yang sama
        $this->actingAs($user)->post(route('katalog.ulasan.simpan', $book), [
            'rating' => 4,
            'ulasan' => 'Ulasan diperbarui setelah baca ulang.',
        ]);

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'ulasan' => 'Ulasan diperbarui setelah baca ulang.',
        ]);

        $this->get(route('katalog.show', $book))
            ->assertOk()
            ->assertSee('Ulasan diperbarui setelah baca ulang.')
            ->assertSee('4.0');
    }

    public function test_anggota_yang_belum_pernah_meminjam_ditolak_memberi_ulasan(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $book = $this->createBook();

        $response = $this->actingAs($user)->post(route('katalog.ulasan.simpan', $book), [
            'rating' => 5,
            'ulasan' => 'Coba review tanpa pinjam.',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_pemilik_atau_admin_bisa_menghapus_ulasan(): void
    {
        $owner = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $otherUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $book = $this->createBook();

        $review = Review::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'rating' => 5,
            'ulasan' => 'Sangat bagus!',
        ]);

        // User lain tidak boleh menghapus
        $this->actingAs($otherUser)
            ->delete(route('katalog.ulasan.hapus', [$book, $review]))
            ->assertForbidden();

        // Pemilik ulasan boleh menghapus
        $this->actingAs($owner)
            ->delete(route('katalog.ulasan.hapus', [$book, $review]))
            ->assertRedirect(route('katalog.show', $book).'#ulasan-buku');

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
