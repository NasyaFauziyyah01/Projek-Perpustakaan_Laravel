<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dan_lupa_password_dapat_diakses(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Lupa password?');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Kirim Kode OTP ke Email');
    }

    public function test_konten_email_otp_menampilkan_nama_dan_kode(): void
    {
        $mailable = new ResetPasswordOtpMail(
            userName: 'Siti Nurhaliza',
            otp: '482910',
            expiresInMinutes: 10,
        );

        $mailable->assertSeeInHtml('Siti Nurhaliza');
        $mailable->assertSeeInHtml('482910');
        $mailable->assertSeeInHtml('10 menit');
    }

    public function test_kirim_otp_berhasil_untuk_email_terdaftar(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'siswa@gmail.com',
        ]);

        $response = $this->post(route('password.otp.send'), [
            'email' => 'siswa@gmail.com',
        ]);

        $response->assertRedirect(route('password.reset.form'));
        $response->assertSessionHas('reset_email', 'siswa@gmail.com');

        Mail::assertSent(ResetPasswordOtpMail::class, function (ResetPasswordOtpMail $mail) use ($user) {
            return $mail->hasTo($user->email) && strlen($mail->otp) === 6 && ctype_digit($mail->otp);
        });

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'siswa@gmail.com',
        ]);
    }

    public function test_kirim_otp_via_brevo_http_api_jika_api_key_tersedia(): void
    {
        config(['services.brevo.key' => 'xkeysib-test-key']);
        config(['mail.from.address' => 'pengirim@gmail.com']);
        config(['mail.from.name' => 'Lentera Pustaka']);

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => '<test@brevo>'], 201),
        ]);

        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@gmail.com',
        ]);

        $response = $this->post(route('password.otp.send'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('password.reset.form'));
        Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'xkeysib-test-key')
                && $request['to'][0]['email'] === $user->email
                && str_contains($request['htmlContent'], 'Budi Santoso');
        });
    }

    public function test_kirim_otp_ditolak_jika_email_tidak_terdaftar(): void
    {
        Mail::fake();

        $response = $this->post(route('password.otp.send'), [
            'email' => 'tidakada@gmail.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        Mail::assertNothingSent();
    }

    public function test_reset_password_berhasil_dengan_otp_valid(): void
    {
        $user = User::factory()->create([
            'email' => 'siswa@gmail.com',
            'password' => Hash::make('passwordlama'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('654321'),
            'created_at' => now(),
        ]);

        $response = $this->withSession(['reset_email' => $user->email])
            ->post(route('password.reset.update'), [
                'email' => $user->email,
                'otp' => '654321',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('passwordbaru123', $user->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_reset_password_gagal_jika_otp_salah(): void
    {
        $user = User::factory()->create([
            'email' => 'siswa@gmail.com',
            'password' => Hash::make('passwordlama'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('654321'),
            'created_at' => now(),
        ]);

        $response = $this->withSession(['reset_email' => $user->email])
            ->post(route('password.reset.update'), [
                'email' => $user->email,
                'otp' => '000000',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ]);

        $response->assertSessionHasErrors(['otp']);
        $user->refresh();
        $this->assertTrue(Hash::check('passwordlama', $user->password));
    }

    public function test_reset_password_gagal_jika_otp_kedaluwarsa(): void
    {
        $user = User::factory()->create([
            'email' => 'siswa@gmail.com',
            'password' => Hash::make('passwordlama'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('654321'),
            'created_at' => now()->subMinutes(15),
        ]);

        $response = $this->withSession(['reset_email' => $user->email])
            ->post(route('password.reset.update'), [
                'email' => $user->email,
                'otp' => '654321',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ]);

        $response->assertSessionHasErrors(['otp']);
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }
}
