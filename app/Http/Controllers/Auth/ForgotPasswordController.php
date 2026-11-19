<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    private const OTP_EXPIRY_MINUTES = 10;

    /**
     * Tampilkan form input email untuk lupa password.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim kode OTP 6 digit ke email user.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tersebut tidak terdaftar di sistem kami.',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        try {
            Mail::to($user->email)->send(new ResetPasswordOtpMail(
                userName: $user->name,
                otp: $otp,
                expiresInMinutes: self::OTP_EXPIRY_MINUTES,
            ));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email OTP reset password', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal mengirim email OTP. Pastikan konfigurasi SMTP email server sudah benar.');
        }

        session(['reset_email' => $user->email]);

        return redirect()
            ->route('password.reset.form')
            ->with('success', 'Kode OTP 6 digit telah dikirim ke '.$user->email.'. Silakan cek kotak masuk atau folder Spam Gmail Anda.');
    }

    /**
     * Tampilkan form verifikasi OTP dan input password baru.
     */
    public function showResetForm(): View|RedirectResponse
    {
        $email = session('reset_email') ?? old('email');

        if (! $email) {
            return redirect()
                ->route('password.request')
                ->with('error', 'Silakan masukkan email Anda terlebih dahulu untuk menerima kode OTP.');
        }

        return view('auth.reset-password', compact('email'));
    }

    /**
     * Verifikasi OTP dan perbarui password user.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.required' => 'Email tidak ditemukan.',
            'email.exists' => 'Email tidak terdaftar.',
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits' => 'Kode OTP harus berupa tepat 6 digit angka.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (! $record) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['otp' => 'Kode OTP tidak ditemukan. Silakan minta kode OTP baru.']);
        }

        if (Carbon::parse($record->created_at)->addMinutes(self::OTP_EXPIRY_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa (lebih dari 10 menit). Silakan kirim ulang kode baru.']);
        }

        if (! Hash::check($validated['otp'], $record->token)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['otp' => 'Kode OTP tidak sesuai. Silakan periksa kembali kode di email Anda.']);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
        session()->forget('reset_email');

        return redirect()
            ->route('login')
            ->with('success', 'Password berhasil direset! Silakan masuk menggunakan password baru Anda.');
    }
}
