<x-app-layout>
    <x-slot name="title">Detail Target {{ $target->code }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('targets.index') }}" class="text-slate-400 hover:text-slate-600">&larr;</a>
            <h1 class="text-lg font-bold text-slate-900">{{ $target->code }} — {{ $target->title }}</h1>
        </div>
    </x-slot>

    @if($unitPeriodTotalWeight > 100)
        <div class="mb-5 px-4 py-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <span><strong>Peringatan Bobot:</strong> Total bobot target unit <strong>{{ $target->unit?->name }}</strong> pada periode <strong>{{ $target->workPeriod?->name }}</strong> mencapai <strong>{{ number_format($unitPeriodTotalWeight, 1) }}%</strong> (melebihi 100%).</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 text-xs font-medium rounded-full border {{ $target->status->color() }}">{{ $target->status->label() }}</span>
                        <span class="px-2.5 py-0.5 text-xs font-medium rounded-full border {{ $target->priority->color() }}">Prioritas {{ $target->priority->label() }}</span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $target->title }}</h2>
                </div>
                <div class="flex gap-2">
                    @can('update', $target)
                        <a href="{{ route('targets.edit', $target) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-50">Ubah Target</a>
                    @endcan
                    <a href="{{ route('tasks.create', ['target_id' => $target->id]) }}" class="px-3 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold">+ Tambah Tugas</a>
                </div>
            </div>

            <p class="text-sm text-slate-600 mb-5">{{ $target->description ?: 'Tidak ada deskripsi tambahan.' }}</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-slate-100 text-xs">
                <div>
                    <span class="text-slate-400 block">Unit / Divisi</span>
                    <span class="font-semibold text-slate-800">{{ $target->unit?->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Penanggung Jawab</span>
                    <span class="font-semibold text-slate-800">{{ $target->owner?->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Periode</span>
                    <span class="font-semibold text-slate-800">{{ $target->workPeriod?->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Rentang Jadwal</span>
                    <span class="font-semibold text-slate-800">{{ $target->start_date?->format('d M') }} - {{ $target->end_date?->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ketercapaian Target</p>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-3xl font-bold text-teal-700">{{ $target->achievement_percent }}%</span>
                    <span class="text-xs text-slate-500">Bobot: <strong>{{ number_format((float) $target->weight, 0) }}%</strong></span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden mt-3">
                    <div class="h-full bg-teal-600 rounded-full" style="width: {{ $target->achievement_percent }}%"></div>
                </div>
                <p class="text-xs text-slate-600 mt-2">
                    Realisasi: <strong>{{ number_format((float) $target->achieved_value, 1) }}</strong> dari <strong>{{ number_format((float) $target->target_value, 1) }} {{ $target->output_unit }}</strong>
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-lg bg-slate-50">
                    <span class="text-slate-400 block">Total Estimasi</span>
                    <span class="text-base font-bold text-slate-800">{{ $totalEstimatedHours }} jam</span>
                </div>
                <div class="p-3 rounded-lg bg-slate-50">
                    <span class="text-slate-400 block">Total Realisasi</span>
                    <span class="text-base font-bold text-teal-700">{{ $totalActualHours }} jam</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Related Tasks --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Daftar Rincian Tugas ({{ $target->tasks->count() }})</h3>
            <a href="{{ route('tasks.create', ['target_id' => $target->id]) }}" class="text-xs font-semibold text-teal-700 hover:underline">+ Tambah Tugas ke Target Ini</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">Tugas</th>
                        <th class="px-4 py-3 text-left font-semibold">Pelaksana</th>
                        <th class="px-4 py-3 text-left font-semibold">Tenggat</th>
                        <th class="px-4 py-3 text-left font-semibold">Est vs Aktual</th>
                        <th class="px-4 py-3 text-left font-semibold">Progres</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($target->tasks as $task)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3">
                                <a href="{{ route('tasks.show', $task) }}" class="font-medium text-slate-900 hover:text-teal-700">{{ $task->title }}</a>
                                <p class="text-xs text-slate-400 font-mono">{{ $task->code }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-700">{{ $task->assignee?->name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">{{ $task->due_date?->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap">
                                {{ (float) $task->estimated_hours }}j / <strong>{{ (float) $task->actual_hours }}j</strong>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-semibold">{{ $task->progress_percent }}%</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full border {{ $task->status->color() }}">{{ $task->status->label() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada tugas yang dikaitkan pada target ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
