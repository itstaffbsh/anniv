<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('QR Scanner') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    <h3 class="text-lg font-bold mb-4">Arahkan Kamera ke QR Code Peserta</h3>
                    
                    <div id="reader" class="mx-auto border-2 border-dashed border-gray-300 rounded-lg overflow-hidden" style="max-width: 500px;"></div>
                    
                    <div id="scan-result" class="mt-6 p-4 rounded-md hidden"></div>

                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include html5-qrcode -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let lastScannedCode = null;
            let lastScanTime = 0;

            function onScanSuccess(decodedText, decodedResult) {
                const now = Date.now();
                // Prevent duplicate scans within 3 seconds
                if (decodedText === lastScannedCode && (now - lastScanTime) < 3000) {
                    return;
                }
                
                lastScannedCode = decodedText;
                lastScanTime = now;

                // Call API
                fetch('{{ route('scanner.process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ token: decodedText })
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('scan-result');
                    resultDiv.classList.remove('hidden', 'bg-green-100', 'bg-red-100', 'bg-yellow-100', 'text-green-800', 'text-red-800', 'text-yellow-800');
                    
                    if (data.success) {
                        resultDiv.classList.add('bg-green-100', 'text-green-800');
                        resultDiv.innerHTML = `
                            <h4 class="font-bold text-lg">Check-in Berhasil!</h4>
                            <p class="mt-1">Nama: <strong>${data.attendee.name}</strong></p>
                            <p>Instansi: ${data.attendee.company || '-'}</p>
                            <p>ID: ${data.attendee.attendee_id}</p>
                        `;
                        // Play success sound (optional)
                    } else if (data.already_checked_in) {
                        resultDiv.classList.add('bg-yellow-100', 'text-yellow-800');
                        resultDiv.innerHTML = `
                            <h4 class="font-bold text-lg">Sudah Check-in!</h4>
                            <p class="mt-1">Nama: <strong>${data.attendee.name}</strong></p>
                            <p>${data.message}</p>
                        `;
                    } else {
                        resultDiv.classList.add('bg-red-100', 'text-red-800');
                        resultDiv.innerHTML = `
                            <h4 class="font-bold text-lg">QR Tidak Valid!</h4>
                            <p class="mt-1">${data.message}</p>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            function onScanFailure(error) {
                // handle scan failure, usually better to ignore and keep scanning
            }

            let html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { fps: 10, qrbox: {width: 250, height: 250} },
                /* verbose= */ false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        });
    </script>
</x-app-layout>
