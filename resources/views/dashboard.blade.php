<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Dashboard Lengkap SARTIKA</h1>
            <p class="text-xs text-slate-500">Pantauan menyeluruh Tugas IT, Tugas RM, Tugas Diklat, Logbook Acara, Riwayat Target Tanggal &amp; WA Manager</p>
        </div>
    </x-slot>

    {{-- 6 KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <a href="{{ route('tasks.it') }}" class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm hover:border-teal-400 transition">
            <p class="text-[11px] font-semibold text-slate-500 uppercase">Tugas IT</p>
            <p class="text-2xl font-bold text-teal-700 mt-1">{{ $kpi['it_total'] }}</p>
            <p class="text-xs text-slate-500 mt-1">Selesai: <strong>{{ $kpi['it_completed'] }}</strong> &bull; {{ $kpi['it_avg'] }}%</p>
        </a>

        <a href="{{ route('tasks.rm') }}" class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm hover:border-sky-400 transition">
            <p class="text-[11px] font-semibold text-slate-500 uppercase">Tugas RM</p>
            <p class="text-2xl font-bold text-sky-700 mt-1">{{ $kpi['rm_total'] }}</p>
            <p class="text-xs text-slate-500 mt-1">Selesai: <strong>{{ $kpi['rm_completed'] }}</strong> &bull; {{ $kpi['rm_avg'] }}%</p>
        </a>

        <a href="{{ route('diklat.index') }}" class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm hover:border-indigo-400 transition">
            <p class="text-[11px] font-semibold text-slate-500 uppercase">Tugas Diklat</p>
            <p class="text-2xl font-bold text-indigo-700 mt-1">{{ $kpi['diklat_total'] }}</p>
            <p class="text-xs text-slate-500 mt-1">Total materi &amp; peserta</p>
        </a>

        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-500 uppercase">Reminder H-2 / H-1</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $kpi['due_soon'] }}</p>
            <p class="text-xs text-rose-600 font-semibold mt-1">Overdue: {{ $kpi['overdue'] }} tugas</p>
        </div>

        <a href="{{ route('logbook.index') }}" class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm hover:border-teal-400 transition">
            <p class="text-[11px] font-semibold text-slate-500 uppercase">Logbook Acara</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $kpi['logbook_upcoming'] }}</p>
            <p class="text-xs text-slate-500 mt-1">Agenda terdekat</p>
        </a>

        <a href="{{ route('wa-manager.index') }}" class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm hover:border-emerald-400 transition">
            <p class="text-[11px] font-semibold text-slate-500 uppercase">WA Manager</p>
            <p class="text-base font-bold mt-2 {{ $kpi['wa_status'] === 'connected' ? 'text-emerald-700' : 'text-amber-600' }}">
                {{ $kpi['wa_status'] === 'connected' ? 'Terhubung' : 'Belum Terhubung' }}
            </p>
            <p class="text-[11px] text-slate-400 mt-1">Klik untuk kelola WA Web</p>
        </a>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-1">Perbandingan Tugas IT vs Tugas RM</h2>
            <p class="text-xs text-slate-500 mb-4">Total tugas, jumlah selesai, dan rata-rata ketercapaian (%)</p>
            <div class="h-56">
                <canvas id="deptChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-1">Distribusi Status Tugas</h2>
            <p class="text-xs text-slate-500 mb-4">Komposisi seluruh tugas IT &amp; RM berdasarkan status</p>
            <div class="h-56">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Lower Row: Urgent Deadlines + Upcoming Logbook + Target Date Change History --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Urgent Tasks (Overdue + H-2 / H-1) --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-rose-50/50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-rose-900">Tenggat Terdekat (H-2 / H-1 / Overdue)</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($urgentTasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="block px-5 py-3 hover:bg-slate-50">
                        <div class="flex items-center justify-between text-xs mb-0.5">
                            <span class="font-semibold text-teal-700">{{ $task->department_label }}</span>
                            <span class="px-2 py-0.5 rounded-full border text-[10px] font-semibold {{ $task->schedule_health['color'] }}">
                                {{ $task->due_date?->format('d M Y') }}
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $task->title }}</p>
                        <p class="text-xs text-slate-500">{{ $task->display_pic_name }} ({{ $task->display_unit_name }}) &bull; <strong>{{ $task->progress_percent }}%</strong></p>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-xs text-slate-400">Tidak ada tugas mendekati atau melewati tenggat.</div>
                @endforelse
            </div>
        </div>

        {{-- Upcoming Logbook Events --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Agenda Logbook Mendatang</h3>
                <a href="{{ route('logbook.index') }}" class="text-xs font-semibold text-teal-700 hover:underline">Kalender &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($upcomingLogbook as $ev)
                    <a href="{{ route('logbook.index', ['month' => $ev->event_date->format('Y-m')]) }}" class="block px-5 py-3 hover:bg-slate-50">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $ev->color }}"></span>
                            <span class="font-bold text-slate-700">{{ $ev->event_date->format('d M Y') }}</span>
                            @if($ev->event_date->isTomorrow())
                                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">Besok (H-1)</span>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-slate-900 mt-0.5">{{ $ev->title }}</p>
                        @if($ev->location)
                            <p class="text-xs text-slate-500">{{ $ev->location }}</p>
                        @endif
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-xs text-slate-400">Belum ada agenda logbook mendatang.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Target Due Date Histories --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">History Perubahan Tanggal Target</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentDueDateHistories as $h)
                    <a href="{{ route('tasks.show', $h->task) }}" class="block px-5 py-3 hover:bg-slate-50 text-xs">
                        <p class="font-semibold text-slate-900 truncate">{{ $h->task?->title }}</p>
                        <p class="text-amber-700 font-semibold mt-0.5">
                            {{ $h->old_due_date?->format('d M Y') }} &rarr; {{ $h->new_due_date?->format('d M Y') }}
                        </p>
                        @if($h->reason)
                            <p class="text-slate-500 mt-0.5 line-clamp-1">Alasan: {{ $h->reason }}</p>
                        @endif
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-xs text-slate-400">Belum ada perubahan tanggal target.</div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deptData = @json($deptChart);
            const statusData = @json($statusChart);

            const deptCtx = document.getElementById('deptChart');
            if (deptCtx && window.Chart) {
                new window.Chart(deptCtx, {
                    type: 'bar',
                    data: {
                        labels: deptData.labels,
                        datasets: [
                            { label: 'Total Tugas', data: deptData.total, backgroundColor: '#E8C5CE', borderRadius: 6 },
                            { label: 'Selesai (100%)', data: deptData.completed, backgroundColor: '#CB4D6D', borderRadius: 6 },
                        ],
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } },
                });
            }

            const statusCtx = document.getElementById('statusChart');
            if (statusCtx && window.Chart) {
                new window.Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels,
                        datasets: [{
                            data: statusData.counts,
                            backgroundColor: ['#D4B4BD', '#E5989B', '#CB4D6D', '#331F24'],
                        }],
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
