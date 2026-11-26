<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}MedTechLog</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-slate-50 text-slate-800" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">
@php
    $authUser = auth()->user();
    $unreadNotificationsCount = $authUser ? $authUser->unreadNotifications()->count() : 0;
@endphp

<div class="min-h-screen flex">
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="[
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
        ]"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-100 flex flex-col transition-all duration-200 ease-in-out lg:static lg:inset-auto">

        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-teal-500 to-sky-600 flex items-center justify-center shrink-0 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2h-3.5l-1-1h-5l-1 1H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div x-show="!sidebarCollapsed" class="truncate">
                    <span class="font-bold text-base tracking-tight text-white">MedTech<span class="text-teal-400">Log</span></span>
                    <p class="text-[11px] text-slate-400 leading-none mt-0.5">Panel Admin Internal</p>
                </div>
            </a>
            <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:inline-flex p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800" title="Perkecil / Perluas Sidebar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h16"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">
            @php
                $navClass = fn (bool $active) => $active
                    ? 'bg-teal-600/20 text-teal-300 border-l-2 border-teal-400 font-medium'
                    : 'text-slate-300 hover:bg-slate-800/80 hover:text-white';
            @endphp

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ $navClass(request()->routeIs('dashboard')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
            </a>

            <div x-show="!sidebarCollapsed" class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Daftar Tugas</div>

            <a href="{{ route('tasks.it') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ $navClass(request()->routeIs('tasks.it') || (request()->routeIs('tasks.*') && request('department') === 'it')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Tugas IT</span>
            </a>

            <a href="{{ route('tasks.rm') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ $navClass(request()->routeIs('tasks.rm') || (request()->routeIs('tasks.*') && request('department') === 'rm')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Tugas RM</span>
            </a>

            <a href="{{ route('diklat.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ $navClass(request()->routeIs('diklat.*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Tugas Diklat</span>
            </a>

            <div x-show="!sidebarCollapsed" class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Agenda &amp; Otomasi</div>

            <a href="{{ route('logbook.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ $navClass(request()->routeIs('logbook.*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Logbook Acara</span>
            </a>

            <a href="{{ route('wa-manager.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ $navClass(request()->routeIs('wa-manager.*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">WA Manager</span>
            </a>

            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ $navClass(request()->routeIs('reports.*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Analisis &amp; Laporan</span>
            </a>

            <div x-show="!sidebarCollapsed" class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Master Data</div>

            <a href="{{ route('master.units.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ $navClass(request()->routeIs('master.units.*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Master Unit PIC</span>
            </a>

            <a href="{{ route('master.pics.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ $navClass(request()->routeIs('master.pics.*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Master PIC</span>
            </a>

            <a href="{{ route('master.diklat-units.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ $navClass(request()->routeIs('master.diklat-units.*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Master Unit Diklat</span>
            </a>

            <a href="{{ route('master.professions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ $navClass(request()->routeIs('master.professions.*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span x-show="!sidebarCollapsed" class="truncate">Master Profesi</span>
            </a>
        </nav>

        <div class="p-3 border-t border-slate-800 text-xs text-slate-400">
            <div x-show="!sidebarCollapsed" class="flex items-center justify-between">
                <span>MedTechLog</span>
                <span class="inline-flex items-center gap-1 text-teal-400">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span> Admin
                </span>
            </div>
        </div>
    </aside>

    {{-- Main Wrapper --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-20">
            <div class="flex items-center gap-3 min-w-0">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="truncate">
                    @isset($header)
                        {{ $header }}
                    @else
                        <h1 class="text-lg font-semibold text-slate-800 truncate">{{ $title ?? 'MedTechLog' }}</h1>
                    @endisset
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Notification Bell --}}
                <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition" title="Notifikasi & Reminder">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($unreadNotificationsCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-rose-600 rounded-full">
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                {{-- Profile Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2.5 pl-2 pr-2.5 py-1.5 rounded-lg hover:bg-slate-100 transition">
                        <div class="w-8 h-8 rounded-full bg-teal-700 text-white font-semibold text-xs flex items-center justify-center">
                            {{ strtoupper(substr($authUser->name, 0, 2)) }}
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-xs font-semibold text-slate-800 leading-tight">{{ $authUser->name }}</p>
                            <p class="text-[11px] text-slate-500 leading-tight">Administrator</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-cloak @click.outside="open = false"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 z-50 text-sm">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="font-semibold text-slate-800 truncate">{{ $authUser->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $authUser->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50">Profil Saya</a>
                        <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50">Notifikasi ({{ $unreadNotificationsCount }})</a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-1 pt-1">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 font-medium">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        <div class="px-4 lg:px-6 pt-4">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-4 flex items-center justify-between px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-sm">
                    <span>{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 font-bold">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show"
                     class="mb-4 flex items-center justify-between px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" class="text-rose-600 hover:text-rose-800 font-bold">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                    <p class="font-semibold mb-1">Terdapat kesalahan pada input:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <main class="flex-1 px-4 lg:px-6 pb-8">
            {{ $slot }}
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
