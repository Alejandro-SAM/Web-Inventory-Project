<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Dashboard title --}}
            <h1 class="text-2xl font-bold mb-6">
                Inventory Dashboard
            </h1>

            {{--
                Summary cards.

                These cards show the most important inventory metrics at a glance.
            --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-lg shadow">
                    <p class="text-sm text-gray-500">Total Assets</p>
                    <h2 class="text-3xl font-bold">{{ $totalAssets }}</h2>
                </div>

                <div class="bg-white p-5 rounded-lg shadow">
                    <p class="text-sm text-gray-500">Active Assets</p>
                    <h2 class="text-3xl font-bold">{{ $activeAssets }}</h2>
                </div>

                <div class="bg-white p-5 rounded-lg shadow">
                    <p class="text-sm text-gray-500">In Maintenance</p>
                    <h2 class="text-3xl font-bold">{{ $maintenanceAssets }}</h2>
                </div>

                <div class="bg-white p-5 rounded-lg shadow">
                    <p class="text-sm text-gray-500">Warranties Expiring Soon</p>
                    <h2 class="text-3xl font-bold">{{ $warrantiesExpiringSoonCount }}</h2>
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
                <div class="bg-white p-5 rounded-lg shadow">
                    <h2 class="text-lg font-semibold mb-4">Warranties Expiring Soon</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">IT Number</th>
                                    <th class="text-left py-2">Category</th>
                                    <th class="text-left py-2">Plant</th>
                                    <th class="text-left py-2">Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($warrantiesExpiringSoon as $asset)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $asset->it_internal_number }}</td>
                                        <td class="py-2">{{ $asset->category }}</td>
                                        <td class="py-2">{{ $asset->plant }}</td>
                                        <td class="py-2">{{ $asset->warranty_expiry_date }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-3 text-gray-500">
                                            No warranties expiring soon.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-lg shadow">
                    <h2 class="text-lg font-semibold mb-4">Upcoming Maintenance</h2>

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

        </div>
    </div>

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
</x-app-layout>