<x-app-layout>
    <x-slot name="title">Tambah Target Kerja</x-slot>
    <x-slot name="header">
        <h1 class="text-lg font-bold text-slate-900">Tambah Target Kerja Baru</h1>
    </x-slot>

    <div class="max-w-4xl bg-white rounded-xl border border-slate-200/80 p-6 shadow-sm">
        <form method="POST" action="{{ route('targets.store') }}" class="space-y-6">
            @csrf
            @include('targets._form')
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('targets.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold">Simpan Target</button>
            </div>
        </form>
    </div>
</x-app-layout>
