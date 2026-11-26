<x-app-layout>
    <x-slot name="title">Verifikasi {{ $task->code }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('reviews.index') }}" class="text-slate-400 hover:text-slate-600">&larr;</a>
            <h1 class="text-lg font-bold text-slate-900">Verifikasi Pekerjaan: {{ $task->code }}</h1>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">{{ $task->title }}</h2>
                <p class="text-sm text-slate-600 mt-1">{{ $task->description }}</p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4 pt-4 border-t border-slate-100 text-xs">
                    <div><span class="text-slate-400 block">Pelaksana</span><strong>{{ $task->assignee?->name }}</strong></div>
                    <div><span class="text-slate-400 block">Estimasi / Aktual</span><strong>{{ (float) $task->estimated_hours }}j / {{ (float) $task->actual_hours }}j</strong></div>
                    <div><span class="text-slate-400 block">Output</span><strong>{{ (float) $task->actual_output }} / {{ (float) $task->estimated_output }} {{ $task->output_unit }}</strong></div>
                    <div><span class="text-slate-400 block">Tenggat</span><strong>{{ $task->due_date?->format('d M Y') }}</strong></div>
                </div>
            </div>

            {{-- Work Logs --}}
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 font-bold text-sm text-slate-900">Log Pekerjaan Terkait ({{ $task->workLogs->count() }})</div>
                <div class="divide-y divide-slate-100">
                    @foreach($task->workLogs as $log)
                        <div class="p-4 text-sm">
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <span><strong>{{ $log->log_date?->format('d M Y') }}</strong> &bull; {{ $log->user?->name }}</span>
                                <span>Output: +{{ (float) $log->output_achieved }}</span>
                            </div>
                            <p class="text-slate-700">{{ $log->activity_description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Decision Form --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-sm h-fit">
            <h3 class="text-sm font-bold text-slate-900 mb-4">Keputusan Verifikasi</h3>
            <form method="POST" action="{{ route('reviews.store', $task) }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="flex items-center gap-2.5 p-3 rounded-lg border border-emerald-200 bg-emerald-50/50 cursor-pointer">
                        <input type="radio" name="decision" value="approved" required class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">Setujui (Selesai)</p>
                            <p class="text-xs text-emerald-700">Tugas dinyatakan selesai dan memenuhi standar.</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-lg border border-amber-200 bg-amber-50/50 cursor-pointer">
                        <input type="radio" name="decision" value="revision_required" required class="text-amber-600 focus:ring-amber-500">
                        <div>
                            <p class="text-sm font-semibold text-amber-900">Minta Revisi</p>
                            <p class="text-xs text-amber-700">Kembalikan ke pelaksana untuk perbaikan.</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-lg border border-rose-200 bg-rose-50/50 cursor-pointer">
                        <input type="radio" name="decision" value="rejected" required class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <p class="text-sm font-semibold text-rose-900">Tolak</p>
                            <p class="text-xs text-rose-700">Pekerjaan tidak memenuhi kriteria yang ditetapkan.</p>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Catatan Umpan Balik</label>
                    <textarea name="comments" rows="4" placeholder="Wajib diisi jika meminta revisi atau menolak..."
                              class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">{{ old('comments') }}</textarea>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold">
                    Simpan Keputusan Verifikasi
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
