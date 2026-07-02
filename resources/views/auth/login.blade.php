@section('title', 'Login - ' . config('app.name'))

<x-guest-layout>
    <div class="auth-login-card">
        <!-- Brand / Logo -->
        <div class="auth-login-brand">
            <img 
                src="{{ asset('images/logo.png') }}" 
                alt="{{ config('app.name') }}" 
                class="auth-login-logo"
            >

            <h1 class="auth-login-title">
                {{ config('app.name', 'IT Inventory') }}
            </h1>

            <p class="auth-login-subtitle">
                Access your asset management platform
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="auth-login-form">
            @csrf

            <!-- EMPLOYEE NUMBER -->
            <div class="auth-field">
                <x-input-label for="employee_number" :value="__('Employee Number')" />

                <x-text-input 
                    id="employee_number" 
                    class="block mt-1 w-full auth-input" 
                    type="text" 
                    name="employee_number" 
                    :value="old('employee_number')" 
                    required 
                    autofocus 
                    autocomplete="username" 
                />

                <x-input-error :messages="$errors->get('employee_number')" class="mt-2" />
            </div>

            <!-- PASSWORD AND SHOW/HIDE TOGGLE -->
            <div class="auth-field mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <div class="relative mt-1">
                    <x-text-input 
                        id="password" 
                        class="block w-full pr-20 auth-input"
                        type="password"
                        name="password"
                        required 
                        autocomplete="current-password" 
                    />

                    <button 
                        type="button"
                        id="togglePassword"
                        class="auth-password-toggle"
                    >
                        Show
                    </button>
                </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- REMEMBER ME -->
            <div class="auth-remember block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input 
                        id="remember_me" 
                        type="checkbox" 
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                        name="remember"
                    >

                    <span class="ms-2 text-sm text-gray-600">
                        {{ __('Remember me') }}
                    </span>
                </label>
            </div>

            <!-- LOGIN BUTTON -->
            <div class="flex items-center justify-end mt-4">
                <x-primary-button class="auth-login-button">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <!-- SCRIPT FOR TOGGLE PASSWORD VISIBILITY -->
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';

                passwordInput.type = isPassword ? 'text' : 'password';
                togglePassword.textContent = isPassword ? 'Hide' : 'Show';
            });
        }
    </script>
</x-guest-layout>