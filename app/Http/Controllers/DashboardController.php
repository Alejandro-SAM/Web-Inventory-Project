<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /*
            Multiple plant filter.

            The dashboard now receives plants as an array using the GET parameter:
            plants[]=B&plants[]=D&plants[]=G

            This allows the user to filter by:
            - All plants
            - One plant
            - Several plants
        */
        $requestedPlants = request('plants', []);

        /*
            Safety validation.

            If Laravel receives only one value or an invalid value,
            we force it to behave as an array to avoid errors later.
        */
        if (!is_array($requestedPlants)) {
            $requestedPlants = [$requestedPlants];
        }

        /*
            Plant list for the dashboard filter.

            These plants come directly from the inventory table.
            This avoids manually defining B, D, G, H, MP, etc.
        */
        $plants = Inventory::whereNotNull('plant')
            ->where('plant', '<>', '')
            ->select('plant')
            ->distinct()
            ->orderBy('plant')
            ->pluck('plant');

        /*
            Clean selected plants.

            We trim empty values and only keep plants that really exist
            in the inventory table.
        */
        $selectedPlants = collect($requestedPlants)
            ->map(fn ($plant) => trim((string) $plant))
            ->filter()
            ->intersect($plants)
            ->values();

        /*
            Default behavior.

            If the user did not select anything, the dashboard behaves as if
            all plants were selected.

            This also makes the Reset button simple because it only needs to
            reload the dashboard without query parameters.
        */
        if ($selectedPlants->isEmpty()) {
            $selectedPlants = $plants->values();
        }

        /*
            Plain array version.

            Blade uses this array to check which checkboxes must appear selected.
        */
        $selectedPlantsArray = $selectedPlants->toArray();

        /*
            Check if all available plants are currently selected.

            This helps us avoid adding a WHERE IN when it is not needed.
        */
        $isAllPlantsSelected = $plants->count() > 0
            && $selectedPlants->count() === $plants->count();

        /*
            Friendly label for the dashboard hero.

            This text will be shown inside the Inventory Dashboard card.
        */
        $selectedPlantLabel = $isAllPlantsSelected
            ? 'All plants selected'
            : $selectedPlants->count() . ' plant(s) selected';

        /*
            Reusable query helper.

            Every dashboard metric, chart and table should use this helper
            so the plant checklist filter applies consistently everywhere.
        */
        $inventoryQuery = function () use ($selectedPlants, $isAllPlantsSelected) {
            $query = Inventory::query();

            /*
                If all plants are selected, there is no need to filter.
                If only some plants are selected, we apply WHERE IN.
            */
            if (!$isAllPlantsSelected && $selectedPlants->isNotEmpty()) {
                $query->whereIn('plant', $selectedPlants);
            }

            return $query;
        };

        /*
            Dashboard summary cards.

            These values respect the selected plant filter, except when no plant
            is selected, in which case they show global inventory data.
        */
        $totalAssets = $inventoryQuery()->count();

        $activeAssets = $inventoryQuery()
            ->where('state', 'active')
            ->count();

        $maintenanceAssets = $inventoryQuery()
            ->where('state', 'maintenance')
            ->count();

        /*
            Count assets whose warranty expires within the next 14 days.
        */
        $warrantiesExpiringSoonCount = $inventoryQuery()
            ->whereBetween('warranty_expiry_date', [
                now()->toDateString(),
                now()->addDays(14)->toDateString()
            ])
            ->count();

        /*
            Chart data: assets grouped by plant.

            This chart now also respects the global plant checklist filter.
            Example:
            - If B and D are selected, only B and D will appear.
            - If all plants are selected, all plants will appear.
        */
        $assetsByPlant = $inventoryQuery()
            ->select('plant', DB::raw('COUNT(*) as total'))
            ->whereNotNull('plant')
            ->where('plant', '<>', '')
            ->groupBy('plant')
            ->orderByDesc('total')
            ->get();

        /*
            Chart data: assets grouped by category.

            This shows how many assets belong to each equipment type,
            for example laptops, desktops, monitors, printers, etc.
        */
        $assetsByCategory = $inventoryQuery()
            ->select('category', DB::raw('COUNT(*) as total'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        /*
            Chart data: assets grouped by state.

            This allows IT to quickly compare active, inactive,
            maintenance, disposed or lost assets.
        */
        $assetsByState = $inventoryQuery()
            ->select('state', DB::raw('COUNT(*) as total'))
            ->whereNotNull('state')
            ->groupBy('state')
            ->orderByDesc('total')
            ->get();

        /*
            Chart data: assets grouped by business unit.

            This helps identify which business units have the largest
            amount of assigned inventory assets.
        */
        $assetsByBusinessUnit = $inventoryQuery()
            ->select('business_unit', DB::raw('COUNT(*) as total'))
            ->whereNotNull('business_unit')
            ->groupBy('business_unit')
            ->orderByDesc('total')
            ->get();

        /*
            Dashboard table: warranties expiring within the next 14 days.

            Only 10 records are shown in the main dashboard to avoid making
            the page too long.
        */
        $warrantiesExpiringSoon = $inventoryQuery()
            ->whereBetween('warranty_expiry_date', [
                now()->toDateString(),
                now()->addDays(14)->toDateString()
            ])
            ->orderBy('warranty_expiry_date')
            ->limit(10)
            ->get();

        /*
            Dashboard table: warranties expiring within the next 3 months.
        */
        $warrantiesExpiringThreeMonths = $inventoryQuery()
            ->whereBetween('warranty_expiry_date', [
                now()->addDays(15)->toDateString(),
                now()->addMonths(3)->toDateString()
            ])
            ->orderBy('warranty_expiry_date')
            ->limit(10)
            ->get();

        /*
            Dashboard table: maintenance scheduled within the next 14 days.

            Only 10 records are shown in the main dashboard. The full list
            is available through the "View More" modal.
        */
        $upcomingMaintenance = $inventoryQuery()
            ->whereBetween('next_maintenance', [
                now()->toDateString(),
                now()->addDays(14)->toDateString()
            ])
            ->orderBy('next_maintenance')
            ->limit(10)
            ->get();

        /*
            Dashboard table: maintenance scheduled within the next 3 months.
        */
        $upcomingMaintenanceThreeMonths = $inventoryQuery()
            ->whereBetween('next_maintenance', [
                now()->addDays(15)->toDateString(),
                now()->addMonths(3)->toDateString()
            ])
            ->orderBy('next_maintenance')
            ->limit(10)
            ->get();

        /*
            Full modal list: all future warranty expirations.

            This data is used by the "View More" modal. It is not limited to
            14 days because the purpose is to show the complete upcoming list.
        */
        $allWarrantiesExpiringSoon = $inventoryQuery()
            ->whereNotNull('warranty_expiry_date')
            ->whereDate('warranty_expiry_date', '>=', now()->toDateString())
            ->orderBy('warranty_expiry_date')
            ->get();

        /*
            Full modal list: all future maintenance records.

            This data is used by the "View More" modal. It shows all future
            maintenance dates ordered from closest to furthest.
        */
        $allUpcomingMaintenance = $inventoryQuery()
            ->whereNotNull('next_maintenance')
            ->whereDate('next_maintenance', '>=', now()->toDateString())
            ->orderBy('next_maintenance')
            ->get();

        /*
            Prepare chart values as plain arrays.

            This avoids Blade parsing issues when using collection methods
            directly inside JavaScript with @json().
        */
        $assetsByPlantLabels = $assetsByPlant->pluck('plant')->toArray();
        $assetsByPlantData = $assetsByPlant->pluck('total')->toArray();

        $assetsByCategoryLabels = $assetsByCategory->pluck('category')->toArray();
        $assetsByCategoryData = $assetsByCategory->pluck('total')->toArray();

        $assetsByStateLabels = $assetsByState->pluck('state')->toArray();
        $assetsByStateData = $assetsByState->pluck('total')->toArray();

        $assetsByBusinessUnitLabels = $assetsByBusinessUnit->pluck('business_unit')->toArray();
        $assetsByBusinessUnitData = $assetsByBusinessUnit->pluck('total')->toArray();

        /*
            Dynamic dashboard color theme.

            Instead of manually creating CSS classes for each plant,
            the controller assigns a color palette automatically.

            Rules:
            - All plants selected: blue corporate theme.
            - One plant selected: color based on its position in the plant list.
            - Multiple plants selected: mixed purple/cyan theme.
        */
        $themePalette = [
            [
                'start' => '#0f172a',
                'middle' => '#1e3a8a',
                'end' => '#2563eb',
                'shadow' => 'rgba(37, 99, 235, 0.25)',
            ],
            [
                'start' => '#064e3b',
                'middle' => '#059669',
                'end' => '#34d399',
                'shadow' => 'rgba(5, 150, 105, 0.28)',
            ],
            [
                'start' => '#881337',
                'middle' => '#e11d48',
                'end' => '#fb7185',
                'shadow' => 'rgba(225, 29, 72, 0.28)',
            ],
            [
                'start' => '#581c87',
                'middle' => '#7e22ce',
                'end' => '#a855f7',
                'shadow' => 'rgba(126, 34, 206, 0.28)',
            ],
            [
                'start' => '#7c2d12',
                'middle' => '#ea580c',
                'end' => '#fb923c',
                'shadow' => 'rgba(234, 88, 12, 0.28)',
            ],
            [
                'start' => '#164e63',
                'middle' => '#0891b2',
                'end' => '#22d3ee',
                'shadow' => 'rgba(8, 145, 178, 0.28)',
            ],
        ];

        /*
            Select the dashboard theme based on current plant filter.
        */
        if ($isAllPlantsSelected) {
            /*
                Default corporate color when all plants are selected.
            */
            $dashboardTheme = $themePalette[0];
        } elseif ($selectedPlants->count() === 1) {
            /*
                If only one plant is selected, get its position in the full plant list.

                This lets the system assign colors automatically even if new plants
                appear later in the inventory table.
            */
            $selectedPlantIndex = $plants->values()->search($selectedPlants->first());

            /*
                Use module to keep cycling through available colors.
                We skip index 0 because that is reserved for "all plants".
            */
            $dashboardTheme = $themePalette[
                (($selectedPlantIndex === false ? 0 : $selectedPlantIndex) % (count($themePalette) - 1)) + 1
            ];
        } else {
            /*
                Special theme for multiple selected plants.
            */
            $dashboardTheme = [
                'start' => '#312e81',
                'middle' => '#7c3aed',
                'end' => '#06b6d4',
                'shadow' => 'rgba(124, 58, 237, 0.28)',
            ];
        }

        /*
            Convert the selected theme into inline CSS variables.

            The Blade view will place this directly in the hero style attribute.
        */
        $dashboardThemeStyle = implode('; ', [
            '--dashboard-theme-start: ' . $dashboardTheme['start'],
            '--dashboard-theme-middle: ' . $dashboardTheme['middle'],
            '--dashboard-theme-end: ' . $dashboardTheme['end'],
            '--dashboard-theme-shadow: ' . $dashboardTheme['shadow'],
        ]);

        /*
            Send all calculated values and datasets to the dashboard view.
        */
        return view('dashboard', compact(
            'totalAssets',
            'activeAssets',
            'maintenanceAssets',
            'warrantiesExpiringSoonCount',
            'assetsByPlant',
            'assetsByCategory',
            'assetsByState',
            'assetsByBusinessUnit',
            'warrantiesExpiringSoon',
            'warrantiesExpiringThreeMonths',
            'upcomingMaintenance',
            'upcomingMaintenanceThreeMonths',
            'assetsByPlantLabels',
            'assetsByPlantData',
            'assetsByCategoryLabels',
            'assetsByCategoryData',
            'assetsByStateLabels',
            'assetsByStateData',
            'assetsByBusinessUnitLabels',
            'assetsByBusinessUnitData',
            'allWarrantiesExpiringSoon',
            'allUpcomingMaintenance',
            'plants',
            'selectedPlants',
            'selectedPlantsArray',
            'isAllPlantsSelected',
            'selectedPlantLabel',
            'dashboardThemeStyle',
        ));
    }
}