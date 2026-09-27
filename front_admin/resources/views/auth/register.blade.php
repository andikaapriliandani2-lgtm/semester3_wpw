<x-guest-layout>
    <div class="mb-8">
        <div class="mb-6 flex rounded-2xl bg-slate-100 p-1 text-sm font-semibold">
            <a href="{{ route('login') }}" class="flex-1 rounded-xl px-4 py-2.5 text-center text-slate-500 transition hover:text-[#173f35]">{{ __('Log in') }}</a>
            <a href="{{ route('register') }}" class="flex-1 rounded-xl bg-white px-4 py-2.5 text-center text-[#173f35] shadow-sm">{{ __('Register') }}</a>
        </div>
        <p class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-[#d79519]">Mulai bersama tim</p>
        <h1 class="font-display text-3xl font-bold tracking-tight text-[#173f35]">Buat akun baru.</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Bergabung ke workspace minimarket Anda.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" class="text-slate-600" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" class="text-slate-600" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" class="text-slate-600" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" class="text-slate-600" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4 bg-[#173f35] hover:bg-[#286b59] focus:bg-[#286b59] active:bg-[#102e27]">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
