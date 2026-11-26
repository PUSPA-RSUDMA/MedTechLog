<x-app-layout>
    <x-slot name="title">Logbook Acara</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Logbook Acara Admin</h1>
            <p class="text-xs text-slate-500">Klik tanggal pada kalender untuk menambah, mengubah, atau menghapus acara — pengingat H-1 otomatis masuk notifikasi</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6" x-data="{
        eventsByDate: @js($eventsByDate),
        modalOpen: false,
        selectedDate: '',
        editingId: null,
        form: {
            event_date: '',
            title: '',
            location: '',
            description: '',
            color: '#0F766E'
        },
        openDate(dateStr) {
            this.selectedDate = dateStr;
            this.resetForm(dateStr);
            this.modalOpen = true;
        },
        resetForm(dateStr) {
            this.editingId = null;
            this.form = {
                event_date: dateStr || this.selectedDate,
                title: '',
                location: '',
                description: '',
                color: '#0F766E'
            };
        },
        startEdit(ev) {
            this.editingId = ev.id;
            this.form = {
                event_date: ev.event_date,
                title: ev.title,
                location: ev.location || '',
                description: ev.description || '',
                color: ev.color || '#0F766E'
            };
        },
        get selectedEvents() {
            return this.eventsByDate[this.selectedDate] || [];
        }
    }">
        {{-- Calendar Main Grid --}}
        <div class="xl:col-span-3 bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            {{-- Calendar Header --}}
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('logbook.index', ['month' => $prevMonth]) }}"
                       class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700">&larr; Sebelumnya</a>
                    <a href="{{ route('logbook.index') }}"
                       class="px-3 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-xs font-semibold text-teal-700">Hari Ini</a>
                    <a href="{{ route('logbook.index', ['month' => $nextMonth]) }}"
                       class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700">Berikutnya &rarr;</a>
                </div>
                <h2 class="text-base font-bold text-slate-900 capitalize">
                    {{ $currentMonth->translatedFormat('F Y') }}
                </h2>
                <button type="button" @click="openDate('{{ now()->format('Y-m-d') }}')"
                        class="px-3.5 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold">
                    + Tambah Acara
                </button>
            </div>

            {{-- Day Names --}}
            <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50 text-center text-xs font-semibold text-slate-500">
                <div class="py-2.5">Sen</div>
                <div class="py-2.5">Sel</div>
                <div class="py-2.5">Rab</div>
                <div class="py-2.5">Kam</div>
                <div class="py-2.5">Jum</div>
                <div class="py-2.5 text-rose-500">Sab</div>
                <div class="py-2.5 text-rose-500">Min</div>
            </div>

            {{-- Calendar Cells --}}
            <div class="grid grid-cols-7 divide-x divide-y divide-slate-100">
                @foreach($days as $cell)
                    @php
                        $cellEvents = $eventsByDate[$cell['date']] ?? [];
                    @endphp
                    <div @click="openDate('{{ $cell['date'] }}')"
                         class="min-h-[104px] p-2 cursor-pointer transition hover:bg-teal-50/40 flex flex-col {{ $cell['isCurrentMonth'] ? 'bg-white' : 'bg-slate-50/60 text-slate-400' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold {{ $cell['isToday'] ? 'bg-teal-700 text-white' : ($cell['isCurrentMonth'] ? 'text-slate-700' : 'text-slate-400') }}">
                                {{ $cell['day'] }}
                            </span>
                            @if(count($cellEvents) > 0)
                                <span class="text-[10px] font-semibold text-slate-400">{{ count($cellEvents) }}</span>
                            @endif
                        </div>

                        <div class="space-y-1 flex-1 overflow-hidden">
                            @foreach(array_slice($cellEvents, 0, 3) as $ev)
                                <div class="px-1.5 py-0.5 rounded text-[11px] font-medium text-white truncate"
                                     style="background-color: {{ $ev['color'] }}"
                                     title="{{ $ev['title'] }}">
                                    {{ $ev['title'] }}
                                </div>
                            @endforeach
                            @if(count($cellEvents) > 3)
                                <p class="text-[10px] text-teal-700 font-semibold">+{{ count($cellEvents) - 3 }} lainnya</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right Sidebar: Upcoming Events --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm h-fit overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900">Acara Mendatang (14 Hari)</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Notifikasi otomatis dikirim H-1 sebelum acara</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($upcomingEvents as $ev)
                    <div @click="openDate('{{ $ev->event_date->format('Y-m-d') }}')"
                         class="px-5 py-3.5 hover:bg-slate-50 cursor-pointer transition">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $ev->color }}"></span>
                            <span class="text-xs font-bold text-slate-700">{{ $ev->event_date->format('d M Y') }}</span>
                            @if($ev->event_date->isTomorrow())
                                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">Besok (H-1)</span>
                            @elseif($ev->event_date->isToday())
                                <span class="px-1.5 py-0.5 rounded bg-teal-100 text-teal-800 text-[10px] font-bold">Hari Ini</span>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-slate-900">{{ $ev->title }}</p>
                        @if($ev->location)
                            <p class="text-xs text-slate-500 mt-0.5">Lokasi: {{ $ev->location }}</p>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-xs text-slate-400">Belum ada acara terjadwal dalam 14 hari ke depan.</div>
                @endforelse
            </div>
        </div>

        {{-- Modal Detail & Form Tambah / Edit / Hapus Acara --}}
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-xl overflow-hidden max-h-[90vh] flex flex-col" @click.outside="modalOpen = false">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Acara Tanggal <span class="text-teal-700" x-text="selectedDate"></span></h3>
                        <p class="text-xs text-slate-500">Kelola atau tambahkan acara logbook pada tanggal ini</p>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold leading-none">&times;</button>
                </div>

                <div class="p-6 overflow-y-auto space-y-5">
                    {{-- Existing Events on Selected Date --}}
                    <template x-if="selectedEvents.length > 0">
                        <div class="space-y-2.5">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Acara di Tanggal Ini</p>
                            <template x-for="ev in selectedEvents" :key="ev.id">
                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="'background-color:' + ev.color"></span>
                                            <p class="text-sm font-bold text-slate-900 truncate" x-text="ev.title"></p>
                                        </div>
                                        <p x-show="ev.location" class="text-xs text-slate-500 mt-0.5" x-text="'Lokasi: ' + ev.location"></p>
                                        <p x-show="ev.description" class="text-xs text-slate-600 mt-1" x-text="ev.description"></p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="startEdit(ev)" class="px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 text-xs font-semibold">Edit</button>
                                        <form :action="'{{ url('/logbook') }}/' + ev.id" method="POST" onsubmit="return confirm('Hapus acara ini dari logbook?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Add / Edit Form --}}
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-bold uppercase tracking-wider text-teal-800" x-text="editingId ? 'Edit Acara Terpilih' : 'Tambah Acara Baru'"></p>
                            <button type="button" x-show="editingId" @click="resetForm(selectedDate)" class="text-xs text-slate-500 hover:underline">Batal Edit</button>
                        </div>

                        <form :action="editingId ? ('{{ url('/logbook') }}/' + editingId) : '{{ route('logbook.store') }}'" method="POST" class="space-y-3.5">
                            @csrf
                            <template x-if="editingId">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Acara *</label>
                                    <input type="date" name="event_date" x-model="form.event_date" required class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Warna Penanda</label>
                                    <select name="color" x-model="form.color" class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                        <option value="#0F766E">Teal (Default)</option>
                                        <option value="#0284C7">Biru</option>
                                        <option value="#D97706">Kuning / Amber</option>
                                        <option value="#E11D48">Merah (Penting)</option>
                                        <option value="#7C3AED">Ungu</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Judul Acara *</label>
                                <input type="text" name="title" x-model="form.title" required placeholder="Contoh: Rapat Evaluasi SIMRS & Rekam Medis"
                                       class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Lokasi / Tempat (Opsional)</label>
                                <input type="text" name="location" x-model="form.location" placeholder="Contoh: Ruang Rapat Lantai 2 / Zoom"
                                       class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Catatan / Keterangan (Opsional)</label>
                                <textarea name="description" x-model="form.description" rows="2" placeholder="Agenda singkat atau peserta..."
                                          class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"></textarea>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-lg border border-slate-300 text-xs text-slate-600">Tutup</button>
                                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold"
                                        x-text="editingId ? 'Simpan Perubahan' : 'Simpan Acara'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
