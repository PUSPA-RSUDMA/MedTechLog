<x-app-layout>
    @php
        $pageTitle = $department === 'rm' ? 'Tugas RM' : ($department === 'it' ? 'Tugas IT' : 'Daftar Tugas');
        $indexRoute = $department === 'rm' ? route('tasks.rm') : ($department === 'it' ? route('tasks.it') : route('tasks.index'));
    @endphp
    <x-slot name="title">{{ $pageTitle }}</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">{{ $pageTitle }}</h1>
            <p class="text-xs text-slate-500">Target ketercapaian paten 100% — otomatis selesai saat pencapaian mencapai 100%</p>
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200/80 p-4 mb-5 shadow-sm">
        <form method="GET" action="{{ $indexRoute }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari Kode / Judul</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tugas..."
                       class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
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
                <label class="block text-xs font-medium text-slate-600 mb-1">Kesehatan Jadwal</label>
                <select name="health" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua</option>
                    <option value="on_track" @selected(request('health') === 'on_track')>On Track</option>
                    <option value="at_risk" @selected(request('health') === 'at_risk')>At Risk</option>
                    <option value="overdue" @selected(request('health') === 'overdue')>Overdue</option>
                    <option value="completed" @selected(request('health') === 'completed')>Selesai</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium">Filter</button>
                <a href="{{ $indexRoute }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-800">{{ $tasks->total() }}</span> {{ strtolower($pageTitle) }}</p>
        <a href="{{ route('tasks.create', ['department' => $department ?? 'it']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium shadow-sm">
            + Tambah {{ $pageTitle }}
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">Tugas</th>
                        <th class="px-4 py-3 text-left font-semibold">PIC &amp; Unit</th>
                        <th class="px-4 py-3 text-left font-semibold">Tanggal Target</th>
                        <th class="px-4 py-3 text-left font-semibold">Ketercapaian</th>
                        <th class="px-4 py-3 text-left font-semibold">Status &amp; Jadwal</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tasks as $task)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('tasks.show', $task) }}" class="font-semibold text-slate-900 hover:text-teal-700">{{ $task->title }}</a>
                                <p class="text-xs text-slate-500 mt-0.5 font-mono">{{ $task->code }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-xs">
                                <p class="font-semibold text-slate-800">{{ $task->display_pic_name }}</p>
                                <p class="text-slate-400">{{ $task->display_unit_name }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-700 whitespace-nowrap">
                                <p class="font-semibold">{{ $task->due_date?->format('d M Y') }}</p>
                                @if($task->due_date_histories_count > 0)
                                    <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-semibold">
                                        Diubah {{ $task->due_date_histories_count }}x
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full {{ $task->progress_percent >= 100 ? 'bg-emerald-600' : 'bg-teal-600' }} rounded-full" style="width: {{ $task->progress_percent }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold">{{ $task->progress_percent }} / 100</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full border {{ $task->status->color() }}">{{ $task->status->label() }}</span>
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-medium rounded-full border {{ $task->schedule_health['color'] }}">{{ $task->schedule_health['label'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('tasks.show', $task) }}" class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 text-xs font-semibold">Update / Detail</a>
                                <a href="{{ route('tasks.edit', $task) }}" class="text-xs font-medium text-sky-700 hover:underline">Ubah</a>
                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada data {{ strtolower($pageTitle) }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tasks->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $tasks->links() }}</div>
        @endif
    </div>
</x-app-layout>
