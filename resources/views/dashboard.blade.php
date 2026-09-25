<x-app-layout>
    <div class="dashboard-page py-6 scroll-smooth">

        {{--
            Main dashboard content container.

            Every dashboard section must remain inside this container
            so the hero, KPI cards, charts and tables share the same width.
        --}}
        <div class="dashboard-content-container">

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

                        <!--
                            Plant filter dropdown toggle.

                            The label has an ID so JavaScript can update the visible
                            selection count without submitting the form.
                        -->
                        <button
                            type="button"
                            class="dashboard-plant-dropdown-button"
                            onclick="toggleDropdown('plantFilterDropdown')"
                        >
                            <span id="plantFilterButtonLabel">
                                {{ $selectedPlantLabel }}
                            </span>

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
                                                @checked(in_array($plant, $selectedPlantsArray, true))>
                                        <span>{{ $plant }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <!--
                                Plant filter actions.

                                Unselect all:
                                Clears the current checkbox selection without reloading the page.

                                Reset filter:
                                Selects every available plant again without reloading the page.

                                Apply filter:
                                Submits the selected plants and refreshes the dashboard data.
                            -->
                            <div class="dashboard-plant-filter-actions">

                                <button
                                    type="button"
                                    class="dashboard-plant-filter-button"
                                    onclick="clearPlantFilterSelection()"
                                >
                                    Unselect all
                                </button>

                                <button
                                    type="button"
                                    class="dashboard-plant-filter-button"
                                    onclick="selectAllPlantFilterOptions()"
                                >
                                    Reset filter
                                </button>

                                <button
                                    type="button"
                                    class="dashboard-plant-filter-button primary"
                                    onclick="applyPlantFilter()"
                                >
                                    Apply filter
                                </button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{--
                Dashboard view selector.

                This selector will later switch dashboard sections without reloading
                the page or changing the current plant filter.
            --}}
            <div class="dashboard-view-selector" role="tablist" aria-label="Dashboard views">
                <button
                    type="button"
                    class="dashboard-view-selector-button is-active"
                    data-dashboard-view="general"
                >
                    General
                </button>

                <button
                    type="button"
                    class="dashboard-view-selector-button"
                    data-dashboard-view="maintenances"
                >
                    Maintenances
                </button>

                <button
                    type="button"
                    class="dashboard-view-selector-button"
                    data-dashboard-view="warranties"
                >
                    Warranties
                </button>

                {{--
                    Asset Intelligence is a read-only analysis of the current
                    inventory data. It does not require additional data capture.
                --}}
                <button
                    type="button"
                    class="dashboard-view-selector-button"
                    data-dashboard-view="intelligence"
                >
                    Asset Intelligence
                </button>
            </div>

            {{--
                General dashboard view.

                It contains the current KPI cards and the four existing asset charts.
            --}}
            <div
                id="dashboard-view-general"
                class="dashboard-view is-active"
            >
            
            {{--
                Summary cards.

                These cards show the main inventory indicators for the selected dashboard scope.
            --}}
            <div class="dashboard-section">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                    {{--
                        IT Room assets KPI.

                        This count respects the selected global plant filter.
                    --}}
                    <div class="dashboard-kpi-card it-room">
                        <p class="dashboard-kpi-label">IT Room Assets</p>

                        <h2 class="dashboard-kpi-value">
                            {{ $itRoomAssetsCount }}
                        </h2>

                        <p class="dashboard-kpi-helper">
                            Assets currently located in IT Room
                        </p>
                    </div>
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

            </div>

            {{--
                Operational tables.

                These tables show assets that may require action soon.
            --}}
            {{--
                Asset Intelligence dashboard view.

                Findings are calculated by DashboardController from fields that
                already exist in inventory. No result is stored or changes an asset.
            --}}
            <div
                id="dashboard-view-intelligence"
                class="dashboard-view"
                hidden
            >
                <div class="dashboard-section">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="dashboard-kpi-card">
                            <p class="dashboard-kpi-label">Total Findings</p>
                            <h2 class="dashboard-kpi-value">{{ $assetIntelligenceFindingCount }}</h2>
                            <p class="dashboard-kpi-helper">Open data-quality and lifecycle risks</p>
                        </div>

                        <div class="dashboard-kpi-card success">
                            <p class="dashboard-kpi-label">At Risk Assets</p>
                            <h2 class="dashboard-kpi-value">
                                {{ ($assetIntelligenceLifecycleData[2] ?? 0) + ($assetIntelligenceLifecycleData[3] ?? 0) }}
                            </h2>
                            <p class="dashboard-kpi-helper">Assets with high or critical findings</p>
                        </div>

                        <div class="dashboard-kpi-card it-room">
                            <p class="dashboard-kpi-label">Attention Required</p>
                            <h2 class="dashboard-kpi-value">{{ $assetIntelligenceLifecycleData[1] ?? 0 }}</h2>
                            <p class="dashboard-kpi-helper">Assets with medium-priority findings</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="dashboard-chart-card">
                        <h2 class="dashboard-chart-title">Lifecycle Risk Status</h2>
                        <p class="dashboard-chart-subtitle">
                            Calculated from current warranty, maintenance, condition and data-quality findings.
                        </p>
                        <div class="dashboard-chart-wrapper">
                            <canvas id="assetIntelligenceLifecycleChart"></canvas>
                        </div>
                    </div>

                    <div class="dashboard-chart-card">
                        <h2 class="dashboard-chart-title">Findings by Type</h2>
                        <p class="dashboard-chart-subtitle">
                            The most common actions required in the selected plant scope.
                        </p>
                        <div class="dashboard-chart-wrapper">
                            <canvas id="assetIntelligenceTypeChart"></canvas>
                        </div>
                        <div
                            id="assetIntelligenceTypeLegend"
                            class="dashboard-doughnut-legend"
                        ></div>
                    </div>
                </div>

                <div class="dashboard-table-card scroll-mt-32">
                    <div class="dashboard-table-header">
                        <div>
                            <h2 class="dashboard-table-title">Priority Findings</h2>
                            <p id="assetIntelligenceTableSubtitle" class="dashboard-table-subtitle"></p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-gray-500">
                                    <th class="px-4 py-3">IT Number</th>
                                    <th class="px-4 py-3">Plant</th>
                                    <th class="px-4 py-3">Finding</th>
                                    <th class="px-4 py-3">Severity</th>
                                    <th class="px-4 py-3">Recommendation</th>
                                </tr>

                                <tr class="border-b bg-gray-50">
                                    <th class="px-4 py-2">
                                        <input
                                            type="text"
                                            id="assetIntelligenceITNumberFilter"
                                            placeholder="Filter IT Number"
                                            class="w-full rounded border-gray-300 text-xs"
                                        >
                                    </th>
                                    <th class="px-4 py-2">
                                        <div class="dashboard-chart-filter-dropdown">
                                            <button type="button" id="assetIntelligencePlantFilterButton" class="dashboard-chart-filter-toggle" onclick="toggleDropdown('assetIntelligencePlantFilterMenu')">All plants ▾</button>
                                            <div id="assetIntelligencePlantFilterMenu" class="dashboard-chart-filter-menu hidden"></div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div class="dashboard-chart-filter-dropdown">
                                            <button type="button" id="assetIntelligenceFindingFilterButton" class="dashboard-chart-filter-toggle" onclick="toggleDropdown('assetIntelligenceFindingFilterMenu')">All findings ▾</button>
                                            <div id="assetIntelligenceFindingFilterMenu" class="dashboard-chart-filter-menu hidden"></div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div class="dashboard-chart-filter-dropdown">
                                            <button type="button" id="assetIntelligenceSeverityFilterButton" class="dashboard-chart-filter-toggle" onclick="toggleDropdown('assetIntelligenceSeverityFilterMenu')">All severities ▾</button>
                                            <div id="assetIntelligenceSeverityFilterMenu" class="dashboard-chart-filter-menu hidden"></div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="assetIntelligenceFindingsBody"></tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" class="dashboard-plant-filter-button" onclick="resetAssetIntelligenceFilters()">Reset table filters</button>
                            <span id="assetIntelligenceRange" class="text-sm text-gray-500"></span>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div id="assetIntelligencePagination" class="flex items-center gap-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{--
                Warranties dashboard view.

                It will contain the warranty charts and the existing expiring-soon table.
            --}}
            <div
                id="dashboard-view-warranties"
                class="dashboard-view"
                hidden
            >
                {{--
                    Warranty charts.

                    Both charts use the same selected plant scope as the rest of the dashboard.
                --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="dashboard-chart-card">
                        <h2 class="dashboard-chart-title">
                            Assets With Warranty by Category
                        </h2>

                        <p class="dashboard-chart-subtitle">
                            Equipment categories with a registered warranty expiry date.
                        </p>

                        <div class="dashboard-chart-wrapper">
                            <canvas id="warrantiesByCategoryChart"></canvas>
                        </div>

                        <div
                            id="warrantiesByCategoryLegend"
                            class="dashboard-doughnut-legend"
                        ></div>
                    </div>

                    <div class="dashboard-chart-card">
                        <h2 class="dashboard-chart-title">
                            Warranty Status
                        </h2>

                        <p class="dashboard-chart-subtitle">
                            Expired, expiring within 14 days and active warranties.
                        </p>

                        <div class="dashboard-chart-wrapper">
                            <canvas id="warrantyStatusChart"></canvas>
                        </div>
                    </div>
                </div>

                {{--
                    Warranties expiring soon section.

                    This section is intentionally simplified to show only:
                    - IT Number
                    - Remaining warranty time
                    - Details button
                --}}
                <div id="warranties-expiring-section" class="dashboard-table-card scroll-mt-32">

                    <div class="flex items-center justify-between gap-4 mb-4">

                        <div>
                            <h2 class="text-lg font-semibold">
                                Warranties Expiring Soon
                            </h2>

                            <p class="text-xs text-gray-500">
                                Next 14 days ·
                                <strong>{{ $warrantiesExpiringSoonCount }}</strong>
                                {{ $warrantiesExpiringSoonCount === 1 ? 'asset' : 'assets' }} in total
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#all-warranties-modal"
                        >
                            View next 3 months
                        </button>

                    </div>


                    <div class="overflow-x-auto">

                        <table class="min-w-full text-sm">

                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">
                                        IT Number
                                    </th>

                                    <th class="text-left py-2">
                                        Time Left
                                    </th>

                                    <th class="text-left py-2">
                                        Details
                                    </th>
                                </tr>
                            </thead>


                            <tbody>

                                @forelse ($warrantiesExpiringSoon as $asset)

                                    @php
                                        $daysLeft = now()
                                            ->startOfDay()
                                            ->diffInDays(
                                                \Carbon\Carbon::parse(
                                                    $asset->warranty_expiry_date
                                                )->startOfDay(),
                                                false
                                            );
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

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#warranty-details-{{ $asset->id }}"
                                            >
                                                View Details
                                            </button>


                                            <div
                                                class="modal fade app-detail-modal"
                                                id="warranty-details-{{ $asset->id }}"
                                                tabindex="-1"
                                                aria-labelledby="warrantyDetailsLabel{{ $asset->id }}"
                                                aria-hidden="true"
                                            >

                                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">

                                                    <div class="modal-content">

                                                        <div class="modal-header">

                                                            <div>

                                                                <h5
                                                                    class="modal-title"
                                                                    id="warrantyDetailsLabel{{ $asset->id }}"
                                                                >
                                                                    Asset Warranty Details
                                                                </h5>

                                                                <p class="modal-subtitle">
                                                                    Asset identification and warranty information.
                                                                </p>

                                                            </div>


                                                            <button
                                                                type="button"
                                                                class="btn-close"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close"
                                                            ></button>

                                                        </div>


                                                        <div class="modal-body">

                                                            <div class="row g-3 app-detail-grid">

                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            IT Number
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->it_internal_number }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Serial Number
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->serial_number ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Asset Number
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->asset_number ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Category
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->category ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Brand
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->brand ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Model
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->model ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Plant
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->plant ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Business Unit
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->business_unit ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            End User
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->end_user ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Purchase Origin Country
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->purchase_origin_country ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Warranty Start Date
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->warranty_start_date ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="app-detail-item">
                                                                        <span class="app-detail-label">
                                                                            Warranty Expiry Date
                                                                        </span>

                                                                        <p class="app-detail-value">
                                                                            {{ $asset->warranty_expiry_date ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                            </div>


                                                            <div class="app-detail-description">

                                                                <span class="app-detail-label">
                                                                    Description
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->description ?? 'N/A' }}
                                                                </p>

                                                            </div>

                                                        </div>


                                                        <div class="modal-footer">

                                                            <button
                                                                type="button"
                                                                class="btn btn-secondary"
                                                                data-bs-dismiss="modal"
                                                            >
                                                                Close
                                                            </button>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="3" class="py-3 text-gray-500">
                                            No warranties expiring within the next 14 days.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>
            
            </div>

        {{--
            Maintenances dashboard view.

            It will contain the maintenance charts and the existing upcoming-maintenance table.
        --}}
        <div
            id="dashboard-view-maintenances"
            class="dashboard-view"
            hidden
        >

        {{--
            Maintenance charts.

            Both charts use the same selected plant scope as the rest of the dashboard.
        --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="dashboard-chart-card">
                <h2 class="dashboard-chart-title">
                    Assigned Maintenances by Plant
                </h2>

                <p class="dashboard-chart-subtitle">
                    Maintenance records with a responsible account assigned.
                </p>

                <div class="dashboard-chart-wrapper">
                    <canvas id="assignedMaintenancesByPlantChart"></canvas>
                </div>

                <div
                    id="assignedMaintenancesByPlantLegend"
                    class="dashboard-doughnut-legend"
                ></div>
            </div>

            <div class="dashboard-chart-card">
                <h2 class="dashboard-chart-title">
                    Maintenance Status
                </h2>

                <p class="dashboard-chart-subtitle">
                    Overdue, pending, in review and completed maintenance records.
                </p>

                <div class="dashboard-chart-wrapper">
                    <canvas id="maintenanceStatusChart"></canvas>
                </div>
            </div>
        </div>

        {{--
            Upcoming maintenance section.

            This section follows the same structure as the warranty section:
            - Main table: next 14 days
            - Maximum 10 visible records
            - Real total displayed in the subtitle
            - Full 3-month list available through a modal
        --}}
        <div id="upcoming-maintenance-section" class="dashboard-table-card scroll-mt-32">

            <div class="flex items-center justify-between gap-4 mb-4">

                <div>
                    <h2 class="text-lg font-semibold">
                        Upcoming Maintenance
                    </h2>

                    <p class="text-xs text-gray-500">
                        Next 14 days ·
                        <strong>{{ $upcomingMaintenanceCount }}</strong>
                        {{ $upcomingMaintenanceCount === 1 ? 'asset' : 'assets' }} in total
                    </p>
                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#all-maintenance-modal"
                >
                    View next 3 months
                </button>

            </div>


            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">
                                IT Number
                            </th>

                            <th class="text-left py-2">
                                Time Left
                            </th>

                            <th class="text-center py-2">
                                Assignment Status
                            </th>

                            <th class="text-left py-2">
                                Maintenance Responsible
                            </th>

                            <th class="text-left py-2">
                                Details
                            </th>
                        </tr>
                    </thead>


                    <tbody>

                        @forelse ($upcomingMaintenance as $asset)

                            @php
                                $daysLeft = now()
                                    ->startOfDay()
                                    ->diffInDays(
                                        \Carbon\Carbon::parse(
                                            $asset->next_maintenance
                                        )->startOfDay(),
                                        false
                                    );
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

                                        Scheduled today

                                    @else

                                        Overdue

                                    @endif

                                </td>

                                <td class="py-2 text-center">

                                    @if ($asset->maintenance_responsible_id)

                                        <span class="badge text-bg-success">
                                            Assigned
                                        </span>

                                    @else

                                        <span class="badge text-bg-danger">
                                            Not assigned
                                        </span>

                                    @endif

                                </td>

                                <td class="py-2">
                                    {{ $asset->maintenanceResponsible?->name ?? '—' }}
                                </td>

                                <td class="py-2">

                                    <div class="d-flex flex-wrap gap-2">

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#maintenance-details-{{ $asset->id }}"
                                        >
                                            View Details
                                        </button>

                                        @if ($asset->maintenance_responsible_id)

                                            <form
                                                method="POST"
                                                action="{{ route('maintenance.unassign', $asset) }}"
                                                onsubmit="return confirm('Cancel the maintenance assignment for this asset?');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                >
                                                    Cancel Assignment
                                                </button>
                                            </form>

                                        @else

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#maintenance-assignment-{{ $asset->id }}"
                                            >
                                                Quick Assign
                                            </button>

                                        @endif

                                    </div>

                                    <div
                                        class="modal fade app-detail-modal"
                                        id="maintenance-assignment-{{ $asset->id }}"
                                        tabindex="-1"
                                        aria-labelledby="maintenanceAssignmentLabel{{ $asset->id }}"
                                        aria-hidden="true"
                                    >
                                        <div class="modal-dialog modal-dialog-centered">

                                            <div class="modal-content">

                                                <form
                                                    method="POST"
                                                    action="{{ route('maintenance.assign', $asset) }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <div class="modal-header">

                                                        <div>

                                                            <h5
                                                                class="modal-title"
                                                                id="maintenanceAssignmentLabel{{ $asset->id }}"
                                                            >
                                                                Quick Maintenance Assignment
                                                            </h5>

                                                            <p class="modal-subtitle">
                                                                Assign {{ $asset->it_internal_number }} to an active IT user.
                                                            </p>

                                                        </div>

                                                        <button
                                                            type="button"
                                                            class="btn-close"
                                                            data-bs-dismiss="modal"
                                                            aria-label="Close"
                                                        ></button>

                                                    </div>

                                                    <div class="modal-body">

                                                        <label
                                                            for="maintenance-responsible-{{ $asset->id }}"
                                                            class="form-label"
                                                        >
                                                            Maintenance Responsible
                                                        </label>

                                                        <select
                                                            id="maintenance-responsible-{{ $asset->id }}"
                                                            name="maintenance_responsible_id"
                                                            class="form-select"
                                                            required
                                                        >
                                                            <option value="" disabled>
                                                                Select a user
                                                            </option>

                                                            @foreach ($maintenanceAssignees as $assignee)

                                                                <option
                                                                    value="{{ $assignee->id }}"
                                                                    {{ (int) $asset->maintenance_responsible_id === (int) $assignee->id ? 'selected' : '' }}
                                                                >
                                                                    {{ $assignee->name }}
                                                                    @if ($assignee->employee_number)
                                                                        — {{ $assignee->employee_number }}
                                                                    @endif
                                                                </option>

                                                            @endforeach

                                                        </select>

                                                    </div>

                                                    <div class="modal-footer">

                                                        <button
                                                            type="button"
                                                            class="btn btn-secondary"
                                                            data-bs-dismiss="modal"
                                                        >
                                                            Cancel
                                                        </button>

                                                        <button
                                                            type="submit"
                                                            class="btn btn-success"
                                                        >
                                                            Assign Maintenance
                                                        </button>

                                                    </div>

                                                </form>

                                            </div>

                                        </div>
                                    </div>

                                    <div
                                        class="modal fade app-detail-modal"
                                        id="maintenance-details-{{ $asset->id }}"
                                        tabindex="-1"
                                        aria-labelledby="maintenanceDetailsLabel{{ $asset->id }}"
                                        aria-hidden="true"
                                    >
                                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">

                                            <div class="modal-content">

                                                <div class="modal-header">

                                                    <div>

                                                        <h5
                                                            class="modal-title"
                                                            id="maintenanceDetailsLabel{{ $asset->id }}"
                                                        >
                                                            Asset Maintenance Details
                                                        </h5>

                                                        <p class="modal-subtitle">
                                                            Asset identification and maintenance information.
                                                        </p>

                                                    </div>


                                                    <button
                                                        type="button"
                                                        class="btn-close"
                                                        data-bs-dismiss="modal"
                                                        aria-label="Close"
                                                    ></button>

                                                </div>


                                                <div class="modal-body">

                                                    <div class="row g-3 app-detail-grid">

                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    IT Number
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->it_internal_number }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Serial Number
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->serial_number ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Asset Number
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->asset_number ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Category
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->category ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Brand
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->brand ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Model
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->model ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Plant
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->plant ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Business Unit
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->business_unit ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    End User
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->end_user ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Maintenance Responsible
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->maintenanceResponsible?->name ?? 'Not assigned' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Next Maintenance
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ $asset->next_maintenance ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="app-detail-item">
                                                                <span class="app-detail-label">
                                                                    Maintenance Status
                                                                </span>

                                                                <p class="app-detail-value">
                                                                    {{ ucfirst($asset->effective_maintenance_status ?? 'N/A') }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                    </div>


                                                    <div class="app-detail-description">

                                                        <span class="app-detail-label">
                                                            Description
                                                        </span>

                                                        <p class="app-detail-value">
                                                            {{ $asset->description ?? 'N/A' }}
                                                        </p>

                                                    </div>

                                                </div>


                                                <div class="modal-footer">

                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary"
                                                        data-bs-dismiss="modal"
                                                    >
                                                        Close
                                                    </button>

                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="py-3 text-gray-500">
                                    No maintenance scheduled within the next 14 days.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>
    
{{-- Warranty expirations within the next three months --}}
<div
    class="modal fade app-table-modal"
    id="all-warranties-modal"
    tabindex="-1"
    aria-labelledby="allWarrantiesModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title"
                        id="allWarrantiesModalLabel"
                    >
                        Warranty Expirations - Next 3 Months
                    </h5>

                    <p class="modal-subtitle">
                        {{ $warrantiesNextThreeMonths->count() }}
                        {{ $warrantiesNextThreeMonths->count() === 1 ? 'asset' : 'assets' }}
                        expiring from today through the next 3 months.
                    </p>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <div class="modal-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle app-table mb-0">

                        <thead>
                            <tr>
                                <th>IT Number</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Plant</th>
                                <th>Warranty Start</th>
                                <th>Warranty Expiry</th>
                                <th>Time Left</th>
                            </tr>
                        </thead>


                        <tbody>

                            @forelse ($warrantiesNextThreeMonths as $asset)

                                @php
                                    $daysLeft = now()
                                        ->startOfDay()
                                        ->diffInDays(
                                            \Carbon\Carbon::parse(
                                                $asset->warranty_expiry_date
                                            )->startOfDay(),
                                            false
                                        );
                                @endphp


                                <tr>

                                    <td class="fw-semibold">
                                        {{ $asset->it_internal_number }}
                                    </td>

                                    <td>
                                        {{ $asset->category ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $asset->brand ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $asset->model ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $asset->plant ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $asset->warranty_start_date ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $asset->warranty_expiry_date ?? 'N/A' }}
                                    </td>

                                    <td>

                                        @if ($daysLeft > 1)

                                            {{ $daysLeft }} days left

                                        @elseif ($daysLeft === 1)

                                            1 day left

                                        @elseif ($daysLeft === 0)

                                            Expires today

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="8"
                                        class="text-center text-muted py-4"
                                    >
                                        No warranties expiring within the next 3 months.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>

            </div>

        </div>

    </div>
</div>

        {{--
            Maintenance scheduled within the next three months.

            This modal shows every maintenance record scheduled from today
            through the next three months, ordered from closest to furthest.
        --}}
        <div
            class="modal fade app-table-modal"
            id="all-maintenance-modal"
            tabindex="-1"
            aria-labelledby="allMaintenanceModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

                <div class="modal-content">

                    <div class="modal-header px-4">

                        <div>

                            <h5
                                class="modal-title"
                                id="allMaintenanceModalLabel"
                            >
                                Upcoming Maintenance - Next 3 Months
                            </h5>

                            <p class="modal-subtitle">
                                {{ $maintenanceNextThreeMonths->count() }}
                                {{ $maintenanceNextThreeMonths->count() === 1 ? 'asset' : 'assets' }}
                                scheduled from today through the next 3 months.
                            </p>

                        </div>


                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>

                    </div>


                    <div class="modal-body px-4 pt-3 pb-4">

                        <div class="table-responsive px-2">

                            <table class="min-w-full text-sm">

                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">IT Number</th>
                                        <th class="text-left py-2">Time Left</th>
                                        <th class="text-center py-2">Assignment Status</th>
                                        <th class="text-left py-2">Maintenance Responsible</th>
                                        <th class="text-left py-2">Details</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse ($maintenanceNextThreeMonths as $asset)

                                        @php
                                            $daysLeft = now()
                                                ->startOfDay()
                                                ->diffInDays(
                                                    \Carbon\Carbon::parse(
                                                        $asset->next_maintenance
                                                    )->startOfDay(),
                                                    false
                                                );
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

                                                    Scheduled today

                                                @else

                                                    Overdue

                                                @endif

                                            </td>

                                            <td class="py-2 text-center">

                                                @if ($asset->maintenance_responsible_id)

                                                    <span class="badge text-bg-success">
                                                        Assigned
                                                    </span>

                                                @else

                                                    <span class="badge text-bg-danger">
                                                        Not assigned
                                                    </span>

                                                @endif

                                            </td>

                                            <td class="py-2">
                                                {{ $asset->maintenanceResponsible?->name ?? '—' }}
                                            </td>

                                            <td class="py-2">

                                                <div class="d-flex flex-wrap gap-2">

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary js-maintenance-detail"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#maintenance-details-modal"
                                                        data-maintenance="{{ json_encode([
                                                            'itNumber' => $asset->it_internal_number,
                                                            'serialNumber' => $asset->serial_number ?? 'N/A',
                                                            'assetNumber' => $asset->asset_number ?? 'N/A',
                                                            'category' => $asset->category ?? 'N/A',
                                                            'brand' => $asset->brand ?? 'N/A',
                                                            'model' => $asset->model ?? 'N/A',
                                                            'plant' => $asset->plant ?? 'N/A',
                                                            'businessUnit' => $asset->business_unit ?? 'N/A',
                                                            'endUser' => $asset->end_user ?? 'N/A',
                                                            'responsible' => $asset->maintenanceResponsible?->name ?? 'Not assigned',
                                                            'nextMaintenance' => $asset->next_maintenance ?? 'N/A',
                                                            'status' => ucfirst($asset->effective_maintenance_status ?? 'N/A'),
                                                            'description' => $asset->description ?? 'N/A',
                                                        ]) }}"
                                                    >
                                                        View Details
                                                    </button>

                                                    @if ($asset->maintenance_responsible_id)

                                                        <form
                                                            method="POST"
                                                            action="{{ route('maintenance.unassign', $asset) }}"
                                                            onsubmit="return confirm('Cancel the maintenance assignment for this asset?');"
                                                        >
                                                            @csrf
                                                            @method('DELETE')

                                                            <button
                                                                type="submit"
                                                                class="btn btn-sm btn-outline-danger"
                                                            >
                                                                Cancel Assignment
                                                            </button>
                                                        </form>

                                                    @else

                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-success js-maintenance-assignment"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#maintenance-assignment-modal"
                                                            data-assign-url="{{ route('maintenance.assign', $asset) }}"
                                                            data-it-number="{{ $asset->it_internal_number }}"
                                                        >
                                                            Quick Assign
                                                        </button>

                                                    @endif

                                                </div>

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td
                                                colspan="5"
                                                class="text-center text-muted py-4"
                                            >
                                                No maintenance scheduled within the next 3 months.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Close
                        </button>

                    </div>

                </div>

            </div>
        </div>

    <div
    class="modal fade app-detail-modal"
    id="maintenance-details-modal"
    tabindex="-1"
    aria-labelledby="maintenanceDetailsModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="maintenanceDetailsModalLabel">
                        Asset Maintenance Details
                    </h5>

                    <p class="modal-subtitle">
                        Asset identification and maintenance information.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <div class="modal-body">
                <div class="row g-3 app-detail-grid">

                    @foreach ([
                        'IT Number' => 'itNumber',
                        'Serial Number' => 'serialNumber',
                        'Asset Number' => 'assetNumber',
                        'Category' => 'category',
                        'Brand' => 'brand',
                        'Model' => 'model',
                        'Plant' => 'plant',
                        'Business Unit' => 'businessUnit',
                        'End User' => 'endUser',
                        'Maintenance Responsible' => 'responsible',
                        'Next Maintenance' => 'nextMaintenance',
                        'Maintenance Status' => 'status',
                    ] as $label => $field)

                        <div class="col-md-6">
                            <div class="app-detail-item">
                                <span class="app-detail-label">
                                    {{ $label }}
                                </span>

                                <p
                                    class="app-detail-value"
                                    id="maintenance-detail-{{ $field }}"
                                >
                                    —
                                </p>
                            </div>
                        </div>

                    @endforeach

                </div>

                <div class="app-detail-description">
                    <span class="app-detail-label">
                        Description
                    </span>

                    <p
                        class="app-detail-value"
                        id="maintenance-detail-description"
                    >
                        —
                    </p>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>
            </div>

        </div>
    </div>
