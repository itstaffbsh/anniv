<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Formulir Registrasi</h2>
        <p class="text-sm text-gray-600">Lengkapi data Anda untuk mendapatkan QR Code masuk.</p>
    </div>

    <form method="POST" action="{{ route('register.complete') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $attendee ? $attendee->invitation_token : '' }}">

        <!-- Email -->
        <div>
            <label for="email" class="block font-medium text-sm text-gray-700">
                Email <span class="text-red-500">*</span>
            </label>
            <input id="email" 
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm {{ $attendee ? 'bg-gray-100' : '' }}"
                type="email" 
                name="email" 
                value="{{ old('email', $attendee ? $attendee->email : '') }}" 
                {{ $attendee ? 'readonly' : 'required' }} 
                placeholder="Masukkan alamat email Anda" />
            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                value="{{ old('phone', ($attendee && $attendee->invite_phone) ? '0' . substr($attendee->invite_phone, 2) : '') }}"
                required placeholder="Contoh: 081234567890" />
            <p class="text-xs text-gray-500 mt-1">Tiket akan dikirim ke nomor WhatsApp ini.</p>
            @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Instansi / Perusahaan (Dropdown Hardcoded) -->
        <div class="mt-4">
            <label for="company_type" class="block font-medium text-sm text-gray-700">
                Instansi / Perusahaan <span class="text-red-500">*</span>
            </label>
            <select id="company_type" name="company_type" required
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Pilih Instansi / Perusahaan --</option>
                <option value="Balisuperhost" {{ old('company_type') == 'Balisuperhost' ? 'selected' : '' }}>Balisuperhost</option>
                <option value="Vendor" {{ old('company_type') == 'Vendor' ? 'selected' : '' }}>Vendor</option>
                <option value="Others" {{ old('company_type') == 'Others' ? 'selected' : '' }}>Others</option>
            </select>
            @error('company_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Board untuk mengetik dari mana dia (Muncul jika pilih Others) -->
        <div id="company-other-container" class="mt-4" style="display: none;">
            <label for="company_other" class="block font-medium text-sm text-gray-700">
                Nama Instansi / Perusahaan <span class="text-red-500">*</span>
            </label>
            <input id="company_other" name="company_other" type="text"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                value="{{ old('company_other') }}" placeholder="Ketik nama instansi/perusahaan Anda" />
            @error('company_other') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                const companyTypeSelect = document.getElementById('company_type');
                const companyOtherContainer = document.getElementById('company-other-container');
                const companyOtherInput = document.getElementById('company_other');
                const deptContainer = document.getElementById('department-container');
                const deptSelect = document.getElementById('department_id');

                function handleCompanyChange() {
                    const val = companyTypeSelect.value;

                    if (val === 'Balisuperhost') {
                        deptContainer.style.display = 'block';
                        deptSelect.setAttribute('required', 'required');
                        
                        companyOtherContainer.style.display = 'none';
                        companyOtherInput.removeAttribute('required');
                        companyOtherInput.value = '';
                    } else if (val === 'Others') {
                        companyOtherContainer.style.display = 'block';
                        companyOtherInput.setAttribute('required', 'required');

                        deptContainer.style.display = 'none';
                        deptSelect.removeAttribute('required');
                        deptSelect.value = '';
                    } else {
                        deptContainer.style.display = 'none';
                        deptSelect.removeAttribute('required');
                        deptSelect.value = '';

                        companyOtherContainer.style.display = 'none';
                        companyOtherInput.removeAttribute('required');
                        companyOtherInput.value = '';
                    }
                }

                companyTypeSelect.addEventListener('change', handleCompanyChange);
                handleCompanyChange();
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
