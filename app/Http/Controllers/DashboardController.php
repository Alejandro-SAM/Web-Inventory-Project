<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /*
            Selected plant filter.

            This filter is received from the dashboard view through the GET parameter "plant".
            If no plant is selected, the dashboard shows global inventory data.
        */
        $selectedPlant = request('plant');

        /*
            Plant list for the dashboard filter.

            This list is not filtered because the user must be able to select
            any available plant from the inventory.
        */
        $plants = Inventory::whereNotNull('plant')
            ->select('plant')
            ->distinct()
            ->orderBy('plant')
            ->pluck('plant');
        
        /*
            Reusable query helper.

            This allows the dashboard to apply the selected plant filter
            to most dashboard sections without repeating the same condition.
        */
        $inventoryQuery = function () use ($selectedPlant) {
            $query = Inventory::query();

            if (!empty($selectedPlant)) {
                $query->where('plant', $selectedPlant);
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

            This is useful to know where inventory assets are physically distributed.
        */
        $assetsByPlant = Inventory::select('plant', DB::raw('COUNT(*) as total'))
            ->whereNotNull('plant')
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
            'selectedPlant',
        ));
    }
}