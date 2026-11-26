<x-app-layout>
    <x-slot name="title">Master Periode Kerja</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Master Data: Periode Kerja</h1>
            <p class="text-xs text-slate-500">Atur rentang waktu evaluasi bulanan, triwulanan, atau tahunan</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ editing: null }">
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm h-fit">
            <h2 class="text-sm font-bold text-slate-900 mb-4">Tambah Periode Baru</h2>
            <form method="POST" action="{{ route('master.periods.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nama Periode *</label>
                    <input type="text" name="name" required placeholder="Desember 2026" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tipe Periode *</label>
                    <select name="type" required class="w-full rounded-lg border-slate-300 text-sm">
                        @foreach($types as $val => $label)
                            <option value="{{ $val }}" @selected($val === 'monthly')>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Mulai *</label>
                        <input type="date" name="start_date" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Selesai *</label>
                        <input type="date" name="end_date" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-teal-700">
                    <span>Aktif</span>
                </label>
                <button type="submit" class="w-full py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold">Simpan Periode</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama Periode</th>
                            <th class="px-4 py-3 text-left">Tipe</th>
                            <th class="px-4 py-3 text-left">Rentang Tanggal</th>
                            <th class="px-4 py-3 text-center">Target</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($periods as $period)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $period->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs rounded-full border {{ $period->type->color() }}">{{ $period->type->label() }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">
                                    {{ $period->start_date?->format('d M Y') }} - {{ $period->end_date?->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-center text-xs font-semibold">{{ $period->targets_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs rounded-full border {{ $period->is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $period->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                    <form method="POST" action="{{ route('master.periods.destroy', $period) }}" onsubmit="return confirm('Hapus periode ini?')" class="inline">
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
    </div>
</x-app-layout>
