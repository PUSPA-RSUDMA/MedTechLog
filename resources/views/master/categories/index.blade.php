<x-app-layout>
    <x-slot name="title">Master Kategori Pekerjaan</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Master Data: Kategori Pekerjaan</h1>
            <p class="text-xs text-slate-500">Klasifikasi jenis pekerjaan untuk analisis beban kerja dan varians</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ editing: null }">
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm h-fit">
            <h2 class="text-sm font-bold text-slate-900 mb-4">Tambah Kategori Baru</h2>
            <form method="POST" action="{{ route('master.categories.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Kode *</label>
                    <input type="text" name="code" required placeholder="DEV" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nama Kategori *</label>
                    <input type="text" name="name" required placeholder="Pengembangan SIMRS" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Warna Label (Hex) *</label>
                    <input type="color" name="color" value="#0F766E" class="h-9 w-20 rounded border-slate-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border-slate-300 text-sm"></textarea>
                </div>
                <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-teal-700">
                    <span>Aktif</span>
                </label>
                <button type="submit" class="w-full py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold">Simpan Kategori</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-center">Tugas</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($categories as $cat)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $cat->color }}"></span>
                                        <span class="font-semibold text-slate-900">{{ $cat->name }}</span>
                                        <span class="font-mono text-xs text-slate-400">({{ $cat->code }})</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $cat->description }}</p>
                                </td>
                                <td class="px-4 py-3 text-center text-xs font-semibold">{{ $cat->tasks_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs rounded-full border {{ $cat->is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                    <button type="button" @click="editing = {{ $cat->toJson() }}" class="text-xs font-medium text-sky-700 hover:underline">Ubah</button>
                                    <form method="POST" action="{{ route('master.categories.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl" @click.outside="editing = null">
                <h3 class="text-base font-bold text-slate-900 mb-4">Ubah Kategori Pekerjaan</h3>
                <form :action="'{{ url('master/categories') }}/' + (editing ? editing.id : '')" method="POST" class="space-y-3.5">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Kode *</label>
                        <input type="text" name="code" :value="editing?.code" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Nama *</label>
                        <input type="text" name="name" :value="editing?.name" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Warna</label>
                        <input type="color" name="color" :value="editing?.color" class="h-9 w-20 rounded border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-slate-300 text-sm" x-text="editing?.description"></textarea>
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
