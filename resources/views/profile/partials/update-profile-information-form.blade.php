<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Account Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Review the information associated with your account.') }}
        </p>
    </header>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Employee Number -->
        <div>
            <x-input-label
                for="employee_number"
                :value="__('Employee Number')"
            />

            <x-text-input
                id="employee_number"
                type="text"
                class="mt-1 block w-full bg-gray-100 cursor-not-allowed"
                :value="$user->employee_number"
                readonly
            />
        </div>

        <!-- Employee Name -->
        <div>
            <x-input-label
                for="employee_name"
                :value="__('Employee Name')"
            />

            <x-text-input
                id="employee_name"
                type="text"
                class="mt-1 block w-full bg-gray-100 cursor-not-allowed"
                :value="$user->name"
                readonly
            />
        </div>

        <!-- Department / Area -->
        <div>
            <x-input-label
                for="department"
                :value="__('Department / Area')"
            />

            <x-text-input
                id="department"
                type="text"
                class="mt-1 block w-full bg-gray-100 cursor-not-allowed"
                :value="$user->department ?? 'N/A'"
                readonly
            />
        </div>

        <!-- User Level -->
        <div>
            <x-input-label
                for="user_level"
                :value="__('User Level')"
            />

            <x-text-input
                id="user_level"
                type="text"
                class="mt-1 block w-full bg-gray-100 cursor-not-allowed"
                :value="$user->user_level"
                readonly
            />
        </div>

    </div>
</section>