</div>


<div
    class="modal fade app-detail-modal"
    id="maintenance-assignment-modal"
    tabindex="-1"
    aria-labelledby="maintenanceAssignmentModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form
                method="POST"
                id="maintenance-assignment-form"
            >
                @csrf
                @method('PATCH')

                <div class="modal-header">
                    <div>
                        <h5
                            class="modal-title"
                            id="maintenanceAssignmentModalLabel"
                        >
                            Quick Maintenance Assignment
                        </h5>

                        <p
                            class="modal-subtitle"
                            id="maintenance-assignment-subtitle"
                        >
                            Assign an asset to an active IT user.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body">
                    <label
                        for="maintenance-responsible"
                        class="form-label"
                    >
                        Maintenance Responsible
                    </label>

                    <select
                        id="maintenance-responsible"
                        name="maintenance_responsible_id"
                        class="form-select"
                        required
                    >
                        <option value="" selected disabled>
                            Select a user
                        </option>

                        @foreach ($maintenanceAssignees as $assignee)

                            <option value="{{ $assignee->id }}">
                                {{ $assignee->name }}
                                @if ($assignee->employee_number)
                                    — {{ $assignee->employee_number }}
                                @endif
                            </option>

                        @endforeach

                    </select>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        Assign Maintenance
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


