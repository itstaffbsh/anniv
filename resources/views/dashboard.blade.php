<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium">Total Diundang</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $attendees->count() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium">Total Terdaftar</div>
                    <div class="text-3xl font-bold text-indigo-600">{{ $totalRegistered }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium">Total Checked-In</div>
                    <div class="text-3xl font-bold text-green-600">{{ $totalCheckedIn }}</div>
                </div>
            </div>

            <!-- Alert -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Invite Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Undang Peserta Baru</h3>

                    <form action="{{ route('admin.invite') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" id="email" required
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                    placeholder="peserta@email.com"
                                    value="{{ old('email') }}">
                            </div>
                            <!-- WhatsApp -->
                            <div>
                                <label for="invite_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nomor WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="invite_phone" id="invite_phone" required
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                    placeholder="081234567890"
                                    value="{{ old('invite_phone') }}">
                            </div>
                            <!-- Tombol -->
                            <div>
                                <button type="submit"
                                    class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition font-medium">
                                    📨 Kirim Undangan
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Undangan akan dikirim melalui <strong>Email</strong> dan <strong>WhatsApp</strong> secara bersamaan.</p>
                    </form>
                </div>
            </div>

            <!-- Manajemen Departemen -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Manajemen Departemen</h3>

                    <div class="flex flex-wrap gap-6">
                        <!-- Form Tambah Departemen -->
                        <div class="flex-1 min-w-[240px]">
                            <form action="{{ route('admin.departments.store') }}" method="POST" class="flex gap-2 items-end">
                                @csrf
                                <div class="flex-1">
                                    <label for="dept_name" class="block text-sm font-medium text-gray-700 mb-1">Tambah Departemen Baru</label>
                                    <input type="text" name="name" id="dept_name" required
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                        placeholder="Nama departemen">
                                </div>
                                <button type="submit"
                                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition whitespace-nowrap">
                                    + Tambah
                                </button>
                            </form>
                        </div>

                        <!-- Daftar Departemen -->
                        <div class="flex-1 min-w-[240px]">
                            @if($departments->isEmpty())
                                <p class="text-sm text-gray-400 italic">Belum ada departemen. Tambahkan departemen terlebih dahulu.</p>
                            @else
                                <p class="text-sm font-medium text-gray-700 mb-2">Daftar Departemen ({{ $departments->count() }}):</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($departments as $dept)
                                    <div class="flex items-center gap-1 bg-indigo-50 border border-indigo-200 rounded-full px-3 py-1">
                                        <span class="text-sm text-indigo-800 font-medium">{{ $dept->name }}</span>
                                        <form action="{{ route('admin.departments.destroy', $dept->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus departemen {{ $dept->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-700 ml-1 text-xs font-bold leading-none" title="Hapus">✕</button>
                                        </form>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manajemen Instansi/Perusahaan -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Manajemen Instansi/Perusahaan</h3>

                    <div class="flex flex-wrap gap-6">
                        <!-- Form Tambah Perusahaan -->
                        <div class="flex-1 min-w-[240px]">
                            <form action="{{ route('admin.companies.store') }}" method="POST" class="flex gap-2 items-end">
                                @csrf
                                <div class="flex-1">
                                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Tambah Instansi Baru</label>
                                    <input type="text" name="name" id="company_name" required
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                        placeholder="Nama instansi/perusahaan">
                                </div>
                                <button type="submit"
                                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition whitespace-nowrap">
                                    + Tambah
                                </button>
                            </form>
                        </div>

                        <!-- Daftar Perusahaan -->
                        <div class="flex-1 min-w-[240px]">
                            @if($companies->isEmpty())
                                <p class="text-sm text-gray-400 italic">Belum ada instansi. Tambahkan instansi terlebih dahulu.</p>
                            @else
                                <p class="text-sm font-medium text-gray-700 mb-2">Daftar Instansi ({{ $companies->count() }}):</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($companies as $comp)
                                    <div class="flex items-center gap-1 bg-indigo-50 border border-indigo-200 rounded-full px-3 py-1">
                                        <span class="text-sm text-indigo-800 font-medium">{{ $comp->name }}</span>
                                        <form action="{{ route('admin.companies.destroy', $comp->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus instansi {{ $comp->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-700 ml-1 text-xs font-bold leading-none" title="Hapus">✕</button>
                                        </form>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scanner Link -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium">QR Code Scanner</h3>
                        <p class="text-sm text-gray-500">Buka scanner untuk check-in peserta.</p>
                    </div>
                    <a href="{{ route('scanner.index') }}" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition font-bold">
                        Buka Scanner
                    </a>
                </div>
            </div>

            <!-- Participant List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Daftar Peserta</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">WhatsApp</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perusahaan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departemen</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($attendees as $attendee)
                                <tr>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $attendee->name ?? '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $attendee->email }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $attendee->invite_phone ?? '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $attendee->company?->name ?? $attendee->company ?? '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        {{ $attendee->department?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($attendee->status == 'registered')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Terdaftar</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Diundang</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        @if($attendee->checkin_status == 'checked_in')
                                            <span class="text-green-600 font-bold">✓ {{ \Carbon\Carbon::parse($attendee->checkin_time)->format('H:i') }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <form action="{{ route('admin.attendees.destroy', $attendee->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus peserta {{ $attendee->email }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-4 text-center text-gray-500">Belum ada peserta.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
