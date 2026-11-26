<x-app-layout>
    <x-slot name="title">Analisis & Laporan</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Analisis Ketercapaian &amp; Laporan</h1>
            <p class="text-xs text-slate-500">Rekap ketercapaian per Unit &amp; PIC serta ekspor dokumen PDF / Excel</p>
        </div>
    </x-slot>

    {{-- Simple Filter Bar --}}
    <div class="bg-white rounded-xl border border-slate-200/80 p-4 mb-5 shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Kelompok</label>
                <select name="department" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">IT &amp; RM</option>
                    <option value="it" @selected(request('department') === 'it')>Tugas IT</option>
                    <option value="rm" @selected(request('department') === 'rm')>Tugas RM</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Unit / Divisi</label>
                <select name="unit_id" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(request('unit_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">PIC</label>
                <select name="pic_id" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua PIC</option>
                    @foreach($pics as $p)
                        <option value="{{ $p->id }}" @selected(request('pic_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $val => $label)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Sampai Tenggat</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium">Filter</button>
                <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 text-sm">Reset</a>
            </div>
        </form>
    </div>

    {{-- Summary KPIs + Export Buttons --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total Tugas</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $summary['total_tasks'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Tugas Selesai</p>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ $summary['completed_tasks'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Rata-rata Ketercapaian</p>
            <p class="text-2xl font-bold text-teal-700 mt-1">{{ $summary['avg_progress'] }}%</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Melewati Tenggat (Overdue)</p>
            <p class="text-2xl font-bold text-rose-600 mt-1">{{ $summary['overdue_tasks'] }}</p>
        </div>
    </div>

    {{-- Analisis Ringkas per Unit & per PIC --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 font-bold text-sm text-slate-900">Analisis Ketercapaian per Unit</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-2.5 text-left">Unit / Divisi</th>
                            <th class="px-3 py-2.5 text-right">Selesai / Total</th>
                            <th class="px-4 py-2.5 text-right">Rata-rata Ketercapaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($byUnit as $u)
                            <tr>
                                <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $u['unit_name'] }}</td>
                                <td class="px-3 py-2.5 text-right">{{ $u['completed_tasks'] }} / {{ $u['total_tasks'] }}</td>
                                <td class="px-4 py-2.5 text-right font-bold text-teal-700">{{ $u['avg_progress'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 font-bold text-sm text-slate-900">Analisis Ketercapaian per PIC</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-2.5 text-left">PIC &amp; Unit</th>
                            <th class="px-3 py-2.5 text-right">Selesai / Total</th>
                            <th class="px-4 py-2.5 text-right">Rata-rata Ketercapaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($byPic as $p)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <p class="font-semibold text-slate-800">{{ $p['pic_name'] }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $p['unit_name'] }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-right">{{ $p['completed_tasks'] }} / {{ $p['total_tasks'] }}</td>
                                <td class="px-4 py-2.5 text-right font-bold text-teal-700">{{ $p['avg_progress'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Export Header + Detail Table --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <h2 class="text-sm font-bold text-slate-900">Rincian Laporan Tugas ({{ $tasks->count() }})</h2>
        <div class="flex gap-2">
            <a href="{{ route('reports.export-pdf', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm">
                Ekspor PDF
            </a>
            <a href="{{ route('reports.export-excel', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold shadow-sm">
                Ekspor Excel (.xlsx)
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode &amp; Tugas</th>
                        <th class="px-3 py-3 text-left">Unit &amp; PIC</th>
                        <th class="px-3 py-3 text-left">Tenggat</th>
                        <th class="px-3 py-3 text-right">Ketercapaian</th>
                        <th class="px-4 py-3 text-left">Status &amp; Jadwal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tasks as $task)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <span class="font-mono text-slate-400">{{ $task->code }}</span>
                                <p class="font-semibold text-slate-900">{{ $task->title }}</p>
                            </td>
                            <td class="px-3 py-3">
                                <p class="font-semibold text-slate-800">{{ $task->display_pic_name }}</p>
                                <p class="text-slate-400">{{ $task->display_unit_name }}</p>
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap">{{ $task->due_date?->format('d M Y') }}</td>
                            <td class="px-3 py-3 text-right whitespace-nowrap">
                                <span class="font-bold text-teal-700">{{ $task->progress_percent }}%</span>
                                <p class="text-[11px] text-slate-400">{{ (int) $task->actual_output }} / {{ (int) $task->estimated_output }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full border {{ $task->status->color() }}">{{ $task->status->label() }}</span>
                                <span class="ml-1 px-2 py-0.5 rounded-full border {{ $task->schedule_health['color'] }}">{{ $task->schedule_health['label'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Tidak ada data laporan sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
