<x-app-layout>
    <x-slot name="title">Notifikasi</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Pusat Notifikasi</h1>
            <p class="text-xs text-slate-500">Pemberitahuan penugasan, pengajuan verifikasi, hasil review, dan pengingat tenggat</p>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-slate-600">Belum dibaca: <strong class="text-rose-600">{{ $unreadCount }}</strong></p>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold">
                        Tandai Semua Sudah Dibaca
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm divide-y divide-slate-100">
            @forelse($notifications as $notification)
                <div class="p-4 flex items-start justify-between gap-4 {{ $notification->read_at === null ? 'bg-teal-50/40' : '' }}">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            @if($notification->read_at === null)
                                <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                            @endif
                            <p class="text-sm font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Notifikasi Sistem' }}</p>
                        </div>
                        <p class="text-sm text-slate-600">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                    </div>
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-xs font-medium text-slate-700 whitespace-nowrap">
                            {{ $notification->read_at === null ? 'Buka & Tandai Dibaca' : 'Lihat' }}
                        </button>
                    </form>
                </div>
            @empty
                <div class="p-10 text-center text-sm text-slate-400">Belum ada notifikasi.</div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
    </div>
</x-app-layout>
