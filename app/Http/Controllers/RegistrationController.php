<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\TicketEmail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegistrationController extends Controller
{
    public function showForm($token)
    {
        $attendee    = Attendee::where('invitation_token', $token)->firstOrFail();
        $departments = Department::orderBy('name')->get();
        $companies   = Company::orderBy('name')->get();

        if ($attendee->status === 'registered') {
            return redirect()->route('register.success', $attendee->attendee_id);
        }

        return view('registration.form', compact('attendee', 'departments', 'companies'));
    }

    public function complete(Request $request)
    {
        $request->validate([
            'token'         => 'required|exists:attendees,invitation_token',
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'company_id'    => 'required|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
        ], [
            'name.required'          => 'Nama lengkap wajib diisi.',
            'phone.required'         => 'Nomor WhatsApp wajib diisi.',
            'company_id.required'    => 'Instansi/perusahaan wajib dipilih.',
            'company_id.exists'      => 'Instansi/perusahaan tidak valid.',
            'department_id.exists'   => 'Departemen tidak valid.',
        ]);

        $company = Company::findOrFail($request->company_id);
        $isBalisuperhost = strtolower($company->name) === 'balisuperhost';

        if ($isBalisuperhost) {
            $request->validate([
                'department_id' => 'required|exists:departments,id'
            ], [
                'department_id.required' => 'Departemen wajib dipilih untuk peserta dari Balisuperhost.'
            ]);
        }

        $attendee = Attendee::where('invitation_token', $request->token)->firstOrFail();

        // Generate unique attendee ID and QR token
        $attendee_id = 'BSH-EVENT-' . str_pad($attendee->id, 4, '0', STR_PAD_LEFT);
        $qr_token    = Str::random(40);

        $attendee->update([
            'name'          => $request->name,
            'phone'         => $this->formatPhone($request->phone),
            'company'       => $company->name, // Keep string for fallback
            'company_id'    => $company->id,
            'department_id' => $isBalisuperhost ? $request->department_id : null,
            'attendee_id'   => $attendee_id,
            'qr_token'      => $qr_token,
            'status'        => 'registered',
        ]);

        // Kirim Email tiket
        Mail::to($attendee->email)->send(new TicketEmail($attendee));

        // Kirim WhatsApp tiket
        $this->sendTicketWhatsApp($attendee->phone, $attendee->name, $attendee_id, $qr_token);

        return redirect()->route('register.success', $attendee_id);
    }

    public function success($attendee_id)
    {
        $attendee = Attendee::with('department')->where('attendee_id', $attendee_id)->firstOrFail();
        $qr_url   = route('scanner.process') . '?token=' . $attendee->qr_token;
        $qrcode   = QrCode::size(250)->generate($attendee->qr_token);

        return view('registration.success', compact('attendee', 'qrcode'));
    }

    // ─── Helper Methods ──────────────────────────────────────────────

    private function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    private function sendTicketWhatsApp($phone, $name, $attendee_id, $qr_token)
    {
        $token = env('FONNTE_TOKEN');
        if (empty($token) || $token === 'YOUR_FONNTE_TOKEN_HERE') {
            return;
        }

        $successLink = route('register.success', $attendee_id);
        $message  = "Halo *$name*! 🎉\n\n";
        $message .= "Pendaftaran Anda untuk *BSH Anniversary* telah berhasil!\n\n";
        $message .= "🪪 *ID Peserta:* $attendee_id\n";
        $message .= "🔗 *Tiket & QR Code Anda:*\n$successLink\n\n";
        $message .= "Tunjukkan QR Code saat check-in di lokasi acara.\n";
        $message .= "_Sampai jumpa di acara!_ 🎊";

        try {
            Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target'  => $phone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            // Abaikan error
        }
    }
}
