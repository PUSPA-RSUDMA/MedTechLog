<x-app-layout>
    <x-slot name="title">Tugas Diklat</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Tugas Diklat</h1>
            <p class="text-xs text-slate-500">Pencatatan peserta diklat, unit, profesi, materi, dan dokumen pendukung</p>
        </div>
    </x-slot>

    <div x-data="{ editing: null, driveModal: false }">
        {{-- Google Drive Storage Status Banner --}}
        <div class="mb-5 p-4 rounded-xl border flex flex-wrap items-center justify-between gap-3 {{ $driveConfigured ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' }}">
            <div class="flex items-center gap-3 text-xs">
                <span class="px-2.5 py-1 rounded-full font-bold {{ $driveConfigured ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white' }}">
                    {{ $driveConfigured ? 'Google Drive Aktif' : 'Penyimpanan Lokal' }}
                </span>
                <div>
                    @if($driveConfigured)
                        <p class="font-semibold">File yang diunggah akan langsung dikirim ke Google Drive (tidak membebani penyimpanan server).</p>
                    @else
                        <p class="font-semibold">Google Drive belum diaktifkan — file sementara tersimpan di server lokal. Klik tombol di kanan untuk menghubungkan Google Drive.</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($driveConfigured)
                    <form method="POST" action="{{ route('diklat.drive-test') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-white border border-emerald-300 text-emerald-800 hover:bg-emerald-100 text-xs font-semibold">
                            Uji Koneksi Drive
                        </button>
                    </form>
                @endif
                <button type="button" @click="driveModal = true" class="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold">
                    Pengaturan Google Drive
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Create Form --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm h-fit">
            <h2 class="text-sm font-bold text-slate-900 mb-4">Tambah Tugas Diklat</h2>
            <form method="POST" action="{{ route('diklat.store') }}" enctype="multipart/form-data" class="space-y-3.5"
                  x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nama <span class="text-rose-500">*</span></label>
                    <input type="text" name="participant_name" value="{{ old('participant_name') }}" required placeholder="Nama peserta / pemateri"
                           class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Unit <span class="text-rose-500">*</span></label>
                    <select name="unit_id" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                        <option value="">-- Pilih Unit --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Profesi <span class="text-rose-500">*</span></label>
                    <select name="profession_id" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                        <option value="">-- Pilih Profesi --</option>
                        @foreach($professions as $p)
                            <option value="{{ $p->id }}" @selected(old('profession_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Materi <span class="text-rose-500">*</span></label>
                    <input type="text" name="material" value="{{ old('material') }}" required placeholder="Judul materi diklat / pelatihan"
                           class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal</label>
                    <input type="date" name="event_date" value="{{ old('event_date', now()->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Upload File (PDF/Office/Gambar, maks 10MB)</label>
                    <input type="file" name="file" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                </div>

                <button type="submit" :disabled="submitting"
                        class="w-full py-2.5 rounded-lg bg-teal-700 hover:bg-teal-800 disabled:opacity-60 text-white text-sm font-semibold flex items-center justify-center gap-2">
                    <svg x-show="submitting" x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Mengunggah ke Google Drive...' : 'Simpan Tugas Diklat'"></span>
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
                <form method="GET" action="{{ route('diklat.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / materi..." class="rounded-lg border-slate-300 text-sm">
                    <select name="unit_id" class="rounded-lg border-slate-300 text-sm">
                        <option value="">Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" @selected(request('unit_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <select name="profession_id" class="rounded-lg border-slate-300 text-sm">
                        <option value="">Semua Profesi</option>
                        @foreach($professions as $p)
                            <option value="{{ $p->id }}" @selected(request('profession_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-slate-800 text-white text-xs font-semibold">Filter</button>
                        <a href="{{ route('diklat.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-600">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Nama &amp; Profesi</th>
                                <th class="px-4 py-3 text-left">Unit</th>
                                <th class="px-4 py-3 text-left">Materi</th>
                                <th class="px-4 py-3 text-left">File</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($diklatTasks as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-900">{{ $item->participant_name }}</p>
                                        <p class="text-xs text-teal-700">{{ $item->profession?->name }}</p>
                                        @if($item->event_date)
                                            <p class="text-[11px] text-slate-400">{{ $item->event_date->format('d M Y') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-700">{{ $item->unit?->name }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-800 font-medium">{{ $item->material }}</td>
                                    <td class="px-4 py-3 text-xs">
                                        @if($item->file_url)
                                            <a href="{{ $item->file_url }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-medium {{ $item->is_google_drive ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-sky-50 text-sky-700 hover:bg-sky-100' }}"
                                               title="{{ $item->file_name }}">
                                                {{ $item->is_google_drive ? 'Buka Google Drive' : 'Unduh' }}
                                            </a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                        <button type="button" @click="editing = {{ json_encode([
                                            'id' => $item->id,
                                            'participant_name' => $item->participant_name,
                                            'unit_id' => $item->unit_id,
                                            'profession_id' => $item->profession_id,
                                            'material' => $item->material,
                                            'event_date' => $item->event_date?->format('Y-m-d'),
                                        ]) }}" class="text-xs font-medium text-sky-700 hover:underline">Ubah</button>
                                        <form method="POST" action="{{ route('diklat.destroy', $item) }}" onsubmit="return confirm('Hapus data Tugas Diklat ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada data Tugas Diklat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($diklatTasks->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100">{{ $diklatTasks->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl" @click.outside="editing = null">
                <h3 class="text-base font-bold text-slate-900 mb-4">Ubah Tugas Diklat</h3>
                <form :action="'{{ url('diklat') }}/' + (editing ? editing.id : '')" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Nama *</label>
                        <input type="text" name="participant_name" :value="editing?.participant_name" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Unit *</label>
                        <select name="unit_id" :value="editing?.unit_id" required class="w-full rounded-lg border-slate-300 text-sm">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Profesi *</label>
                        <select name="profession_id" :value="editing?.profession_id" required class="w-full rounded-lg border-slate-300 text-sm">
                            @foreach($professions as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Materi *</label>
                        <input type="text" name="material" :value="editing?.material" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal</label>
                        <input type="date" name="event_date" :value="editing?.event_date" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Ganti File (Opsional)</label>
                        <input type="file" name="file" class="w-full text-xs">
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="editing = null" class="px-4 py-2 rounded-lg border border-slate-300 text-xs">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-teal-700 text-white text-xs font-semibold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Google Drive Settings Modal --}}
        <div x-show="driveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto" @click.outside="driveModal = false">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Pengaturan Upload Google Drive</h3>
                        <p class="text-xs text-slate-500">Hubungkan akun Google Drive menggunakan OAuth2 (Google Cloud Console)</p>
                    </div>
                    <button type="button" @click="driveModal = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('diklat.drive-settings') }}" class="space-y-3.5">
                    @csrf
                    <label class="flex items-center gap-2.5 p-3 rounded-xl bg-teal-50 border border-teal-200 text-xs font-semibold text-teal-900 cursor-pointer">
                        <input type="checkbox" name="is_enabled" value="1" @checked($driveSetting->is_enabled) class="rounded border-teal-400 text-teal-700 w-4 h-4">
                        <span>Aktifkan Upload Langsung ke Google Drive</span>
                    </label>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Google Client ID</label>
                        <input type="text" name="client_id" value="{{ old('client_id', $driveSetting->client_id) }}" placeholder="xxxx.apps.googleusercontent.com" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Google Client Secret</label>
                        <input type="text" name="client_secret" value="{{ old('client_secret', $driveSetting->client_secret) }}" placeholder="GOCSPX-xxxx" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Google Refresh Token</label>
                        <textarea name="refresh_token" rows="2" placeholder="1//04xxxx..." class="w-full rounded-lg border-slate-300 text-xs">{{ old('refresh_token', $driveSetting->refresh_token) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Folder ID Tujuan di Google Drive (Opsional)</label>
                        <input type="text" name="folder_id" value="{{ old('folder_id', $driveSetting->folder_id) }}" placeholder="Ambil dari URL folder: drive.google.com/drive/folders/[ID_INI]" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>

                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1">
                        <p class="font-semibold text-slate-800">Cara singkat mendapatkan kredensial:</p>
                        <p>1. Buka <em>Google Cloud Console</em> &rarr; aktifkan <strong>Google Drive API</strong> &rarr; buat <em>OAuth Client ID (Web)</em>.</p>
                        <p>2. Gunakan <em>Google OAuth 2.0 Playground</em> (scope <code>https://www.googleapis.com/auth/drive.file</code>) untuk menukar kode menjadi <strong>Refresh Token</strong>.</p>
                        <p>3. Buat folder di Google Drive, salin <strong>Folder ID</strong> dari URL-nya, lalu simpan &amp; klik <strong>Uji Koneksi Drive</strong>.</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="driveModal = false" class="px-4 py-2 rounded-lg border border-slate-300 text-xs">Tutup</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</x-app-layout>
