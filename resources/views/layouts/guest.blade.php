<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — MedTechLog</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#FAF5F6] text-[#331F24]">
    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10 bg-gradient-to-br from-[#FAF5F6] via-[#F2E3E7]/70 to-[#FAF5F6]">
        <div class="mb-6 text-center">
            <a href="/" class="inline-flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-[#CB4D6D] flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2h-3.5l-1-1h-5l-1 1H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <span class="text-2xl font-bold tracking-tight text-[#331F24]">MedTech<span class="text-[#CB4D6D]">Log</span></span>
                    <p class="text-xs text-[#75565E]">Sistem Pencatatan Pekerjaan & Evaluasi Kinerja</p>
                </div>
            </a>
        </div>

        <div class="w-full sm:max-w-md bg-white border border-[#E5CDD3] shadow-sm rounded-2xl px-6 py-7">
            {{ $slot }}
        </div>

        <p class="mt-6 text-xs text-slate-400">&copy; {{ date('Y') }} MedTechLog — Tim Internal Operasional & Teknologi Medis</p>
    </div>
</body>
</html>
