<x-guest-layout>
    <div class="text-center">
        <div class="mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h2 class="mt-3 text-2xl font-bold text-gray-900">Pendaftaran Berhasil!</h2>
            <p class="text-sm text-gray-600 mt-2">Halo {{ $attendee->name }}, berikut adalah tiket QR Code Anda.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 inline-block mb-6">
            <div class="mb-4">
                {!! $qrcode !!}
            </div>
            <p class="font-mono font-bold text-lg text-gray-800">{{ $attendee->attendee_id }}</p>
        </div>

        <div class="text-sm text-gray-500 text-left bg-blue-50 p-4 rounded-md">
            <p><strong>Penting:</strong></p>
            <ul class="list-disc pl-5 mt-1">
                <li>Simpan halaman ini (Screenshot) atau cek Email / WhatsApp Anda untuk melihat tiket ini.</li>
                <li>Tunjukkan QR Code ini kepada panitia saat check-in di lokasi acara.</li>
            </ul>
        </div>
    </div>
</x-guest-layout>
