<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Password — Lentera Pustaka</title>
</head>
<body style="margin:0; padding:0; background-color:#F6F1E5; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#1F2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#F6F1E5; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px; background-color:#FFFFFF; border-radius:18px; overflow:hidden; border:1px solid #E5D7B8; box-shadow:0 10px 28px rgba(22,46,39,0.08);">
                    {{-- Header --}}
                    <tr>
                        <td style="background:linear-gradient(135deg, #162E27 0%, #275347 100%); padding:28px 24px; text-align:center; border-bottom:3px solid #D4A24C;">
                            <div style="font-size:22px; font-weight:800; color:#FDFBF6; letter-spacing:-0.3px;">
                                Lentera <span style="color:#F0C674;">Pustaka</span>
                            </div>
                            <div style="font-size:12px; color:rgba(253,251,246,0.78); margin-top:4px; letter-spacing:1.5px; text-transform:uppercase;">
                                Perpustakaan Sekolah
                            </div>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px 28px;">
                            <p style="margin:0 0 12px; font-size:16px; font-weight:700; color:#162E27;">
                                Halo, {{ $userName }}!
                            </p>
                            <p style="margin:0 0 22px; font-size:14px; line-height:1.65; color:#4B5563;">
                                Kami menerima permintaan untuk mereset password akun <strong>Lentera Pustaka</strong> Anda. Masukkan kode OTP berikut pada halaman verifikasi:
                            </p>

                            {{-- Kotak Kode OTP --}}
                            <div style="background-color:#F6FAF7; border:2px dashed #D4A24C; border-radius:14px; padding:20px; text-align:center; margin:0 0 22px;">
                                <div style="font-size:11px; text-transform:uppercase; letter-spacing:2px; color:#6B7280; margin-bottom:8px; font-weight:700;">
                                    Kode Verifikasi OTP
                                </div>
                                <div style="font-size:34px; font-weight:800; letter-spacing:10px; color:#162E27; font-family:'Courier New', Courier, monospace; padding-left:10px;">
                                    {{ $otp }}
                                </div>
                            </div>

                            <p style="margin:0 0 14px; font-size:13px; line-height:1.6; color:#4B5563;">
                                Kode OTP ini hanya berlaku selama <strong>{{ $expiresInMinutes }} menit</strong>. Demi keamanan, <strong>jangan bagikan kode ini kepada siapa pun</strong>.
                            </p>
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#6B7280;">
                                Jika Anda tidak merasa meminta reset password, abaikan email ini — akun Anda tetap aman.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#FAF6EC; padding:18px 24px; text-align:center; border-top:1px solid #EDE3CC;">
                            <p style="margin:0; font-size:12px; color:#6B7280;">
                                &copy; {{ date('Y') }} Lentera Pustaka. Email ini dikirim secara otomatis.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

