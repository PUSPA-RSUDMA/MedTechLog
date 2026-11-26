<x-app-layout>
    <x-slot name="title">Verifikasi Pekerjaan</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Antrean Verifikasi Pekerjaan</h1>
            <p class="text-xs text-slate-500">Tinjau bukti dukung, realisasi jam kerja, dan berikan persetujuan atau catatan revisi</p>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Menunggu Verifikasi ({{ $pendingTasks->total() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">Tugas</th>
                        <th class="px-4 py-3 text-left font-semibold">Pelaksana</th>
                        <th class="px-4 py-3 text-left font-semibold">Estimasi vs Aktual</th>
                        <th class="px-4 py-3 text-left font-semibold">Tenggat</th>
                        <th class="px-4 py-3 text-left font-semibold">Catatan Log</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingTasks as $task)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-slate-900">{{ $task->title }}</p>
                                <p class="text-xs text-slate-500 font-mono">{{ $task->code }} &bull; {{ $task->category?->name }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-xs">
                                <p class="font-medium text-slate-800">{{ $task->assignee?->name }}</p>
                                <p class="text-slate-400">{{ $task->assignee?->unit?->name }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-xs whitespace-nowrap">
                                {{ (float) $task->estimated_hours }}j / <strong>{{ (float) $task->actual_hours }}j</strong>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600 whitespace-nowrap">{{ $task->due_date?->format('d M Y') }}</td>
                            <td class="px-4 py-3.5 text-xs text-slate-600 whitespace-nowrap">
                                {{ $task->workLogs->count() }} log aktivitas
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('reviews.show', $task) }}" class="px-3 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold">
                                    Periksa & Verifikasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ada tugas yang menunggu verifikasi saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pendingTasks->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $pendingTasks->links() }}</div>
        @endif
    </div>

    {{-- Recent Reviews --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900">Riwayat Verifikasi Terbaru</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentReviews as $rev)
                <div class="px-5 py-3.5 flex flex-wrap items-center justify-between gap-2 text-sm">
                    <div>
                        <a href="{{ route('tasks.show', $rev->task) }}" class="font-semibold text-slate-900 hover:text-teal-700">{{ $rev->task?->code }} — {{ $rev->task?->title }}</a>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $rev->comments ?: 'Tanpa catatan.' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full border {{ $rev->decision->color() }}">{{ $rev->decision->label() }}</span>
                        <p class="text-[11px] text-slate-400 mt-1">{{ $rev->reviewed_at?->format('d M Y H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-xs text-slate-400">Belum ada riwayat verifikasi.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
