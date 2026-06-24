<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Formulir Registrasi</h2>
        <p class="text-sm text-gray-600">Lengkapi data Anda untuk mendapatkan QR Code masuk.</p>
    </div>

    <form method="POST" action="{{ route('register.complete') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $attendee->invitation_token }}">

        <!-- Email (Read Only) -->
        <div>
            <label for="email" class="block font-medium text-sm text-gray-700">Email (Terdaftar)</label>
            <input id="email" class="block mt-1 w-full border-gray-300 bg-gray-100 rounded-md shadow-sm"
                type="email" name="email" value="{{ $attendee->email }}" readonly />
        </div>

        <!-- Nama Lengkap -->
        <div class="mt-4">
            <label for="name" class="block font-medium text-sm text-gray-700">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input id="name"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                type="text" name="name" value="{{ old('name') }}" required autofocus
                placeholder="Masukkan nama lengkap Anda" />
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Nomor WhatsApp -->
        <div class="mt-4">
            <label for="phone" class="block font-medium text-sm text-gray-700">
                Nomor WhatsApp <span class="text-red-500">*</span>
            </label>
            <input id="phone"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                type="text" name="phone"
                value="{{ old('phone', $attendee->invite_phone ? '0' . substr($attendee->invite_phone, 2) : '') }}"
                required placeholder="Contoh: 081234567890" />
            <p class="text-xs text-gray-500 mt-1">Tiket akan dikirim ke nomor WhatsApp ini.</p>
            @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Instansi/Perusahaan (Dropdown) -->
        <div class="mt-4">
            <label for="company_id" class="block font-medium text-sm text-gray-700">
                Instansi / Perusahaan <span class="text-red-500">*</span>
            </label>
            <select id="company_id" name="company_id" required
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Pilih Perusahaan --</option>
                @foreach($companies as $comp)
                    <option value="{{ $comp->id }}" data-name="{{ $comp->name }}" {{ old('company_id') == $comp->id ? 'selected' : '' }}>
                        {{ $comp->name }}
                    </option>
                @endforeach
            </select>
            @error('company_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Departemen (Dropdown - Bersyarat, hanya muncul untuk Balisuperhost) -->
        <div id="department-container" class="mt-4" style="display: none;">
            <label for="department_id" class="block font-medium text-sm text-gray-700">
                Departemen <span class="text-red-500">*</span>
            </label>
            <select id="department_id" name="department_id"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Pilih Departemen --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
            @if($departments->isEmpty())
                <p class="text-xs text-red-500 mt-1">Belum ada departemen tersedia. Hubungi admin.</p>
            @endif
            @error('department_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const companySelect = document.getElementById('company_id');
                const deptContainer = document.getElementById('department-container');
                const deptSelect = document.getElementById('department_id');

                function toggleDepartment() {
                    const selectedOption = companySelect.options[companySelect.selectedIndex];
                    const companyName = selectedOption ? selectedOption.getAttribute('data-name') : '';

                    if (companyName && companyName.toLowerCase() === 'balisuperhost') {
                        deptContainer.style.display = 'block';
                        deptSelect.setAttribute('required', 'required');
                    } else {
                        deptContainer.style.display = 'none';
                        deptSelect.removeAttribute('required');
                        deptSelect.value = ''; // Reset selection
                    }
                }

                companySelect.addEventListener('change', toggleDepartment);
                toggleDepartment();
            });
        </script>

        <div class="flex items-center justify-end mt-6">
            <button type="submit"
                class="w-full justify-center inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Selesaikan Pendaftaran
            </button>
        </div>
    </form>
</x-guest-layout>
