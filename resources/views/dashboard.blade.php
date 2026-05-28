<x-app-layout>
    <div class="py-6 scroll-smooth">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Dashboard title --}}
            <h1 class="text-2xl font-bold mb-6">
                Inventory Dashboard
            </h1>

            {{--
                Plant filter.

                This form reloads the dashboard using a GET parameter.
                All dashboard sections are filtered by plant except the "Assets by Plant" chart.
            --}}
            <div class="bg-white p-4 rounded-lg shadow mb-6 max-w-xl">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col md:flex-row md:items-end gap-3">
                    <div class="flex-1">
                        <label for="plant" class="block text-sm font-medium text-gray-600 mb-1">
                            Filter by Plant
                        </label>

                        <select name="plant"
                                id="plant"
                                onchange="showDashboardLoading(); this.form.submit();"
                                class="w-full rounded border-gray-300 text-sm">
                            <option value="">All Plants</option>

                            @foreach ($plants as $plant)
                                <option value="{{ $plant }}" @selected($selectedPlant === $plant)>
                                    {{ $plant }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('dashboard') }}"
                        onclick="showDashboardLoading();"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">
                            Clear
                        </a>
                    </div>
                </form>

                @if (!empty($selectedPlant))
                    <p class="text-xs text-gray-500 mt-3">
                        Current filter: <span class="font-semibold">{{ $selectedPlant }}</span>
                    </p>
                @endif
            </div>

            {{--
                Sticky summary cards.

                These cards remain visible while scrolling so the user can keep
                the main inventory indicators available at all times.
            --}}
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Total Assets</p>
                        <h2 class="text-2xl font-bold">{{ $totalAssets }}</h2>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Active Assets</p>
                        <h2 class="text-2xl font-bold">{{ $activeAssets }}</h2>
                    </div>

                    {{--
                        This card works as a shortcut to the upcoming maintenance section.
                    --}}
                    <a href="#upcoming-maintenance-section"
                    class="dashboard-action-card bg-white p-4 rounded-lg shadow cursor-pointer block">
                        <p class="text-sm text-gray-500">In Maintenance</p>
                        <h2 class="text-2xl font-bold">{{ $maintenanceAssets }}</h2>
                        <p class="text-xs text-gray-400 mt-2">Click to view related assets</p>
                    </a>

                    {{--
                        This card works as a shortcut to the warranties section.
                    --}}
                    <a href="#warranties-expiring-section"
                    class="dashboard-action-card bg-white p-4 rounded-lg shadow cursor-pointer block">
                        <p class="text-sm text-gray-500">Warranties Expiring Soon</p>
                        <h2 class="text-2xl font-bold">{{ $warrantiesExpiringSoonCount }}</h2>
                        <p class="text-xs text-gray-400 mt-2">Click to view related assets</p>
                    </a>
                </div>
            </div>

            {{--
                Charts section.

                Each canvas is used by Chart.js to render a different inventory chart.
            --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-5 rounded-lg shadow">
                    <h2 class="text-lg font-semibold mb-4">Assets by Plant</h2>
                    <canvas id="assetsByPlantChart"></canvas>
                </div>

                <div class="bg-white p-5 rounded-lg shadow">
                    <h2 class="text-lg font-semibold mb-4">Assets by Category</h2>
                    <canvas id="assetsByCategoryChart"></canvas>
                </div>

                <div class="bg-white p-5 rounded-lg shadow">
                    <h2 class="text-lg font-semibold mb-4">Assets by State</h2>
                    <canvas id="assetsByStateChart"></canvas>
                </div>

                <div class="bg-white p-5 rounded-lg shadow">
                    <h2 class="text-lg font-semibold mb-4">Assets by Business Unit</h2>
                    <canvas id="assetsByBusinessUnitChart"></canvas>
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
                <div id="warranties-expiring-section" class="bg-white p-5 rounded-lg shadow scroll-mt-32">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Warranties Expiring Soon</h2>
                            <p class="text-xs text-gray-500">
                                Showing assets expiring within the next 14 days
                            </p>
                        </div>

                        <button type="button"
                                onclick="document.getElementById('all-warranties-modal').showModal()"
                                class="px-3 py-2 text-xs bg-gray-800 text-white rounded hover:bg-gray-700">
                            View More
                        </button>
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
                            <tbody>
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
                        </table>
                    </div>
                </div>

                {{--
                    Upcoming maintenance section.

                    The In Maintenance card redirects the user to this section.
                --}}
                <div id="upcoming-maintenance-section" class="bg-white p-5 rounded-lg shadow scroll-mt-32">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold">Upcoming Maintenance</h2>
                            <p class="text-xs text-gray-500">
                                Showing assets scheduled within the next 14 days
                            </p>
                        </div>

                        <button type="button"
                                onclick="document.getElementById('all-maintenance-modal').showModal()"
                                class="px-3 py-2 text-xs bg-gray-800 text-white rounded hover:bg-gray-700">
                            View More
                        </button>
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
                            <tbody>
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
            Convert Laravel collections into JavaScript arrays.

            Laravel safely passes PHP data to JavaScript.
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
            Reusable bar chart function.

            This avoids repeating the same Chart.js configuration
            for every bar chart in the dashboard.
        */
        function createBarChart(canvasId, labels, data, label) {
            new Chart(document.getElementById(canvasId), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        /*
            Reusable doughnut chart function.

            This is used for values with fewer categories, such as asset states.
        */
        function createDoughnutChart(canvasId, labels, data, label) {
            new Chart(document.getElementById(canvasId), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });
        }

        /*
            Render dashboard charts.
        */
        createBarChart(
            'assetsByPlantChart',
            assetsByPlantLabels,
            assetsByPlantData,
            'Assets by Plant'
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
            'Assets by State'
        );

        createBarChart(
            'assetsByBusinessUnitChart',
            assetsByBusinessUnitLabels,
            assetsByBusinessUnitData,
            'Assets by Business Unit'
        );
    </script>
    <style>
        /*
            Dashboard interactive cards.

            These cards are clickable shortcuts, so the hover state is intentionally
            stronger to make the interaction clearer for the user.
        */
        .dashboard-action-card {
            transition: all 0.25s ease;
            border: 1px solid transparent;
            background: #ffffff;
        }

        .dashboard-action-card:hover {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-color: #3b82f6;
            transform: translateY(-5px);
            box-shadow: 0 22px 40px rgba(37, 99, 235, 0.25);
        }

        .dashboard-action-card:hover p {
            color: #1e40af;
        }

        .dashboard-action-card:hover h2 {
            color: #0f172a;
        }
    </style>

    {{--
        Back to top button.

        This button appears only when the user scrolls down.
    --}}
    <button type="button"
            id="backToTopButton"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="hidden fixed bottom-6 right-6 z-50 px-4 py-3 bg-gray-800 text-white rounded-full shadow-lg hover:bg-gray-700">
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
        class="hidden fixed inset-0 z-50 bg-white/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white px-6 py-4 rounded-lg shadow text-sm text-gray-700">
            Loading dashboard...
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