<script>
    const maintenanceDetailsModal = document.getElementById(
        'maintenance-details-modal'
    );

    if (maintenanceDetailsModal) {
        maintenanceDetailsModal.addEventListener(
            'show.bs.modal',
            (event) => {
                const button = event.relatedTarget;

                if (!button || !button.dataset.maintenance) {
                    return;
                }

                const maintenance = JSON.parse(
                    button.dataset.maintenance
                );

                Object.entries(maintenance).forEach(
                    ([field, value]) => {
                        const element = document.getElementById(
                            'maintenance-detail-' + field
                        );

                        if (element) {
                            element.textContent = value || 'N/A';
                        }
                    }
                );
            }
        );
    }


    const maintenanceAssignmentModal = document.getElementById(
        'maintenance-assignment-modal'
    );

    if (maintenanceAssignmentModal) {
        maintenanceAssignmentModal.addEventListener(
            'show.bs.modal',
            (event) => {
                const button = event.relatedTarget;

                if (!button) {
                    return;
                }

                document
                    .getElementById('maintenance-assignment-form')
                    .action = button.dataset.assignUrl;

                document
                    .getElementById('maintenance-assignment-subtitle')
                    .textContent =
                        'Assign ' + button.dataset.itNumber
                        + ' to an active IT user.';
            }
        );
    }
