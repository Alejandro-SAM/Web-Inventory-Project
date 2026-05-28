<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /*
            Dashboard summary cards.

            These values are shown at the top of the dashboard to give
            the user a quick overview of the current inventory status.
        */
        $totalAssets = Inventory::count();

        $activeAssets = Inventory::where('state', 'active')->count();

        $maintenanceAssets = Inventory::where('state', 'maintenance')->count();

        /*
            Count assets whose warranty will expire within the next 90 days.

            This helps IT anticipate warranty expiration before assets lose coverage.
        */
        $warrantiesExpiringSoonCount = Inventory::whereBetween('warranty_expiry_date', [
                now()->toDateString(),
                now()->addDays(90)->toDateString()
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
        $assetsByCategory = Inventory::select('category', DB::raw('COUNT(*) as total'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        /*
            Chart data: assets grouped by state.

            This allows IT to quickly compare active, inactive,
            maintenance, disposed or lost assets.
        */
        $assetsByState = Inventory::select('state', DB::raw('COUNT(*) as total'))
            ->whereNotNull('state')
            ->groupBy('state')
            ->orderByDesc('total')
            ->get();

        /*
            Chart data: assets grouped by business unit.

            This helps identify which business units have the largest
            amount of assigned inventory assets.
        */
        $assetsByBusinessUnit = Inventory::select('business_unit', DB::raw('COUNT(*) as total'))
            ->whereNotNull('business_unit')
            ->groupBy('business_unit')
            ->orderByDesc('total')
            ->get();

        /*
            Table data: assets with warranties expiring soon.

            The dashboard only shows the next 10 records to keep the view clean.
        */
        $warrantiesExpiringSoon = Inventory::whereBetween('warranty_expiry_date', [
                now()->toDateString(),
                now()->addDays(90)->toDateString()
            ])
            ->orderBy('warranty_expiry_date')
            ->limit(10)
            ->get();

        /*
            Table data: upcoming maintenance.

            This shows assets with scheduled maintenance in the next 30 days.
        */
        $upcomingMaintenance = Inventory::whereBetween('next_maintenance', [
                now()->toDateString(),
                now()->addDays(30)->toDateString()
            ])
            ->orderBy('next_maintenance')
            ->limit(10)
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
            'upcomingMaintenance',
            'assetsByPlantLabels',
            'assetsByPlantData',
            'assetsByCategoryLabels',
            'assetsByCategoryData',
            'assetsByStateLabels',
            'assetsByStateData',
            'assetsByBusinessUnitLabels',
            'assetsByBusinessUnitData'
        ));
    }
}