<x-app-layout>
    <x-slot name="title">Analisis Varians</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Analisis Varians: Estimasi vs Realisasi</h1>
            <p class="text-xs text-slate-500">Evaluasi deviasi durasi kerja, ketercapaian output, dan ketepatan jadwal tim</p>
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200/80 p-4 mb-5 shadow-sm">
        <form method="GET" action="{{ route('variance.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Periode</label>
                <select name="work_period_id" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Periode</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" @selected(request('work_period_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Unit</label>
                <select name="unit_id" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(request('unit_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Pelaksana</label>
                <select name="user_id" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Pelaksana</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Kategori</label>
                <select name="work_category_id" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('work_category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium">Analisis</button>
                <a href="{{ route('variance.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 text-sm">Reset</a>
            </div>
        </form>
    </div>

    {{-- Summary KPIs --}}
    @php $sum = $analysis['summary']; @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total Estimasi Waktu</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $sum['total_estimated_hours'] }} jam</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total Realisasi Waktu</p>
            <p class="text-2xl font-bold text-teal-700 mt-1">{{ $sum['total_actual_hours'] }} jam</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Selisih / Deviasi Total</p>
            <p class="text-2xl font-bold mt-1 {{ $sum['total_diff_hours'] > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                {{ $sum['total_diff_hours'] > 0 ? '+' : '' }}{{ $sum['total_diff_hours'] }} jam ({{ $sum['overall_deviation_percent'] > 0 ? '+' : '' }}{{ $sum['overall_deviation_percent'] }}%)
            </p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Indeks Akurasi Estimasi</p>
            <p class="text-2xl font-bold text-sky-700 mt-1">{{ $sum['accuracy_index'] }}%</p>
        </div>
    </div>

    {{-- Automated Insights --}}
    <div class="bg-teal-50/70 border border-teal-200 rounded-xl p-4 mb-6">
        <p class="text-xs font-bold uppercase tracking-wider text-teal-800 mb-2">Ringkasan Insight Otomatis</p>
        <ul class="list-disc list-inside text-sm text-teal-900 space-y-1">
            @foreach($analysis['insights'] as $insight)
                <li>{{ $insight }}</li>
            @endforeach
        </ul>
    </div>

    {{-- Rekap per Kategori & per Anggota --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 font-bold text-sm text-slate-900">Rekap Varians per Kategori Pekerjaan</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-2.5 text-left">Kategori</th>
                            <th class="px-3 py-2.5 text-right">Tugas</th>
                            <th class="px-3 py-2.5 text-right">Est (Jam)</th>
                            <th class="px-3 py-2.5 text-right">Akt (Jam)</th>
                            <th class="px-4 py-2.5 text-right">Deviasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($analysis['by_category'] as $cat)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-slate-800">{{ $cat['category_name'] }}</td>
                                <td class="px-3 py-2.5 text-right">{{ $cat['task_count'] }}</td>
                                <td class="px-3 py-2.5 text-right">{{ $cat['estimated_hours'] }}</td>
                                <td class="px-3 py-2.5 text-right font-semibold">{{ $cat['actual_hours'] }}</td>
                                <td class="px-4 py-2.5 text-right font-bold {{ $cat['deviation_percent'] > 10 ? 'text-rose-600' : 'text-teal-700' }}">
                                    {{ $cat['deviation_percent'] > 0 ? '+' : '' }}{{ $cat['deviation_percent'] }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 font-bold text-sm text-slate-900">Rekap Varians per Anggota Tim</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-2.5 text-left">Pelaksana</th>
                            <th class="px-3 py-2.5 text-right">Selesai/Total</th>
                            <th class="px-3 py-2.5 text-right">Est (Jam)</th>
                            <th class="px-3 py-2.5 text-right">Akt (Jam)</th>
                            <th class="px-4 py-2.5 text-right">Deviasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($analysis['by_user'] as $u)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <p class="font-medium text-slate-800">{{ $u['user_name'] }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $u['unit_name'] }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-right">{{ $u['completed_count'] }} / {{ $u['task_count'] }}</td>
                                <td class="px-3 py-2.5 text-right">{{ $u['estimated_hours'] }}</td>
                                <td class="px-3 py-2.5 text-right font-semibold">{{ $u['actual_hours'] }}</td>
                                <td class="px-4 py-2.5 text-right font-bold {{ $u['deviation_percent'] > 10 ? 'text-rose-600' : 'text-teal-700' }}">
                                    {{ $u['deviation_percent'] > 0 ? '+' : '' }}{{ $u['deviation_percent'] }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detail Per Task --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 font-bold text-sm text-slate-900">Perbandingan Detail per Tugas</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tugas</th>
                        <th class="px-3 py-3 text-left">Pelaksana</th>
                        <th class="px-3 py-3 text-right">Est (Jam)</th>
                        <th class="px-3 py-3 text-right">Akt (Jam)</th>
                        <th class="px-3 py-3 text-right">Varians Jam</th>
                        <th class="px-3 py-3 text-right">Output (%)</th>
                        <th class="px-4 py-3 text-left">Ketepatan Jadwal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($analysis['rows'] as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('tasks.show', $row['task']) }}" class="font-semibold text-slate-900 hover:text-teal-700">{{ $row['task']->title }}</a>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $row['task']->code }}</p>
                            </td>
                            <td class="px-3 py-3 text-slate-700">{{ $row['task']->assignee?->name }}</td>
                            <td class="px-3 py-3 text-right">{{ $row['estimated_hours'] }}</td>
                            <td class="px-3 py-3 text-right font-semibold">{{ $row['actual_hours'] }}</td>
                            <td class="px-3 py-3 text-right font-bold {{ $row['diff_hours'] > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ $row['diff_hours'] > 0 ? '+' : '' }}{{ $row['diff_hours'] }}j ({{ $row['deviation_percent'] > 0 ? '+' : '' }}{{ $row['deviation_percent'] }}%)
                            </td>
                            <td class="px-3 py-3 text-right font-semibold">{{ $row['output_percent'] }}%</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full border text-[11px] font-medium {{ $row['schedule_status']['color'] }}">
                                    {{ $row['schedule_status']['label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">Tidak ada data tugas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
