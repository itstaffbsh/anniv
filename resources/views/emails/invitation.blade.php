<!DOCTYPE html>
<html>
<head>
    <title>Undangan Registrasi - Absen BSH Anniv</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Halo!</h2>
    <p>Anda diundang untuk menghadiri event BSH Anniv. Silakan lengkapi data registrasi Anda dengan mengklik tautan di bawah ini:</p>
    
    <p style="margin: 20px 0;">
        <a href="{{ route('register.form', $attendee->invitation_token) }}" style="background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Lengkapi Pendaftaran
        </a>
    </p>

    <p>Jika tombol di atas tidak berfungsi, Anda bisa menyalin dan menempel URL berikut ke browser Anda:</p>
    <p>{{ route('register.form', $attendee->invitation_token) }}</p>

    <p>Terima kasih,</p>
    <p>Panitia BSH Anniv</p>
</body>
</html>
