<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index()
    {
        return view('scanner.index');
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $attendee = Attendee::where('qr_token', $request->token)->first();

        if (!$attendee) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau tidak ditemukan.'
            ]);
        }

        if ($attendee->checkin_status === 'checked_in') {
            return response()->json([
                'success' => false,
                'already_checked_in' => true,
                'attendee' => $attendee,
                'message' => 'Peserta sudah check-in pada ' . \Carbon\Carbon::parse($attendee->checkin_time)->format('d-m-Y H:i')
            ]);
        }

        // Mark as checked in
        $attendee->update([
            'checkin_status' => 'checked_in',
            'checkin_time' => now(),
            'checked_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'attendee' => $attendee,
            'message' => 'Check-in berhasil untuk ' . $attendee->name
        ]);
    }
}
