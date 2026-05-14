<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- EMPLOYEE NUMBER -->
        <div>
        <x-input-label for="employee_number" :value="__('Employee Number')" />

        <x-text-input 
        id="employee_number" 
        class="block mt-1 w-full" 
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
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

          <div class="relative mt-1">
             <x-text-input 
                  id="password" 
                  class="block w-full pr-20"
                  type="password"
                  name="password"
                  required 
                  autocomplete="current-password" 
              />

              <button 
                  type="button"
                 id="togglePassword"
                 class="absolute inset-y-0 right-0 px-3 text-sm text-gray-600 hover:text-gray-900"
                >
                    Show
                </button>
        </div>

         <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- REMEMBER ME -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- LOGIN BUTTON -->
    <div class="flex items-center justify-end mt-4">
        <x-primary-button class="ms-3">
            {{ __('Log in') }}
     </x-primary-button>
    </div>

    </form>
</x-guest-layout>

    <!-- SCRIPT FOR TOGGLE PASSWORD VISIBILITY -->
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';
            togglePassword.textContent = isPassword ? 'Hide' : 'Show';
        });
    </script>