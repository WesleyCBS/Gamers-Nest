<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Senha')" class="text-gray-600" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Lembre-me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                    {{ __('Esqueceu a senha?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="w-full mt-6 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold uppercase text-xs tracking-widest py-3.5 rounded-xl shadow-lg shadow-indigo-200 transition-all">
            {{ __('Entrar') }}
        </button>

        <div class="flex items-center gap-3 my-6">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-xs text-gray-400 uppercase tracking-wider">Ainda não tem conta?</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <a href="{{ route('register') }}" class="block w-full text-center border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white font-bold uppercase text-xs tracking-widest py-3 rounded-xl transition-all">
            {{ __('Criar conta') }}
        </a>
</x-guest-layout>
