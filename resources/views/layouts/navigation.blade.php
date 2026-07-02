<nav x-data="{ open: false }" class="app-navbar">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="app-brand">
                        <img 
                            src="{{ asset('images/logo.png') }}" 
                            alt="{{ config('app.name') }}" 
                            class="app-brand-logo"
                        >

                        <span>
                            <span class="app-brand-text">
                                {{ config('app.name', 'IT Inventory') }}
                            </span>
                            <span class="app-brand-subtitle">
                                Asset Management
                            </span>
                        </span>
                    </a>
                </div>

                <!-- LINKS DE NAVEGACIÓN PARA DASHBOARD -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="app-nav-link">
                {{ __('Dashboard') }}
                </x-nav-link>

                <x-nav-link :href="route('inventory')" :active="request()->routeIs('inventory')" class="app-nav-link">
                {{ __('Inventory') }}
                </x-nav-link>

            @if (Auth::user()->user_level === 'Admin') <!-- ONLY SHOW BUTTON IF THE USER IS ADMIN LEVEL -->
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')" class="app-nav-link">
                {{ __('Users') }}
                </x-nav-link>
            @endif

            @if (Auth::user()->user_level !== 'Read') <!-- ONLY SHOW BUTTON IF THE USER IS NOT READ LEVEL -->
                <x-nav-link :href="route('logs')" :active="request()->routeIs('logs')" class="app-nav-link">
                {{ __('Logs') }}
                </x-nav-link>
            @endif

                </div>
            </div>

            <!-- User actions -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 app-user-actions">

                <!-- Profile button -->
                <a href="{{ route('profile.edit') }}"
                class="app-profile-button {{ request()->routeIs('profile.edit') ? 'is-active' : '' }}"
                title="{{ __('Open profile') }}">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="app-user-action-icon"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0" />
                    </svg>

                    <span>{{ Auth::user()->name }}</span>
                </a>

                <!-- Logout button -->
                <form method="POST"
                    action="{{ route('logout') }}"
                    class="app-logout-form">
                    @csrf

                    <button type="submit"
                            class="app-logout-button"
                            title="{{ __('Log Out') }}">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="app-user-action-icon"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3-3H9m9.75 0l-3-3m3 3l-3 3" />
                        </svg>

                        <span>{{ __('Log Out') }}</span>
                    </button>
                </form>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

<!-- Responsive Navigation Menu -->
<div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
    <div class="pt-2 pb-3 space-y-1">

        <!-- Todos los niveles pueden ver Dashboard -->
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            {{ __('Dashboard') }}
        </x-responsive-nav-link>

        <!-- Todos los niveles pueden ver Inventory -->
        <x-responsive-nav-link :href="route('inventory')" :active="request()->routeIs('inventory')">
            {{ __('Inventory') }}
        </x-responsive-nav-link>

        <!-- Solo Admin puede ver Users -->
        @if (Auth::user()->user_level === 'Admin')
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
                {{ __('Users') }}
            </x-responsive-nav-link>
        @endif

        <!-- Admin y User pueden ver Logs. Read no. -->
        @if (Auth::user()->user_level !== 'Read')
            <x-responsive-nav-link :href="route('logs')" :active="request()->routeIs('logs')">
                {{ __('Logs') }}
            </x-responsive-nav-link>
        @endif

    </div>

    <!-- Responsive Settings Options -->
    <div class="pt-4 pb-1 border-t border-gray-200">
        <div class="px-4">
            <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->employee_number }}</div>
        </div>

        <div class="mt-3 space-y-1">
            <x-responsive-nav-link :href="route('profile.edit')">
                {{ __('Profile') }}
            </x-responsive-nav-link>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                    this.closest('form').submit();">
                    {{ __('Log Out') }}
                </x-responsive-nav-link>
            </form>
        </div>
    </div>
</div>
</nav>
