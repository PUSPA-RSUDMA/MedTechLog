<x-app-layout>
    <x-slot name="title">Master PIC</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Master Data: Penanggung Jawab (PIC)</h1>
            <p class="text-xs text-slate-500">Daftar PIC beserta Unit dan Nomor WhatsApp untuk pilihan tugas dan reminder otomatis</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ editing: null }">
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm h-fit">
            <h2 class="text-sm font-bold text-slate-900 mb-4">Tambah PIC Baru</h2>
            <form method="POST" action="{{ route('master.pics.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nama PIC *</label>
                    <input type="text" name="name" required placeholder="Andi Pratama" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Unit / Divisi *</label>
                    <select name="unit_id" required class="w-full rounded-lg border-slate-300 text-sm">
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">No. WhatsApp (Opsional, untuk Reminder)</label>
                    <input type="text" name="phone" placeholder="081234567890" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-teal-700">
                    <span>Aktif</span>
                </label>
                <button type="submit" class="w-full py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold">Simpan PIC</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama PIC</th>
                            <th class="px-4 py-3 text-left">Unit</th>
                            <th class="px-4 py-3 text-left">No. WA</th>
                            <th class="px-4 py-3 text-center">Tugas</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pics as $pic)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $pic->name }}</td>
                                <td class="px-4 py-3 text-xs text-slate-700">{{ $pic->unit?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs font-mono text-slate-600">{{ $pic->phone ?: '-' }}</td>
                                <td class="px-4 py-3 text-center text-xs font-semibold">{{ $pic->tasks_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs rounded-full border {{ $pic->is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $pic->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                    <button type="button" @click="editing = {{ $pic->toJson() }}" class="text-xs font-medium text-sky-700 hover:underline">Ubah</button>
                                    <form method="POST" action="{{ route('master.pics.destroy', $pic) }}" onsubmit="return confirm('Hapus / nonaktifkan PIC ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada data Master PIC.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pics->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $pics->links() }}</div>
            @endif
        </div>

        {{-- Edit Modal --}}
        <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl" @click.outside="editing = null">
                <h3 class="text-base font-bold text-slate-900 mb-4">Ubah Data PIC</h3>
                <form :action="'{{ url('master/pics') }}/' + (editing ? editing.id : '')" method="POST" class="space-y-3.5">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Nama PIC *</label>
                        <input type="text" name="name" :value="editing?.name" required class="w-full rounded-lg border-slate-300 text-sm">
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
                        <label class="block text-xs font-medium text-slate-700 mb-1">No. WhatsApp</label>
                        <input type="text" name="phone" :value="editing?.phone" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" name="is_active" value="1" :checked="editing?.is_active" class="rounded border-slate-300 text-teal-700">
                        <span>Aktif</span>
                    </label>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="editing = null" class="px-4 py-2 rounded-lg border border-slate-300 text-xs">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-teal-700 text-white text-xs font-semibold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
