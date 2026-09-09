<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Badges') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Permissions and responsibilities assigned to your account.') }}
        </p>
    </header>

    @php
        $activeBadges = $user->badges
            ->filter(function ($badge) {
                return $badge->pivot->is_active;
            });
    @endphp

    <div class="mt-6">

        @if ($activeBadges->isEmpty())

            <p class="text-sm text-gray-500">
                No badges assigned.
            </p>

        @else

            <div class="profile-badge-list">

                @foreach ($activeBadges as $badge)

                    @php
                        $badgeLabel = match ($badge->slug) {
                            'maintenance_management' => 'MM',
                            'inventory_deletion' => 'ID',
                            'it_room_responsible' => 'IT',
                            default => strtoupper(
                                collect(explode(' ', $badge->name))
                                    ->map(fn ($word) => substr($word, 0, 1))
                                    ->take(2)
                                    ->implode('')
                            ),
                        };

                        $badgeClass = match ($badge->slug) {
                            'maintenance_management' => 'badge-maintenance',
                            'inventory_deletion' => 'badge-deletion',
                            'it_room_responsible' => 'badge-it-room',
                            default => 'badge-default',
                        };

                        $badgeDescription = match ($badge->slug) {
                            'maintenance_management' =>
                                'Allows maintenance management and assignment.',

                            'inventory_deletion' =>
                                'Allows permanent inventory deletion.',

                            'it_room_responsible' =>
                                $badge->pivot->plant
                                    ? 'IT Room responsible for Plant ' . $badge->pivot->plant . '.'
                                    : 'IT Room responsibility.',

                            default =>
                                $badge->description ?: 'Additional account permission.',
                        };
                    @endphp

                    <div class="profile-badge-item">

                        <span
                            class="user-table-badge-icon {{ $badgeClass }}"
                            title="{{ $badge->name }}"
                        >
                            {{ $badgeLabel }}
                        </span>

                        <div class="profile-badge-content">

                            <span class="profile-badge-name">
                                {{ $badge->name }}
                            </span>

                            <span class="profile-badge-separator">
                                —
                            </span>

                            <span class="profile-badge-description">
                                {{ $badgeDescription }}
                            </span>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>
</section>