<x-guest-layout>
    {{-- Brand Logo & Name --}}
    <div class="flex flex-col items-center text-center mb-7">
        <div class="inline-flex items-center gap-2.5 mb-4">
            <div class="w-10 h-10 rounded-xl bg-[#331F24] flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-[#F2E3E7]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2h-3.5l-1-1h-5l-1 1H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="text-2xl font-extrabold tracking-tight text-[#331F24]">SAR<span class="text-[#CB4D6D]">TIKA</span></span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#331F24] tracking-tight">Selamat Datang</h1>
        <p class="text-xs text-[#75565E] mt-1.5 max-w-xs leading-relaxed">
            <strong>Sistem Arsip, Rencana, Tugas, dan Integrasi Kelompok Kerja</strong>
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPassword: false }">
        @csrf

        {{-- Email Field (soft filled style like reference) --}}
        <div class="rounded-xl bg-[#FAF5F6] border border-[#E5CDD3] px-4 py-2.5 focus-within:border-[#CB4D6D] focus-within:ring-2 focus-within:ring-[#CB4D6D]/20 transition">
            <label for="email" class="block text-[11px] font-medium text-[#75565E]">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="admin@medtechlog.local"
                   class="w-full bg-transparent border-0 p-0 mt-0.5 text-sm font-medium text-[#331F24] placeholder-[#9A7B83] focus:ring-0">
        </div>
        @error('email')
            <p class="text-xs text-[#CB4D6D] -mt-2">{{ $message }}</p>
        @enderror

        {{-- Password Field with Eye Toggle --}}
        <div class="rounded-xl bg-[#FAF5F6] border border-[#E5CDD3] px-4 py-2.5 focus-within:border-[#CB4D6D] focus-within:ring-2 focus-within:ring-[#CB4D6D]/20 transition">
            <label for="password" class="block text-[11px] font-medium text-[#75565E]">Kata Sandi</label>
            <div class="flex items-center gap-2 mt-0.5">
                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full bg-transparent border-0 p-0 text-sm font-medium text-[#331F24] placeholder-[#9A7B83] focus:ring-0">
                <button type="button" @click="showPassword = !showPassword" class="text-[#75565E] hover:text-[#331F24] shrink-0" title="Tampilkan / Sembunyikan">
                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
        </div>
        @error('password')
            <p class="text-xs text-[#CB4D6D] -mt-2">{{ $message }}</p>
        @enderror

        {{-- Remember & Forgot --}}
        <div class="flex items-center justify-between text-xs pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 text-[#5A3E46] cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-[#D4B4BD] text-[#CB4D6D] focus:ring-[#CB4D6D]">
                <span>Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-[#331F24] hover:text-[#CB4D6D] font-semibold underline underline-offset-2" href="{{ route('password.request') }}">Lupa Kata Sandi?</a>
            @endif
        </div>

        {{-- Primary Login Button --}}
        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#CB4D6D] hover:bg-[#B03C5A] text-white font-semibold text-sm shadow-md shadow-[#CB4D6D]/25 transition">
            Masuk
        </button>
    </form>

</x-guest-layout>
