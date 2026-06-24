<!DOCTYPE html>
<html>
<head>
    <title>Tiket Anda - BSH Anniv</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Pendaftaran Berhasil!</h2>
    <p>Halo {{ $attendee->name }}, pendaftaran Anda untuk event BSH Anniv telah berhasil dikonfirmasi.</p>
    
    <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
        <h3 style="margin-top: 0; color: #4F46E5;">ID Peserta: {{ $attendee->attendee_id }}</h3>
        <p>Silakan tunjukkan email ini atau klik tautan di bawah ini untuk melihat QR Code Anda saat check-in di lokasi acara.</p>
        
        <p style="margin: 20px 0;">
            <a href="{{ route('register.success', $attendee->attendee_id) }}" style="background-color: #10B981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Lihat QR Code
            </a>
        </p>
    </div>

    <p>Terima kasih,</p>
    <p>Panitia BSH Anniv</p>
</body>
</html>