</script>

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
            Warranty chart data prepared by DashboardController.php.
        */
        const warrantiesByCategoryLabels = @json($warrantiesByCategoryLabels);
        const warrantiesByCategoryData = @json($warrantiesByCategoryData);

        const warrantyStatusLabels = @json($warrantyStatusLabels);
        const warrantyStatusData = @json($warrantyStatusData);

        /*
            Maintenance chart data prepared by DashboardController.php.
        */
        const assignedMaintenancesByPlantLabels =
            @json($assignedMaintenancesByPlantLabels);

        const assignedMaintenancesByPlantData =
            @json($assignedMaintenancesByPlantData);

        const maintenanceStatusLabels = @json($maintenanceStatusLabels);
        const maintenanceStatusData = @json($maintenanceStatusData);

        /*
            Asset Intelligence chart data.

            The controller calculates these values from the existing inventory
            dataset, always respecting the selected plant filter.
        */
        const assetIntelligenceLifecycleLabels =
            @json($assetIntelligenceLifecycleLabels);

        const assetIntelligenceLifecycleData =
            @json($assetIntelligenceLifecycleData);

        const assetIntelligenceTypeLabels =
            @json($assetIntelligenceTypeLabels);

        const assetIntelligenceTypeData =
            @json($assetIntelligenceTypeData);

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
        |--------------------------------------------------------------------------
        | Bar value labels
        |--------------------------------------------------------------------------
        |
        | Shows the exact value above bars when explicitly enabled for a chart.
        | This is useful when a few small values share the same chart with a
        | much larger value.
        */
        const barValueLabelsPlugin = {
            id: 'barValueLabels',

            afterDatasetsDraw(chart, args, pluginOptions) {
                if (!pluginOptions?.display) {
                    return;
                }

                const dataset = chart.data.datasets[0];
                const meta = chart.getDatasetMeta(0);
                const { ctx, chartArea } = chart;

                ctx.save();
                ctx.fillStyle = '#0f172a';
                ctx.font = '700 12px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';

                meta.data.forEach((bar, index) => {
                    const value = Number(dataset.data[index] ?? 0);
                    const position = bar.tooltipPosition();

                    /*
                        Keep labels within the visible chart area, including zero values.
                    */
                    const labelY = Math.max(
                        chartArea.top + 14,
                        position.y - 8
                    );

                    ctx.fillText(
                        value.toLocaleString(),
                        position.x,
                        labelY
                    );
                });

                ctx.restore();
            }
        };

        /*
            Create a reusable bar chart.

            Used by:
            - Assets by Category
            - Assets by Business Unit
        */
        function createBarChart(
            canvasId,
            labels,
            data,
            label,
            showValueLabels = false,
            yAxisType = 'linear'
        ) {
            const colors = getChartColors(labels);

            const chart = new Chart(document.getElementById(canvasId), {
                type: 'bar',
                plugins: [barValueLabelsPlugin],
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
                        dashboardValueLabels: {
                            display: showValueLabels
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            type: yAxisType,

                            /*
                                A logarithmic scale cannot start at zero. Using 0.5 as the
                                minimum makes values of 1 and 3 visually distinguishable.
                            */
                            min: yAxisType === 'logarithmic' ? 0.5 : undefined,
                            beginAtZero: yAxisType === 'linear',

                            ticks: {
                                precision: 0,

                                callback(value) {
                                    /*
                                        Keep the normal labels for every linear chart.
                                        For the logarithmic maintenance chart, show only the
                                        main scale levels to prevent overlapping tick labels.
                                    */
                                    if (yAxisType !== 'logarithmic') {
                                        return Number(value).toLocaleString();
                                    }

                                    const mainLogarithmicTicks = [
                                        0.5,
                                        1,
                                        10,
                                        100,
                                        1000,
                                    ];

                                    return mainLogarithmicTicks.includes(Number(value))
                                        ? Number(value).toLocaleString()
                                        : '';
                                }
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

            /*
            |--------------------------------------------------------------------------
            | Chart filter action buttons
            |--------------------------------------------------------------------------
            |
            | Adds two actions to each dashboard chart filter:
            |
            | Unselect all:
            | Clears every checkbox and immediately updates the chart.
            |
            | Reset filter:
            | Selects every checkbox and restores the complete chart data.
            |
            */


            /*
                Create the Unselect all button.
            */
            const clearButton = document.createElement('button');

            clearButton.type = 'button';
            clearButton.className =
                'dashboard-chart-filter-action-button secondary';

            clearButton.textContent = 'Unselect all';


            /*
                Clear every checkbox and update the corresponding chart.
            */
            clearButton.addEventListener('click', () => {
                panel
                    .querySelectorAll('input[type="checkbox"]')
                    .forEach((checkbox) => {
                        checkbox.checked = false;
                    });

                /*
                    Update the chart immediately using the new empty selection.
                */
                updateFilteredChart(
                    panelId,
                    chartId,
                    labels,
                    data,
                    datasetLabel,
                    legendId
                );
            });


            /*
                Create the Reset filter button.
            */
            const resetButton = document.createElement('button');

            resetButton.type = 'button';
            resetButton.className =
                'dashboard-chart-filter-action-button primary';

            resetButton.textContent = 'Reset filter';


            /*
                Select every checkbox and restore all chart information.
            */
            resetButton.addEventListener('click', () => {
                panel
                    .querySelectorAll('input[type="checkbox"]')
                    .forEach((checkbox) => {
                        checkbox.checked = true;
                    });

                /*
                    Update the chart immediately with the complete dataset.
                */
                updateFilteredChart(
                    panelId,
                    chartId,
                    labels,
                    data,
                    datasetLabel,
                    legendId
                );
            });


            /*
                Add both buttons to the filter actions container.
            */
            actions.appendChild(clearButton);
            actions.appendChild(resetButton);


            /*
                Add the actions container to the dropdown filter panel.
            */
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
            Warranty dashboard charts.
        */
        createDoughnutChart(
            'warrantiesByCategoryChart',
            warrantiesByCategoryLabels,
            warrantiesByCategoryData,
            'Assets With Warranty',
            'warrantiesByCategoryLegend'
        );

        createBarChart(
            'warrantyStatusChart',
            warrantyStatusLabels,
            warrantyStatusData,
            'Warranty Status'
        );

        /*
            Maintenance dashboard charts.
        */
        createDoughnutChart(
            'assignedMaintenancesByPlantChart',
            assignedMaintenancesByPlantLabels,
            assignedMaintenancesByPlantData,
            'Assigned Maintenances',
            'assignedMaintenancesByPlantLegend'
        );

        createBarChart(
            'maintenanceStatusChart',
            maintenanceStatusLabels,
            maintenanceStatusData,
            'Maintenance Status',
            true,
            'logarithmic'
        );

        /*
            Asset Intelligence charts.

            They use the same reusable chart builders as the other dashboard
            views, keeping the interface and resize behavior consistent.
        */
        createBarChart(
            'assetIntelligenceLifecycleChart',
            assetIntelligenceLifecycleLabels,
            assetIntelligenceLifecycleData,
            'Assets by Lifecycle Risk'
        );

        createDoughnutChart(
            'assetIntelligenceTypeChart',
            assetIntelligenceTypeLabels,
            assetIntelligenceTypeData,
            'Findings by Type',
            'assetIntelligenceTypeLegend'
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
        |--------------------------------------------------------------------------
        | Apply plant filter
        |--------------------------------------------------------------------------
        |
        | Submits the plant filter form only after the user confirms the
        | prepared checkbox selection.
        |
        | This prevents the dashboard from reloading every time a plant
        | checkbox is selected or unselected.
        |
        */
        function applyPlantFilter() {
            const form = document.getElementById('plantFilterForm');

            const checkedPlants = document.querySelectorAll(
                '.plant-filter-checkbox:checked'
            );

            /*
                Stop the function if the plant filter form does not exist.
            */
            if (!form) {
                return;
            }

            /*
                Require at least one selected plant.

                The DashboardController currently interprets an empty plant
                selection as all plants selected. This validation prevents
                that behavior from confusing the user.
            */
            if (checkedPlants.length === 0) {
                alert('Please select at least one plant before applying the filter.');
                return;
            }

            /*
                Display the dashboard loading indicator before submitting.
            */
            showDashboardLoading();

            /*
                Submit the form and refresh all dashboard information.
            */
            form.submit();
        }


        /*
        |--------------------------------------------------------------------------
        | Select all plant options
        |--------------------------------------------------------------------------
        |
        | Selects every plant checkbox without submitting the form.
        |
        | The dashboard data will only change after the user clicks
        | the Apply filter button.
        |
        */
        function selectAllPlantFilterOptions() {
            const plantCheckboxes = document.querySelectorAll(
                '.plant-filter-checkbox'
            );

            /*
                Mark every available plant checkbox as selected.
            */
            plantCheckboxes.forEach((checkbox) => {
                checkbox.checked = true;
            });

            /*
                Update the visible dropdown button label.
            */
            updatePlantFilterButtonLabel();
        }


        /*
        |--------------------------------------------------------------------------
        | Unselect all plant options
        |--------------------------------------------------------------------------
        |
        | Clears every plant checkbox without submitting the form.
        |
        | This is useful when the user wants to start with an empty
        | selection and then choose only specific plants.
        |
        */
        function clearPlantFilterSelection() {
            const plantCheckboxes = document.querySelectorAll(
                '.plant-filter-checkbox'
            );

            /*
                Uncheck every available plant option.
            */
            plantCheckboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });

            /*
                Update the visible dropdown button label.
            */
            updatePlantFilterButtonLabel();
        }


        /*
        |--------------------------------------------------------------------------
        | Update plant filter button label
        |--------------------------------------------------------------------------
        |
        | Updates the dropdown button text while the user modifies the
        | checkbox selection.
        |
        | This function only updates the interface.
        | It does not reload or filter the dashboard.
        |
        */
        function updatePlantFilterButtonLabel() {
            const label = document.getElementById('plantFilterButtonLabel');

            const plantCheckboxes = document.querySelectorAll(
                '.plant-filter-checkbox'
            );

            const checkedPlantCheckboxes = document.querySelectorAll(
                '.plant-filter-checkbox:checked'
            );

            /*
                Stop if the button label element does not exist.
            */
            if (!label) {
                return;
            }

            /*
                Display the all-selected message when every plant is checked.
            */
            if (
                plantCheckboxes.length > 0 &&
                checkedPlantCheckboxes.length === plantCheckboxes.length
            ) {
                label.textContent = 'All plants selected';
                return;
            }

            /*
                Display the empty-selection message when no plant is checked.
            */
            if (checkedPlantCheckboxes.length === 0) {
                label.textContent = 'No plants selected';
                return;
            }

            /*
                Display the number of currently selected plants.
            */
            label.textContent =
                checkedPlantCheckboxes.length + ' plant(s) selected';
        }

        /*
        |--------------------------------------------------------------------------
        | Plant checkbox change listeners
        |--------------------------------------------------------------------------
        |
        | Updates the visible plant filter label whenever the user manually
        | checks or unchecks a plant.
        |
        | This does not submit the form or reload the dashboard.
        |
        */
        document.querySelectorAll('.plant-filter-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                updatePlantFilterButtonLabel();
            });
        });

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

        /*
            Asset Intelligence table explorer.

            All findings are already scoped by the global plant filter. These
            local controls only redraw the table, so they never reload the page.
        */
        const assetIntelligenceFindings = @json($assetIntelligenceFindingsForClient);
        const assetIntelligencePageSize = 50;
        let assetIntelligencePage = 1;
        const assetIntelligenceFilters = { itNumber: '', plants: new Set(), findings: new Set(), severities: new Set() };

        function escapeAssetIntelligenceHtml(value) {
            const element = document.createElement('div');
            element.textContent = value ?? '';
            return element.innerHTML;
        }

        function buildAssetIntelligenceChecklist(menuId, buttonId, values, key, allLabel) {
            const menu = document.getElementById(menuId);
            if (!menu) return;
            const uniqueValues = [...new Set(values)].sort();
            /* Every option starts selected, matching the Dashboard checklist pattern. */
            uniqueValues.forEach(value => assetIntelligenceFilters[key].add(value));
            menu.innerHTML = `<div class="dashboard-chart-filter-actions"><button type="button" class="dashboard-chart-filter-action-button secondary">Unselect all</button><button type="button" class="dashboard-chart-filter-action-button primary">Reset filter</button></div>`;
            const actions = menu.querySelectorAll('button');
            actions[0].onclick = () => { assetIntelligenceFilters[key].clear(); menu.querySelectorAll('input[type="checkbox"]').forEach(input => input.checked = false); assetIntelligencePage = 1; renderAssetIntelligenceTable(); };
            actions[1].onclick = () => { uniqueValues.forEach(value => assetIntelligenceFilters[key].add(value)); menu.querySelectorAll('input[type="checkbox"]').forEach(input => input.checked = true); assetIntelligencePage = 1; renderAssetIntelligenceTable(); };
            const list = document.createElement('div');
            list.className = 'dashboard-chart-filter-list';
            uniqueValues.forEach(value => {
                const label = document.createElement('label');
                label.className = 'dashboard-chart-filter-item';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox'; checkbox.value = value; checkbox.checked = true;
                checkbox.addEventListener('change', () => { checkbox.checked ? assetIntelligenceFilters[key].add(value) : assetIntelligenceFilters[key].delete(value); assetIntelligencePage = 1; renderAssetIntelligenceTable(); });
                label.append(checkbox, document.createTextNode(value)); list.appendChild(label);
            });
            menu.appendChild(list);
            document.getElementById(buttonId).dataset.allLabel = allLabel;
        }

        function renderAssetIntelligenceTable() {
            const term = assetIntelligenceFilters.itNumber.toLowerCase();
            const filtered = assetIntelligenceFindings.filter(item =>
                (!term || item.it_number.toLowerCase().includes(term))
                && assetIntelligenceFilters.plants.has(item.plant)
                && assetIntelligenceFilters.findings.has(item.type)
                && assetIntelligenceFilters.severities.has(item.severity)
            );
            const totalPages = Math.max(1, Math.ceil(filtered.length / assetIntelligencePageSize));
            assetIntelligencePage = Math.min(assetIntelligencePage, totalPages);
            const start = (assetIntelligencePage - 1) * assetIntelligencePageSize;
            const rows = filtered.slice(start, start + assetIntelligencePageSize);
            const body = document.getElementById('assetIntelligenceFindingsBody');
            body.innerHTML = rows.length ? rows.map(item => {
                const colors = item.severity === 'Critical' ? 'bg-red-100 text-red-700' : item.severity === 'High' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700';
                const missing = item.missing_fields ? `<div class="mt-1 text-xs text-gray-500">Missing: ${escapeAssetIntelligenceHtml(item.missing_fields)}</div>` : '';
                return `<tr class="border-b border-gray-100 align-top"><td class="px-4 py-3 font-medium text-gray-800">${escapeAssetIntelligenceHtml(item.it_number)}</td><td class="px-4 py-3">${escapeAssetIntelligenceHtml(item.plant)}</td><td class="px-4 py-3"><div class="font-medium text-gray-800">${escapeAssetIntelligenceHtml(item.type)}</div>${missing}</td><td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${colors}">${escapeAssetIntelligenceHtml(item.severity)}</span></td><td class="px-4 py-3 text-gray-600">${escapeAssetIntelligenceHtml(item.recommendation)}</td></tr>`;
            }).join('') : `
                <tr>
                    {{-- Keep enough table height so the filter dropdown is not clipped when there are no results. --}}
                    <td colspan="5" class="h-80 px-4 py-6 align-top text-center text-gray-500">
                        No findings match the current filters.
                    </td>
                </tr>
            `;
            document.getElementById('assetIntelligenceTableSubtitle').textContent = `${filtered.length} of ${assetIntelligenceFindings.length} findings match the current table filters.`;
            document.getElementById('assetIntelligenceRange').textContent = filtered.length ? `Showing ${start + 1}–${Math.min(start + assetIntelligencePageSize, filtered.length)}.` : '';
            const pagination = document.getElementById('assetIntelligencePagination');
            pagination.innerHTML = totalPages > 1 ? `<button type="button" class="dashboard-plant-filter-button" ${assetIntelligencePage === 1 ? 'disabled' : ''}>Previous</button><span class="text-sm text-gray-600">Page ${assetIntelligencePage} of ${totalPages}</span><button type="button" class="dashboard-plant-filter-button" ${assetIntelligencePage === totalPages ? 'disabled' : ''}>Next</button>` : '';
            const buttons = pagination.querySelectorAll('button');
            if (buttons[0]) buttons[0].onclick = () => { assetIntelligencePage--; renderAssetIntelligenceTable(); };
            if (buttons[1]) buttons[1].onclick = () => { assetIntelligencePage++; renderAssetIntelligenceTable(); };
            [['plants', 'assetIntelligencePlantFilterButton'], ['findings', 'assetIntelligenceFindingFilterButton'], ['severities', 'assetIntelligenceSeverityFilterButton']].forEach(([key, id]) => {
                const button = document.getElementById(id); const selected = assetIntelligenceFilters[key].size;
                const total = document.querySelectorAll(`#${id.replace('Button', 'Menu')} input[type="checkbox"]`).length;
                button.textContent = selected === total ? `${button.dataset.allLabel} ▾` : `${selected} selected ▾`;
            });
        }

        function resetAssetIntelligenceFilters() {
            assetIntelligenceFilters.itNumber = '';
            document.getElementById('assetIntelligenceITNumberFilter').value = '';
            [['plants', 'assetIntelligencePlantFilterMenu'], ['findings', 'assetIntelligenceFindingFilterMenu'], ['severities', 'assetIntelligenceSeverityFilterMenu']].forEach(([key, menuId]) => {
                const inputs = document.querySelectorAll(`#${menuId} input[type="checkbox"]`);
                assetIntelligenceFilters[key].clear();
                inputs.forEach(input => { input.checked = true; assetIntelligenceFilters[key].add(input.value); });
            });
            assetIntelligencePage = 1; renderAssetIntelligenceTable();
        }

        buildAssetIntelligenceChecklist('assetIntelligencePlantFilterMenu', 'assetIntelligencePlantFilterButton', assetIntelligenceFindings.map(item => item.plant), 'plants', 'All plants');
        buildAssetIntelligenceChecklist('assetIntelligenceFindingFilterMenu', 'assetIntelligenceFindingFilterButton', assetIntelligenceFindings.map(item => item.type), 'findings', 'All findings');
        buildAssetIntelligenceChecklist('assetIntelligenceSeverityFilterMenu', 'assetIntelligenceSeverityFilterButton', assetIntelligenceFindings.map(item => item.severity), 'severities', 'All severities');
        document.getElementById('assetIntelligenceITNumberFilter').addEventListener('input', event => { assetIntelligenceFilters.itNumber = event.target.value; assetIntelligencePage = 1; renderAssetIntelligenceTable(); });
        renderAssetIntelligenceTable();

        /*
        |--------------------------------------------------------------------------
        | Dashboard view selector
        |--------------------------------------------------------------------------
        |
        | Switches dashboard content locally. The plant filter stays outside these
        | views, so the current plant selection remains unchanged.
        */
        function showDashboardView(viewName) {
            /* Remember the active view after a global dashboard refresh. */
            sessionStorage.setItem('activeDashboardView', viewName);
            document.querySelectorAll('.dashboard-view').forEach((view) => {
                const isTarget = view.id === `dashboard-view-${viewName}`;

                view.hidden = !isTarget;
                view.classList.toggle('is-active', isTarget);
            });

            document
                .querySelectorAll('.dashboard-view-selector-button')
                .forEach((button) => {
                    const isTarget = button.dataset.dashboardView === viewName;

                    button.classList.toggle('is-active', isTarget);
                    button.setAttribute(
                        'aria-selected',
                        isTarget ? 'true' : 'false'
                    );
                });

            /*
                Chart.js must resize after a hidden view becomes visible.
            */
            window.setTimeout(() => {
                Object.values(dashboardCharts).forEach((chart) => {
                    chart.resize();
                });
            }, 180);
        }

        document
            .querySelectorAll('.dashboard-view-selector-button')
            .forEach((button) => {
                button.addEventListener('click', () => {
                    showDashboardView(button.dataset.dashboardView);
                });
            });

        /* Restore the last selected dashboard view after a plant-filter reload. */
        const storedDashboardView = sessionStorage.getItem('activeDashboardView');
        if (storedDashboardView && document.getElementById(`dashboard-view-${storedDashboardView}`)) {
            showDashboardView(storedDashboardView);
        }
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
    
</x-app-layout>
