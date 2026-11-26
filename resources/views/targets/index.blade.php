<x-app-layout>
    <x-slot name="title">Target & Rencana Kerja</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Target & Rencana Kerja</h1>
            <p class="text-xs text-slate-500">Kelola sasaran kerja, bobot nilai, dan ketercapaian target per periode</p>
        </div>
    </x-slot>

    {{-- Filter & Action Bar --}}
    <div class="bg-white rounded-xl border border-slate-200/80 p-4 mb-5 shadow-sm">
        <form method="GET" action="{{ route('targets.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari Kode / Judul</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari target..."
                       class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Periode</label>
                <select name="work_period_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Semua Periode</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" @selected(request('work_period_id') == $period->id)>{{ $period->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Unit</label>
                <select name="unit_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $val => $label)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium">Filter</button>
                <a href="{{ route('targets.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-800">{{ $targets->total() }}</span> target</p>
        @can('create', App\Models\Target::class)
            <a href="{{ route('targets.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium shadow-sm">
                + Tambah Target
            </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">Kode & Judul Target</th>
                        <th class="px-4 py-3 text-left font-semibold">Unit / PIC</th>
                        <th class="px-4 py-3 text-left font-semibold">Periode</th>
                        <th class="px-4 py-3 text-left font-semibold">Ketercapaian</th>
                        <th class="px-4 py-3 text-left font-semibold">Bobot</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($targets as $target)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('targets.show', $target) }}" class="font-semibold text-slate-900 hover:text-teal-700">{{ $target->title }}</a>
                                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                                    <span class="font-mono">{{ $target->code }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $target->category?->name }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $target->tasks_count }} tugas</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-medium text-slate-800 text-xs">{{ $target->unit?->name }}</p>
                                <p class="text-xs text-slate-500">{{ $target->owner?->name }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600 whitespace-nowrap">
                                <p class="font-medium">{{ $target->workPeriod?->name }}</p>
                                <p class="text-slate-400">{{ $target->end_date?->format('d M Y') }}</p>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-teal-600 rounded-full" style="width: {{ $target->achievement_percent }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">{{ $target->achievement_percent }}%</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ number_format((float) $target->achieved_value, 1) }} / {{ number_format((float) $target->target_value, 1) }} {{ $target->output_unit }}</p>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-semibold text-slate-700">
                                {{ number_format((float) $target->weight, 0) }}%
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full border {{ $target->status->color() }}">
                                    {{ $target->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('targets.show', $target) }}" class="text-xs font-medium text-teal-700 hover:underline">Detail</a>
                                @can('update', $target)
                                    <a href="{{ route('targets.edit', $target) }}" class="text-xs font-medium text-sky-700 hover:underline">Ubah</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">Belum ada target kerja yang sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($targets->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $targets->links() }}</div>
        @endif
    </div>
</x-app-layout>
