<x-app-layout>
    <x-slot name="title">Log Kerja Harian</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Log Pekerjaan Harian</h1>
            <p class="text-xs text-slate-500">Catatan aktivitas kerja, durasi realisasi, kendala, dan bukti dukung</p>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200/80 p-4 mb-5 shadow-sm">
        <form method="GET" action="{{ route('work-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari Aktivitas / Tugas</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari uraian atau kode tugas..."
                       class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Pelaksana</label>
                <select name="user_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Semua</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium">Filter</button>
                <a href="{{ route('work-logs.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="text-sm text-slate-600">
            Menampilkan <span class="font-bold text-teal-700">{{ $workLogs->total() }}</span> catatan log pekerjaan
        </div>
        <a href="{{ route('work-logs.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium shadow-sm">
            + Catat Log Kerja
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold">Tugas & Aktivitas</th>
                        <th class="px-4 py-3 text-left font-semibold">Pelaksana</th>
                        <th class="px-4 py-3 text-left font-semibold">Output</th>
                        <th class="px-4 py-3 text-left font-semibold">Status Akhir</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workLogs as $log)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-medium text-slate-700">
                                {{ $log->log_date?->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3.5 max-w-md">
                                <a href="{{ route('tasks.show', $log->task) }}" class="font-semibold text-teal-700 hover:underline text-xs block">{{ $log->task?->code }} — {{ $log->task?->title }}</a>
                                <p class="text-xs text-slate-600 mt-0.5 line-clamp-2">{{ $log->activity_description }}</p>
                                @if($log->obstacle)
                                    <p class="text-[11px] text-rose-600 mt-1"><strong>Kendala:</strong> {{ $log->obstacle }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-700 whitespace-nowrap">{{ $log->user?->name }}</td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-semibold text-slate-800">
                                @if((float) $log->output_achieved > 0)
                                    +{{ (float) $log->output_achieved }} {{ $log->task?->output_unit }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full border {{ $log->status_after_log->color() }}">
                                    {{ $log->status_after_log->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                @can('delete', $log)
                                    <form method="POST" action="{{ route('work-logs.destroy', $log) }}" onsubmit="return confirm('Hapus catatan log kerja ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada catatan log pekerjaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($workLogs->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $workLogs->links() }}</div>
        @endif
    </div>
</x-app-layout>
