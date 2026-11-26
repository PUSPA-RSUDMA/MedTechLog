<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — SARTIKA</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#FAF5F6] text-[#331F24]">
    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-10 bg-gradient-to-br from-[#FAF5F6] via-[#F2E3E7]/60 to-[#FAF5F6]">
        <div class="w-full max-w-5xl bg-white rounded-[32px] shadow-xl shadow-[#331F24]/10 border border-[#E5CDD3]/80 p-4 sm:p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-center">
            {{-- Left Column: Cityscape Artwork with Organic Notched Frame --}}
            <div class="lg:col-span-6 relative hidden lg:block">
                <div class="relative h-[540px] w-full rounded-[28px] overflow-hidden bg-[#331F24]">
                    <img src="{{ asset('images/sartika-login-city.jpg') }}"
                         alt="SARTIKA Cityscape"
                         class="w-full h-full object-cover">

                    {{-- Subtle Rose/Plum Gradient Overlay for Readability --}}
                    <div class="absolute inset-0 bg-gradient-to-b from-[#331F24]/70 via-[#331F24]/15 to-[#331F24]/80"></div>

                    {{-- Organic Corner Notch Decorations (matching reference style) --}}
                    <div class="absolute top-0 left-0 w-16 h-16 bg-white rounded-br-[28px]"></div>
                    <div class="absolute bottom-0 right-0 w-16 h-16 bg-white rounded-tl-[28px]"></div>

                    {{-- Top Right Tagline --}}
                    <div class="absolute top-7 right-7 left-20 text-right">
                        <span class="inline-block px-3 py-1 rounded-full bg-[#CB4D6D]/90 text-white text-[11px] font-semibold tracking-wide mb-2">
                            SARTIKA
                        </span>
                        <p class="text-white text-sm font-semibold leading-relaxed drop-shadow">
                            Sistem Arsip, Rencana, Tugas, dan Integrasi Kelompok Kerja
                        </p>
                    </div>

                    {{-- Bottom Left Caption --}}
                    <div class="absolute bottom-7 left-7 right-20">
                        <p class="text-[#F2E3E7] text-xs font-medium leading-relaxed">
                            Kelola Tugas IT, Tugas RM, Tugas Diklat, Logbook Acara, dan Reminder WA dalam satu sistem terpadu.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right Column: Login Form --}}
            <div class="lg:col-span-6 px-2 sm:px-6 lg:px-8 py-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
