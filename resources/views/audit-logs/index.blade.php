<x-app-layout>
    <x-slot name="title">Audit Log</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Audit Log Sistem</h1>
            <p class="text-xs text-slate-500">Rekam jejak perubahan penting pada Target, Tugas, Work Log, dan Verifikasi</p>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200/80 p-4 mb-5 shadow-sm">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Pengguna</label>
                <select name="user_id" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Pengguna</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Aksi</label>
                <select name="action" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Aksi</option>
                    <option value="created" @selected(request('action') === 'created')>Dibuat</option>
                    <option value="updated" @selected(request('action') === 'updated')>Diperbarui</option>
                    <option value="deleted" @selected(request('action') === 'deleted')>Dihapus</option>
                    <option value="status_changed" @selected(request('action') === 'status_changed')>Status Berubah</option>
                    <option value="reviewed" @selected(request('action') === 'reviewed')>Diverifikasi</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Entitas</label>
                <select name="model" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Entitas</option>
                    <option value="Target" @selected(request('model') === 'Target')>Target</option>
                    <option value="Task" @selected(request('model') === 'Task')>Task</option>
                    <option value="WorkLog" @selected(request('model') === 'WorkLog')>WorkLog</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-slate-800 text-white text-sm font-medium">Filter</button>
                <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 text-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">Pengguna</th>
                        <th class="px-4 py-3 text-left">Aksi & Entitas</th>
                        <th class="px-4 py-3 text-left">Nilai Sebelum</th>
                        <th class="px-4 py-3 text-left">Nilai Sesudah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                {{ $log->created_at?->format('d M Y H:i') }}
                                <p class="text-[10px] text-slate-400">{{ $log->ip_address }}</p>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-semibold">{{ $log->action_label }}</span>
                                <span class="text-slate-500 ml-1">{{ $log->model_name }} #{{ $log->auditable_id }}</span>
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                @if($log->old_values)
                                    <pre class="text-[11px] bg-slate-50 p-2 rounded overflow-x-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                @if($log->new_values)
                                    <pre class="text-[11px] bg-teal-50/50 p-2 rounded overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada catatan audit log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $logs->links() }}</div>
        @endif
    </div>
</x-app-layout>
