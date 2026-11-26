<x-app-layout>
    <x-slot name="title">Catat Log Kerja Harian</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Catat Log Pekerjaan Harian</h1>
            <p class="text-xs text-slate-500">Catat aktivitas pekerjaan cukup dengan tanggal pengerjaan dan uraian progres</p>
        </div>
    </x-slot>

    <div class="max-w-3xl" x-data="{
        createIncidental: {{ old('create_incidental_task') ? 'true' : 'false' }},
        statusAfter: '{{ old('status_after_log', 'in_progress') }}'
    }">
        <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-sm">
            <form method="POST" action="{{ route('work-logs.store') }}" class="space-y-5">
                @csrf

                <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Buat Tugas Insidental Cepat?</p>
                        <p class="text-xs text-slate-500">Centang jika Anda ingin mencatat pekerjaan mendadak yang belum ada di daftar tugas.</p>
                    </div>
                    <input type="checkbox" name="create_incidental_task" value="1" x-model="createIncidental"
                           class="rounded border-slate-300 text-teal-700 focus:ring-teal-600 w-5 h-5">
                </div>

                <div x-show="!createIncidental">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pilih Tugas Aktif <span class="text-rose-500">*</span></label>
                    <select name="task_id" :required="!createIncidental" class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                        <option value="">-- Pilih Tugas --</option>
                        @foreach($tasks as $t)
                            <option value="{{ $t->id }}" @selected(old('task_id', $defaultTaskId) == $t->id)>
                                [{{ $t->code }}] {{ $t->title }} (Progres: {{ $t->progress_percent }}%)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="createIncidental" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-amber-50/70 border border-amber-200">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-amber-900 mb-1">Judul Tugas Insidental <span class="text-rose-500">*</span></label>
                        <input type="text" name="incidental_title" value="{{ old('incidental_title') }}" :required="createIncidental"
                               placeholder="Contoh: Penanganan darurat gangguan jaringan IGD"
                               class="w-full rounded-lg border-amber-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-amber-900 mb-1">Kategori Pekerjaan <span class="text-rose-500">*</span></label>
                        <select name="incidental_category_id" :required="createIncidental" class="w-full rounded-lg border-amber-300 text-sm">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('incidental_category_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-amber-900 mb-1">Estimasi Jam <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.25" min="0.25" name="incidental_estimated_hours" value="{{ old('incidental_estimated_hours', 2) }}" :required="createIncidental"
                               class="w-full rounded-lg border-amber-300 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pekerjaan <span class="text-rose-500">*</span></label>
                        <input type="date" name="log_date" value="{{ old('log_date', now()->format('Y-m-d')) }}" required
                               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Output yang Dicapai</label>
                        <input type="number" step="0.01" min="0" name="output_achieved" value="{{ old('output_achieved', 0) }}"
                               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status Tugas Setelah Log <span class="text-rose-500">*</span></label>
                        <select name="status_after_log" x-model="statusAfter" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                            @foreach($statuses as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Uraian Aktivitas Pekerjaan <span class="text-rose-500">*</span></label>
                    <textarea name="activity_description" rows="4" required placeholder="Jelaskan secara ringkas dan jelas pekerjaan yang dilakukan pada tanggal tersebut..."
                              class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">{{ old('activity_description') }}</textarea>
                </div>

                {{-- Conditional On Hold Obstacle & Solution Plan --}}
                <div x-show="statusAfter === 'on_hold'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl bg-rose-50 border border-rose-200">
                    <div>
                        <label class="block text-xs font-semibold text-rose-800 mb-1">Kendala / Hambatan <span class="text-rose-600">*</span></label>
                        <textarea name="obstacle" rows="3" :required="statusAfter === 'on_hold'" placeholder="Jelaskan kendala yang menyebabkan pekerjaan tertunda..."
                                  class="w-full rounded-lg border-rose-300 text-sm focus:border-rose-500 focus:ring-rose-500">{{ old('obstacle') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-rose-800 mb-1">Rencana Solusi / Tindak Lanjut <span class="text-rose-600">*</span></label>
                        <textarea name="solution_plan" rows="3" :required="statusAfter === 'on_hold'" placeholder="Langkah penyelesaian yang direncanakan..."
                                  class="w-full rounded-lg border-rose-300 text-sm focus:border-rose-500 focus:ring-rose-500">{{ old('solution_plan') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('work-logs.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm hover:bg-slate-50">Batal</a>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold">Simpan Log Kerja</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
