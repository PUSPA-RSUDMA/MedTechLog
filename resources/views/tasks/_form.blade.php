@php
    $activeDept = old('department', $task->department ?? $department ?? 'it');
    $deptLabel = $activeDept === 'rm' ? 'Tugas RM' : 'Tugas IT';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Kelompok Tugas (Paten)</label>
        <input type="hidden" name="department" value="{{ $activeDept }}">
        <input type="text" value="{{ $deptLabel }}" readonly
               class="w-full rounded-lg border-slate-200 bg-slate-100 text-slate-700 font-semibold text-sm cursor-not-allowed">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Prioritas</label>
        <select name="priority" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            @foreach($priorities as $val => $label)
                <option value="{{ $val }}" @selected(old('priority', isset($task) ? $task->priority->value : 'medium') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Judul Tugas <span class="text-rose-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" required
               placeholder="Contoh: Integrasi SIMRS / Validasi Berkas Rekam Medis"
               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Penanggung Jawab (PIC) &amp; Unit <span class="text-rose-500">*</span></label>
        <select name="pic_id" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            <option value="">-- Pilih PIC dari Master PIC --</option>
            @foreach($pics as $p)
                <option value="{{ $p->id }}" @selected(old('pic_id', $task->pic_id ?? '') == $p->id)>
                    {{ $p->name }} {{ $p->unit ? '— ' . $p->unit->name : '' }}
                </option>
            @endforeach
        </select>
        @if($pics->isEmpty())
            <p class="text-[11px] text-amber-700 mt-1">Belum ada Master PIC. <a href="{{ route('master.pics.index') }}" class="underline font-semibold">Tambah Master PIC di sini</a>.</p>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Target Ketercapaian (Paten)</label>
        <input type="number" name="estimated_output" value="100" readonly
               class="w-full rounded-lg border-slate-200 bg-slate-100 text-slate-600 font-bold text-sm cursor-not-allowed">
        <p class="text-[11px] text-slate-400 mt-1">Otomatis 100 — status langsung Selesai saat mencapai 100.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
        <input type="date" name="estimated_start_date" value="{{ old('estimated_start_date', isset($task) ? $task->estimated_start_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required
               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Target (Tenggat) <span class="text-rose-500">*</span></label>
        <input type="date" name="due_date" value="{{ old('due_date', isset($task) ? $task->due_date?->format('Y-m-d') : now()->addDays(5)->format('Y-m-d')) }}" required
               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
    </div>

    @isset($task)
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-amber-800 mb-1">Alasan Perubahan Tanggal Target (isi jika tanggal target diubah)</label>
            <input type="text" name="due_date_change_reason" value="{{ old('due_date_change_reason') }}"
                   placeholder="Contoh: Belum selesai, diperpanjang karena menunggu konfirmasi vendor"
                   class="w-full rounded-lg border-amber-300 text-sm focus:border-amber-500 focus:ring-amber-500">
        </div>
    @endisset

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Catatan / Deskripsi (Opsional)</label>
        <textarea name="description" rows="3" placeholder="Rincian singkat pekerjaan..."
                  class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">{{ old('description', $task->description ?? '') }}</textarea>
    </div>
</div>
