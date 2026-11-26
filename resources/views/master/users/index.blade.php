<x-app-layout>
    <x-slot name="title">Manajemen Pengguna</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Master Data: Manajemen Pengguna</h1>
            <p class="text-xs text-slate-500">Kelola akun tim internal, peran (RBAC), unit kerja, dan struktur atasan</p>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ showCreate: false, editing: null }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('master.users.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / NIP..." class="rounded-lg border-slate-300 text-sm">
                <select name="role" class="rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Peran</option>
                    @foreach($roles as $val => $label)
                        <option value="{{ $val }}" @selected(request('role') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-slate-800 text-white text-sm font-medium">Filter</button>
            </form>
            <button type="button" @click="showCreate = true" class="px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold">
                + Tambah Pengguna
            </button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Pengguna</th>
                            <th class="px-4 py-3 text-left">Peran</th>
                            <th class="px-4 py-3 text-left">Unit & Jabatan</th>
                            <th class="px-4 py-3 text-left">Atasan</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $u->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $u->email }} @if($u->employee_id) &bull; <span class="font-mono">{{ $u->employee_id }}</span> @endif</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full border {{ $u->role->color() }}">{{ $u->role->label() }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <p class="font-medium text-slate-800">{{ $u->unit?->name ?? '-' }}</p>
                                    <p class="text-slate-400">{{ $u->position ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-700">{{ $u->supervisor?->name ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 text-xs rounded-full border {{ $u->is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200' }}">
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                    <button type="button" @click="editing = {{ json_encode([
                                        'id' => $u->id,
                                        'employee_id' => $u->employee_id,
                                        'name' => $u->name,
                                        'email' => $u->email,
                                        'role' => $u->role->value,
                                        'unit_id' => $u->unit_id,
                                        'supervisor_id' => $u->supervisor_id,
                                        'position' => $u->position,
                                        'is_active' => $u->is_active,
                                    ]) }}" class="text-xs font-medium text-sky-700 hover:underline">Ubah</button>
                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('master.users.destroy', $u) }}" onsubmit="return confirm('Hapus / nonaktifkan pengguna ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $users->links() }}</div>
            @endif
        </div>

        {{-- Create Modal --}}
        <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.outside="showCreate = false">
                <h3 class="text-base font-bold text-slate-900 mb-4">Tambah Pengguna Baru</h3>
                <form method="POST" action="{{ route('master.users.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">NIP / ID Pegawai</label>
                        <input type="text" name="employee_id" placeholder="MTL-0401" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Email *</label>
                        <input type="email" name="email" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Kata Sandi *</label>
                        <input type="password" name="password" required minlength="6" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Peran (Role) *</label>
                        <select name="role" required class="w-full rounded-lg border-slate-300 text-sm">
                            @foreach($roles as $val => $label)
                                <option value="{{ $val }}" @selected($val === 'staff')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Unit / Divisi</label>
                        <select name="unit_id" class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Atasan / Supervisor</label>
                        <select name="supervisor_id" class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="">-- Tidak Ada --</option>
                            @foreach($supervisors as $spv)
                                <option value="{{ $spv->id }}">{{ $spv->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Jabatan</label>
                        <input type="text" name="position" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-2 pt-3">
                        <button type="button" @click="showCreate = false" class="px-4 py-2 rounded-lg border border-slate-300 text-xs">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-teal-700 text-white text-xs font-semibold">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.outside="editing = null">
                <h3 class="text-base font-bold text-slate-900 mb-4">Ubah Data Pengguna</h3>
                <form :action="'{{ url('master/users') }}/' + (editing ? editing.id : '')" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">NIP / ID Pegawai</label>
                        <input type="text" name="employee_id" :value="editing?.employee_id" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" :value="editing?.name" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Email *</label>
                        <input type="email" name="email" :value="editing?.email" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Kata Sandi Baru (Kosongkan jika tetap)</label>
                        <input type="password" name="password" minlength="6" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Peran *</label>
                        <select name="role" :value="editing?.role" required class="w-full rounded-lg border-slate-300 text-sm">
                            @foreach($roles as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Unit</label>
                        <select name="unit_id" :value="editing?.unit_id ?? ''" class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Atasan</label>
                        <select name="supervisor_id" :value="editing?.supervisor_id ?? ''" class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="">-- Tidak Ada --</option>
                            @foreach($supervisors as $spv)
                                <option value="{{ $spv->id }}">{{ $spv->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Jabatan</label>
                        <input type="text" name="position" :value="editing?.position" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                            <input type="checkbox" name="is_active" value="1" :checked="editing?.is_active" class="rounded border-slate-300 text-teal-700">
                            <span>Akun Aktif</span>
                        </label>
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-2 pt-3">
                        <button type="button" @click="editing = null" class="px-4 py-2 rounded-lg border border-slate-300 text-xs">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-teal-700 text-white text-xs font-semibold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
