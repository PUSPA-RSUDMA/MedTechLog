<x-app-layout>
    <x-slot name="title">WA Manager</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">WA Manager (WhatsApp Web &amp; Auto-Reminder)</h1>
            <p class="text-xs text-slate-500">Hubungkan WhatsApp Web Gateway untuk pengiriman otomatis pengingat Tugas (H-2 &amp; H-1) dan Logbook Acara (H-1)</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Status & QR Pairing Panel --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Status Koneksi WA Web</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $gatewayStatus['connected'] ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                    {{ $gatewayStatus['connected'] ? 'Terhubung' : 'Belum Terhubung' }}
                </span>
            </div>

            @if($gatewayStatus['connected'])
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900">
                    <p class="font-bold">WhatsApp Web Aktif &amp; Siap Mengirim Pesan Otomatis!</p>
                    @if($gatewayStatus['phone'])
                        <p class="mt-1">Nomor terhubung: <strong class="font-mono">{{ $gatewayStatus['phone'] }}</strong></p>
                    @endif
                </div>
            @elseif($gatewayStatus['qr'])
                <div class="text-center p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <p class="text-xs font-semibold text-slate-700 mb-2">Scan QR Code ini di WhatsApp (Perangkat Tertaut):</p>
                    <img src="{{ $gatewayStatus['qr'] }}" alt="WA Web QR" class="w-48 h-48 mx-auto rounded-lg border border-slate-200">
                </div>
            @else
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                    <p class="font-semibold text-slate-800">{{ $gatewayStatus['error'] ?? 'Menunggu koneksi WA Web Gateway.' }}</p>
                    <p>Hubungkan layanan WA Web Bridge (seperti <code>whatsapp-web.js</code> / Baileys) pada URL gateway di samping, atau gunakan tombol <strong>Buka WA Web</strong> pada tabel riwayat pesan untuk kirim cepat 1-klik.</p>
                </div>
            @endif

            <div class="grid grid-cols-3 gap-2 text-center text-xs pt-2 border-t border-slate-100">
                <div class="p-2.5 rounded-lg bg-emerald-50">
                    <span class="block text-emerald-700 font-bold text-base">{{ $stats['sent'] }}</span>
                    <span class="text-slate-500">Terkirim</span>
                </div>
                <div class="p-2.5 rounded-lg bg-amber-50">
                    <span class="block text-amber-700 font-bold text-base">{{ $stats['pending'] }}</span>
                    <span class="text-slate-500">Antrean</span>
                </div>
                <div class="p-2.5 rounded-lg bg-rose-50">
                    <span class="block text-rose-700 font-bold text-base">{{ $stats['failed'] }}</span>
                    <span class="text-slate-500">Gagal</span>
                </div>
            </div>

            <form method="POST" action="{{ route('wa-manager.run-reminders') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold">
                    Jalankan Pengecekan Reminder (H-2 &amp; H-1) Sekarang
                </button>
            </form>
        </div>

        {{-- Gateway Configuration --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4">Pengaturan WA Web &amp; Auto-Reminder</h2>
            <form method="POST" action="{{ route('wa-manager.settings') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">URL WA Web Gateway *</label>
                    <input type="url" name="gateway_url" value="{{ old('gateway_url', $setting->gateway_url) }}" required placeholder="http://127.0.0.1:3001"
                           class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">API Key / Token (Opsional)</label>
                    <input type="text" name="api_key" value="{{ old('api_key', $setting->api_key) }}" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nomor WA Default Admin (untuk Logbook &amp; Fallback)</label>
                    <input type="text" name="default_target_phone" value="{{ old('default_target_phone', $setting->default_target_phone) }}" placeholder="081234567890"
                           class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div class="space-y-2 pt-1 text-xs text-slate-700">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_enabled" value="1" @checked($setting->is_enabled) class="rounded border-slate-300 text-teal-700">
                        <span>Aktifkan Pengiriman Otomatis via WA Gateway</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="auto_remind_tasks" value="1" @checked($setting->auto_remind_tasks) class="rounded border-slate-300 text-teal-700">
                        <span>Auto-Reminder Tugas Mendekati Tenggat (H-2 &amp; H-1)</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="auto_remind_logbook" value="1" @checked($setting->auto_remind_logbook) class="rounded border-slate-300 text-teal-700">
                        <span>Auto-Reminder Acara Logbook (H-1)</span>
                    </label>
                </div>
                <button type="submit" class="w-full py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold">
                    Simpan Pengaturan
                </button>
            </form>
        </div>

        {{-- Send Test / Manual Chat --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4">Kirim Pesan WA Manual / Uji Coba</h2>
            <form method="POST" action="{{ route('wa-manager.send-test') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nomor WhatsApp Tujuan *</label>
                    <input type="text" name="phone" value="{{ old('phone', $setting->default_target_phone) }}" required placeholder="081234567890"
                           class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Isi Pesan *</label>
                    <textarea name="message" rows="5" required placeholder="Halo, ini pesan uji coba dari MedTechLog WA Manager..."
                              class="w-full rounded-lg border-slate-300 text-sm">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="w-full py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold">
                    Kirim Pesan WhatsApp
                </button>
            </form>
        </div>
    </div>

    {{-- Message History / Queue Table --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Riwayat &amp; Antrean Pesan WhatsApp Otomatis</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu &amp; Jenis</th>
                        <th class="px-4 py-3 text-left">Tujuan</th>
                        <th class="px-4 py-3 text-left">Isi Pesan</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($messages as $m)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-semibold text-slate-800">{{ $m->category_label }}</p>
                                <p class="text-[11px] text-slate-400">{{ $m->created_at?->format('d M Y H:i') }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-semibold text-slate-800">{{ $m->recipient_name ?? '-' }}</p>
                                <p class="font-mono text-slate-500">{{ $m->recipient_phone }}</p>
                            </td>
                            <td class="px-4 py-3 max-w-md">
                                <p class="whitespace-pre-line text-slate-700">{{ $m->message }}</p>
                                @if($m->error_message)
                                    <p class="text-[11px] text-rose-600 mt-1">{{ $m->error_message }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($m->status === 'sent')
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">Terkirim</span>
                                @elseif($m->status === 'failed')
                                    <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-semibold">Gagal</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-semibold">Antrean</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ $m->wa_web_url }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold">
                                    Buka WA Web
                                </a>
                                @if($m->status !== 'sent')
                                    <form method="POST" action="{{ route('wa-manager.retry', $m) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sky-700 hover:underline font-semibold">Kirim Ulang</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada riwayat pesan WhatsApp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $messages->links() }}</div>
        @endif
    </div>
</x-app-layout>
