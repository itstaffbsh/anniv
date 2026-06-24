<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\InvitationEmail;

class AdminController extends Controller
{
    public function index()
    {
        $attendees = Attendee::with(['department', 'company'])->latest()->get();
        $departments = Department::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $totalRegistered = Attendee::where('status', 'registered')->count();
        $totalCheckedIn = Attendee::where('checkin_status', 'checked_in')->count();

        return view('dashboard', compact('attendees', 'departments', 'companies', 'totalRegistered', 'totalCheckedIn'));
    }

    public function invite(Request $request)
    {
        $request->validate([
            'email'        => 'required|email|unique:attendees,email',
            'invite_phone' => 'required|string|max:20',
        ], [
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email ini sudah pernah diundang.',
            'invite_phone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $token = Str::random(32);

        $attendee = new Attendee();
        $attendee->email        = $request->email;
        $attendee->invite_phone = $this->formatPhone($request->invite_phone);
        $attendee->invitation_token = $token;
        $attendee->status       = 'invited';
        $attendee->save();

        // Kirim Email undangan
        Mail::to($attendee->email)->send(new InvitationEmail($attendee));

        // Kirim WhatsApp undangan
        $this->sendInviteWhatsApp($attendee->invite_phone, $attendee->email, $token);

        return redirect()->back()->with('success', 'Undangan berhasil dikirim ke ' . $attendee->email . ' dan WhatsApp ' . $attendee->invite_phone);
    }

    public function destroy($id)
    {
        $attendee = Attendee::findOrFail($id);
        $attendee->delete();

        return redirect()->back()->with('success', 'Peserta berhasil dihapus.');
    }

    // ─── Manajemen Departemen ────────────────────────────────────────

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
        ], [
            'name.required' => 'Nama departemen wajib diisi.',
            'name.unique'   => 'Departemen ini sudah ada.',
        ]);

        Department::create(['name' => $request->name]);

        return redirect()->back()->with('success', 'Departemen "' . $request->name . '" berhasil ditambahkan.');
    }

    public function destroyDepartment($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return redirect()->back()->with('success', 'Departemen berhasil dihapus.');
    }

    // ─── Manajemen Instansi/Perusahaan ────────────────────────────────
    
    public function storeCompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:companies,name',
        ], [
            'name.required' => 'Nama instansi/perusahaan wajib diisi.',
            'name.unique'   => 'Instansi/perusahaan ini sudah ada.',
        ]);

        Company::create(['name' => $request->name]);

        return redirect()->back()->with('success', 'Instansi/perusahaan "' . $request->name . '" berhasil ditambahkan.');
    }

    public function destroyCompany($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return redirect()->back()->with('success', 'Instansi/perusahaan berhasil dihapus.');
    }

    // ─── Helper Methods ──────────────────────────────────────────────

    private function formatPhone($phone)
    {
        // Bersihkan karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // Ubah 08xx → 628xx
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    private function sendInviteWhatsApp($phone, $email, $token)
    {
        $fonnteToken = env('FONNTE_TOKEN');
        if (empty($fonnteToken) || $fonnteToken === 'YOUR_FONNTE_TOKEN_HERE') {
            return;
        }

        $link = route('register.form', $token);
        $message  = "Halo! 👋\n\n";
        $message .= "Anda mendapatkan undangan untuk acara *BSH Anniversary*.\n\n";
        $message .= "Silakan klik link berikut untuk melengkapi data pendaftaran Anda:\n";
        $message .= $link . "\n\n";
        $message .= "_Jika Anda merasa tidak mengharapkan undangan ini, abaikan pesan ini._";

        try {
            Http::withHeaders([
                'Authorization' => $fonnteToken,
            ])->post('https://api.fonnte.com/send', [
                'target'  => $phone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            // Abaikan error pengiriman WA
        }
    }
}
