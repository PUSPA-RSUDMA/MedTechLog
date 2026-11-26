<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Judul Target <span class="text-rose-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $target->title ?? '') }}" required
               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi & Kriteria Keberhasilan</label>
        <textarea name="description" rows="3" class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">{{ old('description', $target->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Periode Kerja <span class="text-rose-500">*</span></label>
        <select name="work_period_id" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            @foreach($periods as $period)
                <option value="{{ $period->id }}" @selected(old('work_period_id', $target->work_period_id ?? '') == $period->id)>{{ $period->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Unit / Divisi <span class="text-rose-500">*</span></label>
        <select name="unit_id" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $target->unit_id ?? auth()->user()->unit_id) == $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Kategori Pekerjaan <span class="text-rose-500">*</span></label>
        <select name="work_category_id" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('work_category_id', $target->work_category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Penanggung Jawab (PIC) <span class="text-rose-500">*</span></label>
        <select name="owner_id" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            @foreach($owners as $owner)
                <option value="{{ $owner->id }}" @selected(old('owner_id', $target->owner_id ?? '') == $owner->id)>{{ $owner->name }} ({{ $owner->position ?? $owner->role->label() }})</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Nilai Target <span class="text-rose-500">*</span></label>
        <input type="number" step="0.01" min="0.01" name="target_value" value="{{ old('target_value', $target->target_value ?? 10) }}" required
               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Satuan Output <span class="text-rose-500">*</span></label>
        <input type="text" name="output_unit" value="{{ old('output_unit', $target->output_unit ?? 'dokumen') }}" required
               placeholder="tiket, dokumen, perangkat, modul, jam..."
               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Bobot (%) <span class="text-rose-500">*</span></label>
        <input type="number" step="0.5" min="0" max="100" name="weight" value="{{ old('weight', $target->weight ?? 20) }}" required
               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Prioritas</label>
        <select name="priority" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            @foreach($priorities as $val => $label)
                <option value="{{ $val }}" @selected(old('priority', isset($target) ? $target->priority->value : 'medium') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select name="status" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            @foreach($statuses as $val => $label)
                <option value="{{ $val }}" @selected(old('status', isset($target) ? $target->status->value : 'active') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
            <input type="date" name="start_date" value="{{ old('start_date', isset($target) ? $target->start_date?->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}" required
                   class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
            <input type="date" name="end_date" value="{{ old('end_date', isset($target) ? $target->end_date?->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d')) }}" required
                   class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
        </div>
    </div>
</div>
