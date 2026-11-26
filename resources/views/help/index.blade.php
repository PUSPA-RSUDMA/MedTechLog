<x-app-layout>
    <x-slot name="title">Panduan Penggunaan (Help)</x-slot>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-900">Panduan Singkat MedTechLog</h1>
            <p class="text-xs text-slate-500">Cara mudah menggunakan aplikasi sesuai peran Anda</p>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6" x-data="{ openFaq: 1 }">
        <div class="bg-gradient-to-r from-teal-700 to-sky-700 text-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-bold">3 Langkah Mudah Menggunakan MedTechLog</h2>
            <p class="text-sm text-teal-50 mt-1">Aplikasi ini dirancang sederhana agar tim cukup fokus pada input tugas, update ketercapaian (berdasarkan Unit &amp; PIC), serta pantau analisis &amp; ekspor laporan.</p>
        </div>

        {{-- Penjelasan Menu Ringkas --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900">1. Fungsi Menu Utama</h3>
            </div>
            <div class="divide-y divide-slate-100 text-xs text-slate-700">
                <div class="px-6 py-3.5 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                    <span class="font-bold text-teal-800 sm:w-40 shrink-0">Dashboard</span>
                    <span>Ringkasan total tugas, tugas selesai, rata-rata ketercapaian per Unit, dan tugas mendekati tenggat.</span>
                </div>
                <div class="px-6 py-3.5 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                    <span class="font-bold text-teal-800 sm:w-40 shrink-0">Daftar Tugas</span>
                    <span>Tempat membuat tugas baru (tentukan Unit &amp; PIC, tanggal, dan target output) serta meng-<strong>Update Ketercapaian</strong> langsung di halaman detail tugas.</span>
                </div>
                <div class="px-6 py-3.5 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                    <span class="font-bold text-sky-700 sm:w-40 shrink-0">Verifikasi <em class="font-normal text-slate-400">(Supervisor/Admin)</em></span>
                    <span>Memeriksa tugas staf yang sudah selesai lalu memilih <strong>Setujui</strong>, <strong>Minta Revisi</strong>, atau <strong>Tolak</strong>.</span>
                </div>
                <div class="px-6 py-3.5 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                    <span class="font-bold text-teal-800 sm:w-40 shrink-0">Analisis &amp; Laporan</span>
                    <span>Rekap ketercapaian per <strong>Unit</strong> dan per <strong>PIC</strong>, filter data dengan mudah, lalu unduh laporan <strong>PDF</strong> atau <strong>Excel (.xlsx)</strong>.</span>
                </div>
                <div class="px-6 py-3.5 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                    <span class="font-bold text-purple-800 sm:w-40 shrink-0">Pengaturan <em class="font-normal text-slate-400">(Khusus Admin)</em></span>
                    <span>Kelola daftar <strong>Unit / Divisi</strong> dan akun <strong>Pengguna &amp; PIC</strong> (Admin, Supervisor, Staf).</span>
                </div>
            </div>
        </div>

        {{-- 3 Peran Pengguna --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm">
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-800 font-semibold mb-2">Peran 1: Staf / PIC</span>
                <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                    <li>Melihat &amp; membuat tugas sendiri.</li>
                    <li>Klik <strong>Update / Detail</strong> pada tugas untuk mengisi tanggal update, tambahan ketercapaian, dan catatan progres.</li>
                    <li>Pilih status <em>Selesai &amp; Ajukan Verifikasi</em> saat tugas tuntas.</li>
                    <li>Melihat analisis &amp; ekspor laporan pribadi.</li>
                </ul>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm">
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800 font-semibold mb-2">Peran 2: Supervisor</span>
                <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                    <li>Membuat dan menugaskan tugas ke PIC di unitnya.</li>
                    <li>Memantau ketercapaian seluruh tim di Dashboard &amp; Daftar Tugas.</li>
                    <li>Menyetujui atau meminta revisi di menu <strong>Verifikasi</strong>.</li>
                    <li>Melihat analisis &amp; ekspor laporan unit.</li>
                </ul>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-sm">
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 font-semibold mb-2">Peran 3: Administrator</span>
                <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                    <li>Mengelola <strong>Unit / Divisi</strong> dan <strong>Pengguna &amp; PIC</strong>.</li>
                    <li>Akses penuh ke seluruh tugas, verifikasi, dan analisis lintas unit.</li>
                    <li>Mengekspor laporan PDF &amp; Excel seluruh organisasi.</li>
                </ul>
            </div>
        </div>

        {{-- FAQ Singkat --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900">2. Pertanyaan Umum</h3>
            </div>
            <div class="divide-y divide-slate-100 text-sm">
                <div>
                    <button type="button" @click="openFaq = openFaq === 1 ? null : 1" class="w-full px-6 py-3.5 text-left flex items-center justify-between font-semibold text-slate-800 hover:bg-slate-50">
                        <span>Bagaimana cara staf meng-update ketercapaian tugas?</span>
                        <span class="text-slate-400" x-text="openFaq === 1 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 1" class="px-6 pb-4 text-xs text-slate-600">
                        Buka <strong>Daftar Tugas</strong> &rarr; klik tombol <strong>Update / Detail</strong> pada tugas Anda &rarr; isi <strong>Tanggal Update</strong>, <strong>Tambahan Ketercapaian</strong>, <strong>Status</strong>, dan <strong>Catatan Progres</strong> langsung di kotak <em>Update Ketercapaian Tugas</em> lalu klik <strong>Simpan</strong>.
                    </div>
                </div>
                <div>
                    <button type="button" @click="openFaq = openFaq === 2 ? null : 2" class="w-full px-6 py-3.5 text-left flex items-center justify-between font-semibold text-slate-800 hover:bg-slate-50">
                        <span>Apakah perlu mengisi jam kerja atau mengunggah file?</span>
                        <span class="text-slate-400" x-text="openFaq === 2 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 2" x-cloak class="px-6 pb-4 text-xs text-slate-600">
                        Tidak perlu. Cukup isi <strong>tanggal</strong> dan <strong>ketercapaian</strong> saja tanpa input jam dan tanpa unggah dokumen.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
