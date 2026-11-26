<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-lg font-bold text-slate-900">Masuk ke MedTechLog</h2>
        <p class="text-sm text-slate-500 mt-0.5">Silakan masuk menggunakan akun internal Anda.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="nama@medtechlog.local"
                   class="w-full rounded-lg border-slate-300 focus:border-teal-600 focus:ring-teal-600 text-sm">
            @error('email')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full rounded-lg border-slate-300 focus:border-teal-600 focus:ring-teal-600 text-sm">
            @error('password')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="inline-flex items-center gap-2 text-slate-600">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                <span>Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-teal-700 hover:text-teal-800 font-medium text-xs" href="{{ route('password.request') }}">Lupa kata sandi?</a>
            @endif
        </div>

        <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-semibold text-sm shadow-sm transition">
            Masuk
        </button>
    </form>

    <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
        <p class="font-semibold text-slate-700 mb-1">Akun Admin:</p>
        <p>Email: <code class="text-teal-700">admin@medtechlog.local</code> &bull; Sandi: <code class="px-1 py-0.5 bg-slate-100 rounded text-slate-800">password</code></p>
    </div>
</x-guest-layout>
