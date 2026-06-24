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
    public function showForm($token = null)
    {
        $attendee = null;
        if ($token) {
            $attendee = Attendee::where('invitation_token', $token)->firstOrFail();
            if ($attendee->status === 'registered') {
                return redirect()->route('register.success', $attendee->attendee_id);
            }
        }

        $departments = Department::orderBy('name')->get();

        return view('registration.form', compact('attendee', 'departments'));
    }

    public function complete(Request $request)
    {
        $rules = [
            'token'         => 'nullable|exists:attendees,invitation_token',
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'company_type'  => 'required|in:Balisuperhost,Vendor,Others',
        ];

        // Email is required if not coming from invite token
        if ($request->token) {
            $rules['email'] = 'nullable|email';
        } else {
            $rules['email'] = 'required|email|max:255';
        }

        // Conditional validation for company types
        if ($request->company_type === 'Balisuperhost') {
            $rules['department_id'] = 'required|exists:departments,id';
        } elseif ($request->company_type === 'Others') {
            $rules['company_other'] = 'required|string|max:255';
        }

        $messages = [
            'email.required'         => 'Email wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
            'name.required'          => 'Nama lengkap wajib diisi.',
            'phone.required'         => 'Nomor WhatsApp wajib diisi.',
            'company_type.required'  => 'Instansi / perusahaan wajib dipilih.',
            'company_type.in'        => 'Pilihan instansi tidak valid.',
            'department_id.required' => 'Departemen wajib dipilih untuk peserta dari Balisuperhost.',
            'department_id.exists'   => 'Departemen tidak valid.',
            'company_other.required' => 'Nama instansi wajib diisi jika memilih Others.',
        ];

        $request->validate($rules, $messages);

        // Resolve attendee
        if ($request->token) {
            $attendee = Attendee::where('invitation_token', $request->token)->firstOrFail();
            if ($attendee->status === 'registered') {
                return redirect()->route('register.success', $attendee->attendee_id);
            }
        } else {
            $email = $request->email;
            $existing = Attendee::where('email', $email)->first();

            if ($existing) {
                if ($existing->status === 'registered') {
                    return back()->withErrors(['email' => 'Email ini sudah terdaftar sebagai peserta.'])->withInput();
                }
                $attendee = $existing;
            } else {
                $attendee = new Attendee();
                $attendee->email = $email;
                $attendee->status = 'invited';
            }
        }

        // Process company & department
        $company_id = null;
        $company_name = '';
        $department_id = null;

        if ($request->company_type === 'Balisuperhost') {
            $company = Company::firstOrCreate(['name' => 'Balisuperhost']);
            $company_id = $company->id;
            $company_name = 'Balisuperhost';
            $department_id = $request->department_id;
        } elseif ($request->company_type === 'Vendor') {
            $company = Company::firstOrCreate(['name' => 'Vendor']);
            $company_id = $company->id;
            $company_name = 'Vendor';
        } else {
            $company_name = $request->company_other;
        }

        // Save new attendee first to get DB ID
        if (!$attendee->exists) {
            $attendee->save();
        }

        // Generate unique attendee ID and QR token (shorten to 20 for better scan speed)
        $attendee_id = 'BSH-EVENT-' . str_pad($attendee->id, 4, '0', STR_PAD_LEFT);
        $qr_token    = $attendee->qr_token ?: Str::random(20);

        $attendee->update([
            'name'          => $request->name,
            'phone'         => $this->formatPhone($request->phone),
            'company'       => $company_name,
            'company_id'    => $company_id,
            'department_id' => $department_id,
            'attendee_id'   => $attendee_id,
            'qr_token'      => $qr_token,
            'status'        => 'registered',
        ]);

        // Send Email ticket
        try {
            Mail::to($attendee->email)->send(new TicketEmail($attendee));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email tiket: ' . $e->getMessage());
        }

        // Send WhatsApp ticket
        try {
            $this->sendTicketWhatsApp($attendee->phone, $attendee->name, $attendee_id, $qr_token);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim WhatsApp tiket: ' . $e->getMessage());
        }

        return redirect()->route('register.success', $attendee_id);
    }

    public function success($attendee_id)
    {
        $attendee = Attendee::with('department')->where('attendee_id', $attendee_id)->firstOrFail();
        $qr_url   = route('scanner.process') . '?token=' . $attendee->qr_token;
        
        // Generate QR code with solid white background, black foreground, size 300, and margin 3
        $qrcode   = QrCode::size(300)
            ->backgroundColor(255, 255, 255)
            ->color(0, 0, 0)
            ->margin(3)
            ->generate($attendee->qr_token);

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
