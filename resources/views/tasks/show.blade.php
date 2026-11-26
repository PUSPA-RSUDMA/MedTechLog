<x-app-layout>
    <x-slot name="title">Detail Tugas {{ $task->code }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ $task->department === 'rm' ? route('tasks.rm') : route('tasks.it') }}" class="text-slate-400 hover:text-slate-600">&larr;</a>
            <h1 class="text-lg font-bold text-slate-900">{{ $task->code }} — {{ $task->title }}</h1>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Info Tugas --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">{{ $task->department_label }}</span>
                            <span class="px-2.5 py-0.5 text-xs font-medium rounded-full border {{ $task->status->color() }}">{{ $task->status->label() }}</span>
                            <span class="px-2.5 py-0.5 text-xs font-medium rounded-full border {{ $task->schedule_health['color'] }}">{{ $task->schedule_health['label'] }}</span>
                            <span class="px-2.5 py-0.5 text-xs font-medium rounded-full border {{ $task->priority->color() }}">Prioritas {{ $task->priority->label() }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $task->title }}</h2>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('tasks.edit', $task) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 text-xs font-medium hover:bg-slate-50">Ubah</a>
                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-semibold">Hapus</button>
                        </form>
                    </div>
                </div>

                @if($task->description)
                    <p class="text-sm text-slate-600 mb-5">{{ $task->description }}</p>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 block">Unit / Divisi</span>
                        <span class="font-semibold text-slate-800">{{ $task->display_unit_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">PIC</span>
                        <span class="font-semibold text-slate-800">{{ $task->display_pic_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Tanggal Target</span>
                        <span class="font-semibold text-slate-800">{{ $task->due_date?->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Form Update Ketercapaian (Auto-Complete at 100%) --}}
            @if($task->status->value !== 'completed')
                <div class="bg-white rounded-xl border-2 border-teal-200 p-6 shadow-sm" x-data="{ statusAfter: '{{ old('status_after_log', 'in_progress') }}' }">
                    <h3 class="text-sm font-bold text-teal-900 mb-1">Update Ketercapaian Tugas</h3>
                    <p class="text-xs text-slate-500 mb-4">
                        Sisa menuju selesai: <strong>{{ max(0, 100 - (int) $task->actual_output) }}</strong> (dari target paten 100). Saat total mencapai <strong>100</strong>, status langsung berubah menjadi <strong>Selesai</strong>.
                    </p>

                    <form method="POST" action="{{ route('work-logs.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="task_id" value="{{ $task->id }}">

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Update <span class="text-rose-500">*</span></label>
                                <input type="date" name="log_date" value="{{ old('log_date', now()->format('Y-m-d')) }}" required
                                       class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Tambahan Ketercapaian (1-100)</label>
                                <input type="number" step="1" min="0" max="100" name="output_achieved" value="{{ old('output_achieved', 0) }}"
                                       class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Status Tugas <span class="text-rose-500">*</span></label>
                                <select name="status_after_log" x-model="statusAfter" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                    <option value="in_progress">Sedang Berjalan</option>
                                    <option value="on_hold">Tertunda (Ada Kendala)</option>
                                    <option value="completed">Langsung Selesai (100%)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Catatan Progres <span class="text-rose-500">*</span></label>
                            <textarea name="activity_description" rows="2" required placeholder="Tuliskan progres pekerjaan hari ini..."
                                      class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">{{ old('activity_description') }}</textarea>
                        </div>

                        <div x-show="statusAfter === 'on_hold'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-lg bg-rose-50 border border-rose-200">
                            <div>
                                <label class="block text-xs font-semibold text-rose-800 mb-1">Kendala *</label>
                                <textarea name="obstacle" rows="2" :required="statusAfter === 'on_hold'" class="w-full rounded-lg border-rose-300 text-xs">{{ old('obstacle') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-rose-800 mb-1">Rencana Solusi *</label>
                                <textarea name="solution_plan" rows="2" :required="statusAfter === 'on_hold'" class="w-full rounded-lg border-rose-300 text-xs">{{ old('solution_plan') }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold">
                                Simpan Update Ketercapaian
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Riwayat Update Ketercapaian --}}
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Riwayat Update Ketercapaian ({{ $task->workLogs->count() }})</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($task->workLogs as $log)
                        <div class="p-4">
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                <span><strong class="text-slate-800">{{ $log->log_date?->format('d M Y') }}</strong> &bull; {{ $log->user?->name }}</span>
                                <span class="px-2 py-0.5 rounded-full border text-[11px] {{ $log->status_after_log->color() }}">{{ $log->status_after_log->label() }}</span>
                            </div>
                            <p class="text-sm text-slate-700">{{ $log->activity_description }}</p>
                            @if((int) $log->output_achieved > 0)
                                <p class="text-xs text-teal-700 mt-1">Ketercapaian: <strong>+{{ (int) $log->output_achieved }}</strong></p>
                            @endif
                            @if($log->obstacle)
                                <div class="mt-2 p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-800">
                                    <strong>Kendala:</strong> {{ $log->obstacle }} &mdash; <strong>Solusi:</strong> {{ $log->solution_plan }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">Belum ada update ketercapaian.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column: Ringkasan + Ubah Tanggal Target + History Tanggal Target --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Ringkasan Ketercapaian</h3>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-500">Progres Ketercapaian</span>
                        <span class="font-bold text-teal-700 text-base">{{ $task->progress_percent }}%</span>
                    </div>
                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full {{ $task->progress_percent >= 100 ? 'bg-emerald-600' : 'bg-teal-600' }} rounded-full" style="width: {{ $task->progress_percent }}%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-slate-50">
                        <span class="text-slate-400 block">Target Paten</span>
                        <span class="text-base font-bold text-slate-800">100</span>
                    </div>
                    <div class="p-3 rounded-lg bg-teal-50/70">
                        <span class="text-teal-600 block">Tercapai</span>
                        <span class="text-base font-bold text-teal-800">{{ min(100, (int) $task->actual_output) }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tanggal Mulai</span>
                        <span class="font-medium">{{ $task->estimated_start_date?->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tanggal Target</span>
                        <span class="font-semibold text-slate-800">{{ $task->due_date?->format('d M Y') }}</span>
                    </div>
                    @if($task->actual_completed_at)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tanggal Selesai</span>
                            <span class="font-semibold text-emerald-700">{{ $task->actual_completed_at->format('d M Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Form Cepat Ubah Tanggal Target --}}
            @if($task->status->value !== 'completed')
                <div class="bg-white rounded-xl border border-amber-200 p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-amber-900 mb-1">Perpanjang / Ubah Tanggal Target</h3>
                    <p class="text-[11px] text-slate-500 mb-3">Setiap perubahan tanggal target akan tersimpan di history di bawah.</p>
                    <form method="POST" action="{{ route('tasks.update-due-date', $task) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Target Baru *</label>
                            <input type="date" name="new_due_date" value="{{ $task->due_date?->format('Y-m-d') }}" required
                                   class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Alasan Perubahan *</label>
                            <textarea name="reason" rows="2" required placeholder="Contoh: Belum selesai, perpanjang ke 1 Des 2026"
                                      class="w-full rounded-lg border-slate-300 text-xs focus:border-amber-500 focus:ring-amber-500"></textarea>
                        </div>
                        <button type="submit" class="w-full py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold">
                            Simpan Perubahan Tanggal
                        </button>
                    </form>
                </div>
            @endif

            {{-- History Perubahan Tanggal Target --}}
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-sm font-bold text-slate-900">History Target Tanggal ({{ $task->dueDateHistories->count() }})</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($task->dueDateHistories as $h)
                        <div class="p-4 text-xs">
                            <p class="font-bold text-amber-700">
                                {{ $h->old_due_date?->format('d M Y') }} &rarr; {{ $h->new_due_date?->format('d M Y') }}
                            </p>
                            @if($h->reason)
                                <p class="text-slate-700 mt-1">{{ $h->reason }}</p>
                            @endif
                            <p class="text-[11px] text-slate-400 mt-1">
                                Diubah {{ $h->created_at?->format('d M Y H:i') }} oleh {{ $h->changer?->name ?? 'Admin' }}
                            </p>
                        </div>
                    @empty
                        <div class="p-5 text-center text-xs text-slate-400">Tanggal target belum pernah diubah.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
