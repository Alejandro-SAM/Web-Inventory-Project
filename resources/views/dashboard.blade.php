<x-app-layout>
    <div class="dashboard-page py-6 scroll-smooth">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{--
                Dashboard hero.

                This card now contains:
                - Dashboard title
                - Current plant filter status
                - Plant checklist dropdown

                The color is dynamic and comes from the controller through:
                $dashboardThemeStyle
            --}}
            <div class="dashboard-hero" style="{{ $dashboardThemeStyle }}">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="dashboard-hero-title">
                            Inventory Dashboard
                        </h1>

                        <p class="dashboard-hero-subtitle">
                            Overview of IT assets, warranties, maintenance and plant distribution.
                        </p>
                    </div>

                    {{--
                        Plant checklist filter.

                        Important:
                        - The input name is plants[] because this is now a multiple filter.
                        - Reset does not submit selected values; it reloads the dashboard clean.
                        - Apply submits the checked plants through GET.
                    --}}
                    <form method="GET"
                          action="{{ route('dashboard') }}"
                          id="plantFilterForm"
                          class="dashboard-hero-filter">
                        <label class="dashboard-hero-filter-label">
                            Filter by Plant
                        </label>

                        <button type="button"
                                class="dashboard-plant-dropdown-button"
                                onclick="toggleDropdown('plantFilterDropdown')">
                            <span>{{ $selectedPlantLabel }}</span>
                            <span>▾</span>
                        </button>

                        <div id="plantFilterDropdown" class="dashboard-plant-dropdown-menu hidden">
                            <div class="dashboard-plant-checklist">
                                @foreach ($plants as $plant)
                                    <label class="dashboard-plant-check-item">
                                         <input type="checkbox"
                                                name="plants[]"
                                                value="{{ $plant }}"
                                                class="plant-filter-checkbox"
                                                onchange="submitPlantFilterOnChange()"
                                                @checked(in_array($plant, $selectedPlantsArray, true))>

                                        <span>{{ $plant }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="dashboard-plant-filter-actions">
                                {{--
                                    Reset reloads the dashboard without plants[] parameters.

                                    The controller interprets an empty filter as:
                                    "all plants selected".
                                --}}
                                <button type="button"
                                        class="dashboard-plant-filter-button primary"
                                        onclick="resetPlantFilter()">
                                    Reset filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{--
                Summary cards.

                These cards show the main inventory indicators for the selected dashboard scope.
            --}}
            <div class="dashboard-section">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="dashboard-kpi-card">
                        <p class="dashboard-kpi-label">Total Assets</p>
                        <h2 class="dashboard-kpi-value">{{ $totalAssets }}</h2>
                        <p class="dashboard-kpi-helper">
                            Registered IT assets
                        </p>
                    </div>

                    <div class="dashboard-kpi-card success">
                        <p class="dashboard-kpi-label">Active Assets</p>
                        <h2 class="dashboard-kpi-value">{{ $activeAssets }}</h2>
                        <p class="dashboard-kpi-helper">
                            Currently active assets
                        </p>
                    </div>

                    <a href="#upcoming-maintenance-section"
                        class="dashboard-kpi-card dashboard-action-card dashboard-kpi-link warning block">
                        <div class="dashboard-kpi-link-arrow">
                            ↓
                        </div>

                        <p class="dashboard-kpi-label">In Maintenance</p>
                        <h2 class="dashboard-kpi-value">{{ $maintenanceAssets }}</h2>
                        <p class="dashboard-kpi-helper">
                            Click to view related assets
                        </p>
                    </a>

                    <a href="#warranties-expiring-section"
                        class="dashboard-kpi-card dashboard-action-card dashboard-kpi-link danger block">
                        <div class="dashboard-kpi-link-arrow">
                            ↓
                        </div>

                        <p class="dashboard-kpi-label">Warranties Expiring Soon</p>
                        <h2 class="dashboard-kpi-value">{{ $warrantiesExpiringSoonCount }}</h2>
                        <p class="dashboard-kpi-helper">
                            Click to view related assets
                        </p>
                    </a>
                </div>
            </div>

            {{--
                Charts section.

                Changes included:
                - Assets by Plant is now a doughnut chart.
                - Assets by Category has a dropdown checklist filter.
                - Assets by State has a dropdown checklist filter.
                - Assets by Business Unit has a dropdown checklist filter.
                - Doughnut charts include custom legends below the chart.
            --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="dashboard-chart-card">
                    <h2 class="dashboard-chart-title">Assets by Plant</h2>
                    <p class="dashboard-chart-subtitle">
                        Distribution of assets by selected plants.
                    </p>

                    <div class="dashboard-chart-wrapper">
                        <canvas id="assetsByPlantChart"></canvas>
                    </div>

                    {{--
                        Custom legend for the plant doughnut chart.

                        JavaScript will fill this div with:
                        color + plant + asset count
                    --}}
                    <div id="assetsByPlantLegend" class="dashboard-doughnut-legend"></div>
                </div>

                <div class="dashboard-chart-card">
                    <div class="dashboard-chart-header">
                        <div>
                            <h2 class="dashboard-chart-title">Assets by Category</h2>
                            <p class="dashboard-chart-subtitle">
                                Asset distribution by registered category.
                            </p>
                        </div>

                        {{--
                            Dropdown checklist.

                            It hides/shows chart columns without reloading the page.
                        --}}
                        <div class="dashboard-chart-filter-dropdown">
                            <button type="button"
                                    class="dashboard-chart-filter-toggle"
                                    onclick="toggleDropdown('categoryChartFilters')">
                                Filter columns ▾
                            </button>

                            <div id="categoryChartFilters" class="dashboard-chart-filter-menu hidden"></div>
                        </div>
                    </div>

                    <div class="dashboard-chart-wrapper">
                        <canvas id="assetsByCategoryChart"></canvas>
                    </div>
                </div>

                <div class="dashboard-chart-card">
                    <div class="dashboard-chart-header">
                        <div>
                            <h2 class="dashboard-chart-title">Assets by State</h2>
                            <p class="dashboard-chart-subtitle">
                                Current status of the selected asset scope.
                            </p>
                        </div>

                        {{--
                            Dropdown checklist for states.

                            This was added so Assets by State behaves like
                            Category and Business Unit.
                        --}}
                        <div class="dashboard-chart-filter-dropdown">
                            <button type="button"
                                    class="dashboard-chart-filter-toggle"
                                    onclick="toggleDropdown('stateChartFilters')">
                                Filter columns ▾
                            </button>

                            <div id="stateChartFilters" class="dashboard-chart-filter-menu hidden"></div>
                        </div>
                    </div>

                    <div class="dashboard-chart-wrapper">
                        <canvas id="assetsByStateChart"></canvas>
                    </div>

                    {{--
                        Custom legend for the state doughnut chart.
                    --}}
                    <div id="assetsByStateLegend" class="dashboard-doughnut-legend"></div>
                </div>

                <div class="dashboard-chart-card">
                    <div class="dashboard-chart-header">
                        <div>
                            <h2 class="dashboard-chart-title">Assets by Business Unit</h2>
                            <p class="dashboard-chart-subtitle">
                                Asset distribution by business unit.
                            </p>
                        </div>

                        {{--
                            Dropdown checklist for Business Unit.
                        --}}
                        <div class="dashboard-chart-filter-dropdown">
                            <button type="button"
                                    class="dashboard-chart-filter-toggle"
                                    onclick="toggleDropdown('businessUnitChartFilters')">
                                Filter columns ▾
                            </button>

                            <div id="businessUnitChartFilters" class="dashboard-chart-filter-menu hidden"></div>
                        </div>
                    </div>

                    <div class="dashboard-chart-wrapper">
                        <canvas id="assetsByBusinessUnitChart"></canvas>
                    </div>
                </div>
            </div>

            {{--
                Operational tables.

                These tables show assets that may require action soon.
            --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{--
                    Warranties expiring soon section.

                    This section is intentionally simplified to show only:
                    - IT Number
                    - Remaining warranty time
                    - Details button
                --}}
                <div id="warranties-expiring-section" class="dashboard-table-card scroll-mt-32">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Warranties Expiring Soon</h2>
                            <p class="text-xs text-gray-500">
                                Showing assets expiring within the next 14 days
                            </p>
                        </div>

                        <select id="warrantyRangeFilter"
                                onchange="handleWarrantyRangeFilter(this.value)"
                                class="text-xs rounded border-gray-300">
                            <option value="14">View within 14 days</option>
                            <option value="3months">View within 1 to 3 months</option>
                            <option value="all">View all</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">IT Number</th>
                                    <th class="text-left py-2">Time Left</th>
                                    <th class="text-left py-2">Details</th>
                                </tr>
                            </thead>
                            <tbody id="warrantyRows14Days">
                                @forelse ($warrantiesExpiringSoon as $asset)
                                    @php
                                        /*
                                            Calculate how many days remain before the warranty expires.
                                            If the value is negative, it means the warranty already expired.
                                        */
                                        $daysLeft = now()
                                            ->startOfDay()
                                            ->diffInDays(\Carbon\Carbon::parse($asset->warranty_expiry_date)->startOfDay(), false);
                                    @endphp

                                    <tr class="border-b">
                                        <td class="py-2 font-medium">
                                            {{ $asset->it_internal_number }}
                                        </td>

                                        <td class="py-2">
                                            @if ($daysLeft > 1)
                                                {{ $daysLeft }} days left
                                            @elseif ($daysLeft === 1)
                                                1 day left
                                            @elseif ($daysLeft === 0)
                                                Expires today
                                            @else
                                                Expired
                                            @endif
                                        </td>

                                        <td class="py-2">
                                            <button type="button"
                                                    onclick="document.getElementById('warranty-details-{{ $asset->id }}').showModal()"
                                                    class="px-3 py-1 bg-gray-800 text-white rounded text-xs hover:bg-gray-700">
                                                View Details
                                            </button>

                                            {{--
                                                Native HTML modal.

                                                This avoids adding extra JavaScript libraries.
                                                It shows the most relevant asset and warranty information.
                                            --}}
                                            <dialog id="warranty-details-{{ $asset->id }}" class="rounded-lg shadow-xl p-0 w-full max-w-2xl">
                                                <div class="p-5">
                                                    <div class="flex justify-between items-center mb-4">
                                                        <h3 class="text-lg font-semibold">
                                                            Asset Warranty Details
                                                        </h3>

                                                        <button type="button"
                                                                onclick="document.getElementById('warranty-details-{{ $asset->id }}').close()"
                                                                class="text-gray-500 hover:text-gray-800 text-xl">
                                                            &times;
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                        <div>
                                                            <p class="text-gray-500">IT Number</p>
                                                            <p class="font-medium">{{ $asset->it_internal_number }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Serial Number</p>
                                                            <p class="font-medium">{{ $asset->serial_number ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Asset Number</p>
                                                            <p class="font-medium">{{ $asset->asset_number ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Category</p>
                                                            <p class="font-medium">{{ $asset->category ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Brand</p>
                                                            <p class="font-medium">{{ $asset->brand ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Model</p>
                                                            <p class="font-medium">{{ $asset->model ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Plant</p>
                                                            <p class="font-medium">{{ $asset->plant ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Business Unit</p>
                                                            <p class="font-medium">{{ $asset->business_unit ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">End User</p>
                                                            <p class="font-medium">{{ $asset->end_user ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Purchase Origin Country</p>
                                                            <p class="font-medium">{{ $asset->purchase_origin_country ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Warranty Start Date</p>
                                                            <p class="font-medium">{{ $asset->warranty_start_date ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Warranty Expiry Date</p>
                                                            <p class="font-medium">{{ $asset->warranty_expiry_date ?? 'N/A' }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="mt-5">
                                                        <p class="text-gray-500 text-sm">Description</p>
                                                        <p class="font-medium text-sm">
                                                            {{ $asset->description ?? 'N/A' }}
                                                        </p>
                                                    </div>

                                                    <div class="mt-6 text-right">
                                                        <button type="button"
                                                                onclick="document.getElementById('warranty-details-{{ $asset->id }}').close()"
                                                                class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </dialog>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 text-gray-500">
                                            No warranties expiring soon.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tbody id="warrantyRowsThreeMonths" class="hidden">
                                @forelse ($warrantiesExpiringThreeMonths as $asset)
                                    @php
                                        $daysLeft = now()
                                            ->startOfDay()
                                            ->diffInDays(\Carbon\Carbon::parse($asset->warranty_expiry_date)->startOfDay(), false);
                                    @endphp

                                    <tr class="border-b">
                                        <td class="py-2 font-medium">
                                            {{ $asset->it_internal_number }}
                                        </td>

                                        <td class="py-2">
                                            @if ($daysLeft > 1)
                                                {{ $daysLeft }} days left
                                            @elseif ($daysLeft === 1)
                                                1 day left
                                            @elseif ($daysLeft === 0)
                                                Expires today
                                            @else
                                                Expired
                                            @endif
                                        </td>

                                        <td class="py-2">
                                            <button type="button"
                                                    onclick="document.getElementById('warranty-details-{{ $asset->id }}-three-months').showModal()"
                                                    class="px-3 py-1 bg-gray-800 text-white rounded text-xs hover:bg-gray-700">
                                                View Details
                                            </button>

                                            <dialog id="warranty-details-{{ $asset->id }}-three-months" class="rounded-lg shadow-xl p-0 w-full max-w-2xl">
                                                <div class="p-5">
                                                    <div class="flex justify-between items-center mb-4">
                                                        <h3 class="text-lg font-semibold">
                                                            Asset Warranty Details
                                                        </h3>

                                                        <button type="button"
                                                                onclick="document.getElementById('warranty-details-{{ $asset->id }}-three-months').close()"
                                                                class="text-gray-500 hover:text-gray-800 text-xl">
                                                            &times;
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                        <div>
                                                            <p class="text-gray-500">IT Number</p>
                                                            <p class="font-medium">{{ $asset->it_internal_number }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Serial Number</p>
                                                            <p class="font-medium">{{ $asset->serial_number ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Asset Number</p>
                                                            <p class="font-medium">{{ $asset->asset_number ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Category</p>
                                                            <p class="font-medium">{{ $asset->category ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Brand</p>
                                                            <p class="font-medium">{{ $asset->brand ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Model</p>
                                                            <p class="font-medium">{{ $asset->model ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Plant</p>
                                                            <p class="font-medium">{{ $asset->plant ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Business Unit</p>
                                                            <p class="font-medium">{{ $asset->business_unit ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">End User</p>
                                                            <p class="font-medium">{{ $asset->end_user ?? 'N/A' }}</p>
                                                        </div>

                                                        <div>
                                                            <p class="text-gray-500">Warranty Expiry Date</p>
                                                            <p class="font-medium">{{ $asset->warranty_expiry_date ?? 'N/A' }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="mt-6 text-right">
                                                        <button type="button"
                                                                onclick="document.getElementById('warranty-details-{{ $asset->id }}-three-months').close()"
                                                                class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </dialog>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 text-gray-500">
                                            No warranties expiring within 1 to 3 months.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{--
                    Upcoming maintenance section.

                    The In Maintenance card redirects the user to this section.
                --}}
                <div id="upcoming-maintenance-section" class="dashboard-table-card scroll-mt-32">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Upcoming Maintenance</h2>
                            <p class="text-xs text-gray-500">
                                Showing assets scheduled within the next 14 days
                            </p>
                        </div>

                        <select id="maintenanceRangeFilter"
                                onchange="handleMaintenanceRangeFilter(this.value)"
                                class="text-xs rounded border-gray-300">
                            <option value="14">View within 14 days</option>
                            <option value="3months">View within 1 to 3 months</option>
                            <option value="all">View all</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">IT Number</th>
                                    <th class="text-left py-2">Category</th>
                                    <th class="text-left py-2">End User</th>
                                    <th class="text-left py-2">Maintenance Date</th>
                                </tr>
                            </thead>
                            <tbody id="maintenanceRows14Days">
                                @forelse ($upcomingMaintenance as $asset)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $asset->it_internal_number }}</td>
                                        <td class="py-2">{{ $asset->category }}</td>
                                        <td class="py-2">{{ $asset->end_user }}</td>
                                        <td class="py-2">{{ $asset->next_maintenance }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-3 text-gray-500">
                                            No upcoming maintenance.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tbody id="maintenanceRowsThreeMonths" class="hidden">
                                @forelse ($upcomingMaintenanceThreeMonths as $asset)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $asset->it_internal_number }}</td>
                                        <td class="py-2">{{ $asset->category }}</td>
                                        <td class="py-2">{{ $asset->end_user }}</td>
                                        <td class="py-2">{{ $asset->next_maintenance }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-3 text-gray-500">
                                            No upcoming maintenance within 1 to 3 months.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    
    {{--
        Full warranty expiration modal.

        This modal opens from the "View More" button and displays all future
        warranty expirations ordered from closest to furthest.
    --}}
    <dialog id="all-warranties-modal" class="rounded-lg shadow-xl p-0 w-full max-w-6xl">
        <div class="p-6">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-xl font-semibold">
                        All Warranty Expirations
                    </h3>
                    <p class="text-sm text-gray-500">
                        Assets ordered from the closest warranty expiration date to the furthest one.
                    </p>
                </div>

                <button type="button"
                        onclick="document.getElementById('all-warranties-modal').close()"
                        class="text-gray-500 hover:text-gray-800 text-2xl">
                    &times;
                </button>
            </div>

            <div class="overflow-x-auto max-h-[70vh] overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white sticky top-0">
                        <tr class="border-b">
                            <th class="text-left py-2 px-2">IT Number</th>
                            <th class="text-left py-2 px-2">Category</th>
                            <th class="text-left py-2 px-2">Brand</th>
                            <th class="text-left py-2 px-2">Model</th>
                            <th class="text-left py-2 px-2">Plant</th>
                            <th class="text-left py-2 px-2">Warranty Start</th>
                            <th class="text-left py-2 px-2">Warranty Expiry</th>
                            <th class="text-left py-2 px-2">Time Left</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($allWarrantiesExpiringSoon as $asset)
                            @php
                                /*
                                    Calculate remaining warranty days.
                                */
                                $daysLeft = now()
                                    ->startOfDay()
                                    ->diffInDays(\Carbon\Carbon::parse($asset->warranty_expiry_date)->startOfDay(), false);
                            @endphp

                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-2 font-medium">{{ $asset->it_internal_number }}</td>
                                <td class="py-2 px-2">{{ $asset->category ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->brand ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->model ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->plant ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->warranty_start_date ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->warranty_expiry_date ?? 'N/A' }}</td>
                                <td class="py-2 px-2">
                                    @if ($daysLeft > 1)
                                        {{ $daysLeft }} days left
                                    @elseif ($daysLeft === 1)
                                        1 day left
                                    @elseif ($daysLeft === 0)
                                        Expires today
                                    @else
                                        Expired
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-3 text-gray-500">
                                    No upcoming warranty expirations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-right">
                <button type="button"
                        onclick="document.getElementById('all-warranties-modal').close()"
                        class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                    Close
                </button>
            </div>
        </div>
    </dialog>

    {{--
        Full upcoming maintenance modal.

        This modal opens from the "View More" button and displays all future
        maintenance records ordered from closest to furthest.
    --}}
    <dialog id="all-maintenance-modal" class="rounded-lg shadow-xl p-0 w-full max-w-6xl">
        <div class="p-6">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-xl font-semibold">
                        All Upcoming Maintenance
                    </h3>
                    <p class="text-sm text-gray-500">
                        Assets ordered from the closest maintenance date to the furthest one.
                    </p>
                </div>

                <button type="button"
                        onclick="document.getElementById('all-maintenance-modal').close()"
                        class="text-gray-500 hover:text-gray-800 text-2xl">
                    &times;
                </button>
            </div>

            <div class="overflow-x-auto max-h-[70vh] overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white sticky top-0">
                        <tr class="border-b">
                            <th class="text-left py-2 px-2">IT Number</th>
                            <th class="text-left py-2 px-2">Category</th>
                            <th class="text-left py-2 px-2">Brand</th>
                            <th class="text-left py-2 px-2">Model</th>
                            <th class="text-left py-2 px-2">Plant</th>
                            <th class="text-left py-2 px-2">End User</th>
                            <th class="text-left py-2 px-2">Next Maintenance</th>
                            <th class="text-left py-2 px-2">Time Left</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($allUpcomingMaintenance as $asset)
                            @php
                                /*
                                    Calculate remaining days before maintenance.
                                */
                                $daysLeft = now()
                                    ->startOfDay()
                                    ->diffInDays(\Carbon\Carbon::parse($asset->next_maintenance)->startOfDay(), false);
                            @endphp

                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-2 font-medium">{{ $asset->it_internal_number }}</td>
                                <td class="py-2 px-2">{{ $asset->category ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->brand ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->model ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->plant ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->end_user ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $asset->next_maintenance ?? 'N/A' }}</td>
                                <td class="py-2 px-2">
                                    @if ($daysLeft > 1)
                                        {{ $daysLeft }} days left
                                    @elseif ($daysLeft === 1)
                                        1 day left
                                    @elseif ($daysLeft === 0)
                                        Scheduled today
                                    @else
                                        Overdue
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-3 text-gray-500">
                                    No upcoming maintenance found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-right">
                <button type="button"
                        onclick="document.getElementById('all-maintenance-modal').close()"
                        class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                    Close
                </button>
            </div>
        </div>
    </dialog>

    {{--
        Chart.js library.

        For now, it is loaded from CDN to keep this first version simple.
        Later it can be installed through npm if the project needs local dependencies.
    --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        /*
            Convert Laravel data into JavaScript arrays.

            These arrays come from DashboardController.php.
            They already respect the global plant checklist filter.
        */
        const assetsByPlantLabels = @json($assetsByPlantLabels);
        const assetsByPlantData = @json($assetsByPlantData);

        const assetsByCategoryLabels = @json($assetsByCategoryLabels);
        const assetsByCategoryData = @json($assetsByCategoryData);

        const assetsByStateLabels = @json($assetsByStateLabels);
        const assetsByStateData = @json($assetsByStateData);

        const assetsByBusinessUnitLabels = @json($assetsByBusinessUnitLabels);
        const assetsByBusinessUnitData = @json($assetsByBusinessUnitData);

        /*
            Automatic chart color palette.

            Colors are assigned by index.
            If there are more labels than colors, the palette repeats.
        */
        const chartColors = [
            '#2563eb',
            '#22c55e',
            '#f97316',
            '#ef4444',
            '#a855f7',
            '#06b6d4',
            '#eab308',
            '#ec4899',
            '#14b8a6',
            '#6366f1',
            '#84cc16',
            '#f43f5e',
            '#0ea5e9',
            '#facc15',
            '#10b981',
            '#d946ef',
            '#fb7185',
            '#38bdf8'
        ];

        /*
            Store Chart.js instances.

            We need this so the dropdown checklist filters can update
            existing charts instead of creating new ones.
        */
        const dashboardCharts = {};

        /*
            Return one color per label.

            Example:
            labels: [B, D, G]
            colors: [blue, green, orange]
        */
        function getChartColors(labels) {
            return labels.map((_, index) => chartColors[index % chartColors.length]);
        }

        /*
            Create a reusable bar chart.

            Used by:
            - Assets by Category
            - Assets by Business Unit
        */
        function createBarChart(canvasId, labels, data, label) {
            const colors = getChartColors(labels);

            const chart = new Chart(document.getElementById(canvasId), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        borderRadius: 8,
                        hoverBackgroundColor: colors,
                        hoverBorderColor: '#0f172a',
                        hoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        /*
                            We hide the default legend because these bar charts
                            already show labels on the axis.
                        */
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                /*
                                    Asset counts should be whole numbers.
                                */
                                precision: 0
                            }
                        }
                    }
                }
            });

            dashboardCharts[canvasId] = chart;

            return chart;
        }

        /*
            Create a reusable doughnut chart.

            Used by:
            - Assets by Plant
            - Assets by State
        */
        function createDoughnutChart(canvasId, labels, data, label, legendId = null) {
            const colors = getChartColors(labels);

            const chart = new Chart(document.getElementById(canvasId), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        /*
                            We use our own custom legend because we want to show:
                            color + label + asset count.
                        */
                        legend: {
                            display: false
                        }
                    }
                }
            });

            dashboardCharts[canvasId] = chart;

            /*
                If a legend container exists, render the custom legend.
            */
            if (legendId) {
                renderDoughnutLegend(legendId, labels, data, colors);
            }

            return chart;
        }

        /*
            Render a custom legend for doughnut charts.

            The legend shows:
            - Color dot
            - Label
            - Count
        */
        function renderDoughnutLegend(legendId, labels, data, colors) {
            const legend = document.getElementById(legendId);

            if (!legend) {
                return;
            }

            legend.innerHTML = '';

            labels.forEach((label, index) => {
                const item = document.createElement('div');
                item.className = 'dashboard-doughnut-legend-item';

                const color = document.createElement('span');
                color.className = 'dashboard-doughnut-legend-color';
                color.style.backgroundColor = colors[index];

                const text = document.createElement('span');
                text.textContent = label;

                const value = document.createElement('span');
                value.className = 'dashboard-doughnut-legend-value';
                value.textContent = data[index];

                item.appendChild(color);
                item.appendChild(text);
                item.appendChild(value);

                legend.appendChild(item);
            });
        }

        /*
            Generic dropdown toggle.

            Used by:
            - Global plant checklist
            - Category chart checklist
            - State chart checklist
            - Business Unit chart checklist
        */
        function toggleDropdown(elementId) {
            const targetDropdown = document.getElementById(elementId);

            if (!targetDropdown) {
                return;
            }

            /*
                Close every other dashboard dropdown before opening the selected one.
            */
            document.querySelectorAll('.dashboard-chart-filter-menu, .dashboard-plant-dropdown-menu')
                .forEach(dropdown => {
                    if (dropdown.id !== elementId) {
                        dropdown.classList.add('hidden');
                    }
                });

            targetDropdown.classList.toggle('hidden');
        }

        /*
            Build a dropdown checklist for a chart.

            This function creates one checkbox per label.
            Example:
            Category chart labels:
            - Laptop
            - Desktop
            - Printer
        */
        function buildChartDropdownFilter(panelId, chartId, labels, data, datasetLabel, legendId = null) {
            const panel = document.getElementById(panelId);

            if (!panel) {
                return;
            }

            panel.innerHTML = '';

            /*
                Reset button.

                This selects all checkboxes again and restores the original chart data.
            */
            const actions = document.createElement('div');
            actions.className = 'dashboard-chart-filter-actions';

            const resetButton = document.createElement('button');
            resetButton.type = 'button';
            resetButton.className = 'dashboard-chart-filter-reset-button';
            resetButton.textContent = 'Reset filter';

            resetButton.addEventListener('click', () => {
                panel.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                    checkbox.checked = true;
                });

                updateFilteredChart(panelId, chartId, labels, data, datasetLabel, legendId);
            });

            actions.appendChild(resetButton);
            panel.appendChild(actions);

            const list = document.createElement('div');
            list.className = 'dashboard-chart-filter-list';

            labels.forEach((itemLabel) => {
                const labelElement = document.createElement('label');
                labelElement.className = 'dashboard-chart-filter-item';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = true;
                checkbox.value = itemLabel;

                /*
                    Every time a checkbox changes, update the chart.
                */
                checkbox.addEventListener('change', () => {
                    updateFilteredChart(panelId, chartId, labels, data, datasetLabel, legendId);
                });

                labelElement.appendChild(checkbox);
                labelElement.appendChild(document.createTextNode(itemLabel));

                list.appendChild(labelElement);
            });

            panel.appendChild(list);
        }

        /*
            Update a chart based on checked values.

            This does not reload the dashboard.
            It only updates the Chart.js instance in the browser.
        */
        function updateFilteredChart(panelId, chartId, labels, data, datasetLabel, legendId = null) {
            const panel = document.getElementById(panelId);
            const chart = dashboardCharts[chartId];

            if (!panel || !chart) {
                return;
            }

            const checkedLabels = Array
                .from(panel.querySelectorAll('input[type="checkbox"]:checked'))
                .map(input => input.value);

            const filteredLabels = [];
            const filteredData = [];

            labels.forEach((originalLabel, originalIndex) => {
                if (checkedLabels.includes(originalLabel)) {
                    filteredLabels.push(originalLabel);
                    filteredData.push(data[originalIndex]);
                }
            });

            const filteredColors = getChartColors(filteredLabels);

            chart.data.labels = filteredLabels;
            chart.data.datasets[0].data = filteredData;
            chart.data.datasets[0].backgroundColor = filteredColors;
            chart.data.datasets[0].hoverBackgroundColor = filteredColors;
            chart.data.datasets[0].label = datasetLabel;
            chart.update();

            /*
                If this chart has a custom legend, update it too.
            */
            if (legendId) {
                renderDoughnutLegend(legendId, filteredLabels, filteredData, filteredColors);
            }
        }

        /*
            Render dashboard charts.
        */

        createDoughnutChart(
            'assetsByPlantChart',
            assetsByPlantLabels,
            assetsByPlantData,
            'Assets by Plant',
            'assetsByPlantLegend'
        );

        createBarChart(
            'assetsByCategoryChart',
            assetsByCategoryLabels,
            assetsByCategoryData,
            'Assets by Category'
        );

        createDoughnutChart(
            'assetsByStateChart',
            assetsByStateLabels,
            assetsByStateData,
            'Assets by State',
            'assetsByStateLegend'
        );

        createBarChart(
            'assetsByBusinessUnitChart',
            assetsByBusinessUnitLabels,
            assetsByBusinessUnitData,
            'Assets by Business Unit'
        );

        /*
            Build dropdown filters for charts.

            Assets by Plant does not need a local chart filter because
            it is controlled by the global plant checklist.
        */

        buildChartDropdownFilter(
            'categoryChartFilters',
            'assetsByCategoryChart',
            assetsByCategoryLabels,
            assetsByCategoryData,
            'Assets by Category'
        );

        buildChartDropdownFilter(
            'stateChartFilters',
            'assetsByStateChart',
            assetsByStateLabels,
            assetsByStateData,
            'Assets by State',
            'assetsByStateLegend'
        );

        buildChartDropdownFilter(
            'businessUnitChartFilters',
            'assetsByBusinessUnitChart',
            assetsByBusinessUnitLabels,
            assetsByBusinessUnitData,
            'Assets by Business Unit'
        );

        /*
            Auto-submit plant filter.

            This function is triggered whenever the user checks or unchecks
            a plant from the global plant checklist.
        */
        function submitPlantFilterOnChange() {
            const form = document.getElementById('plantFilterForm');

            if (!form) {
                return;
            }

            showDashboardLoading();
            form.submit();
        }

        /*
            Reset plant filter.

            Since no plants[] are sent, the controller will automatically
            select all plants again.
        */
        function resetPlantFilter() {
            showDashboardLoading();
            window.location.href = "{{ route('dashboard') }}";
        }

        /*
            Close dropdowns when clicking outside them.

            This prevents dropdowns from staying open while the user interacts
            with another area of the dashboard.
        */
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.dashboard-chart-filter-dropdown')
                && !event.target.closest('.dashboard-hero-filter')) {
                document.querySelectorAll('.dashboard-chart-filter-menu, .dashboard-plant-dropdown-menu')
                    .forEach(dropdown => dropdown.classList.add('hidden'));
            }
        });
    </script>

    {{--
        Back to top button.

        This button appears only when the user scrolls down.
    --}}
    <button type="button"
            id="backToTopButton"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="dashboard-back-to-top hidden fixed bottom-6 right-6 z-50 flex items-center justify-center">
        ↑
    </button>

    <script>
        /*
            Show or hide the back-to-top button depending on scroll position.
        */
        const backToTopButton = document.getElementById('backToTopButton');

        window.addEventListener('scroll', function () {
            if (window.scrollY > 300) {
                backToTopButton.classList.remove('hidden');
            } else {
                backToTopButton.classList.add('hidden');
            }
        });
    </script>

    {{--
        Dashboard loading overlay.

        This appears when the user changes the plant filter.
    --}}
    <div id="dashboardLoading"
        class="hidden fixed inset-0 z-50 bg-white/70 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white px-6 py-4 rounded-xl shadow text-sm text-gray-700 border border-gray-200">
            Updating dashboard filter...
        </div>
    </div>

    <script>
        /*
            Show a small loading overlay while the dashboard reloads
            after applying or clearing the plant filter.
        */
        function showDashboardLoading() {
            document.getElementById('dashboardLoading').classList.remove('hidden');
        }
    </script>

    <script>
        function handleWarrantyRangeFilter(value) {
            const rows14Days = document.getElementById('warrantyRows14Days');
            const rowsThreeMonths = document.getElementById('warrantyRowsThreeMonths');

            if (value === 'all') {
                document.getElementById('all-warranties-modal').showModal();
                document.getElementById('warrantyRangeFilter').value = '14';
                rows14Days.classList.remove('hidden');
                rowsThreeMonths.classList.add('hidden');
                return;
            }

            if (value === '3months') {
                rows14Days.classList.add('hidden');
                rowsThreeMonths.classList.remove('hidden');
                return;
            }

            rows14Days.classList.remove('hidden');
            rowsThreeMonths.classList.add('hidden');
        }

        function handleMaintenanceRangeFilter(value) {
            const rows14Days = document.getElementById('maintenanceRows14Days');
            const rowsThreeMonths = document.getElementById('maintenanceRowsThreeMonths');

            if (value === 'all') {
                document.getElementById('all-maintenance-modal').showModal();
                document.getElementById('maintenanceRangeFilter').value = '14';
                rows14Days.classList.remove('hidden');
                rowsThreeMonths.classList.add('hidden');
                return;
            }

            if (value === '3months') {
                rows14Days.classList.add('hidden');
                rowsThreeMonths.classList.remove('hidden');
                return;
            }

            rows14Days.classList.remove('hidden');
            rowsThreeMonths.classList.add('hidden');
        }
    </script>
</x-app-layout>