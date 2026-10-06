<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Carbon;
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

        /*
            Count assets located specifically in IT Room.

            Using $inventoryQuery() keeps this KPI synchronized with the
            dashboard's global plant filter.
        */
        $itRoomAssetsCount = $inventoryQuery()
            ->where('location', 'IT Room')
            ->count();

        $maintenanceAssets = $inventoryQuery()
            ->where('state', 'maintenance')
            ->count();

        /*
            Total warranties expiring from today through the next 14 days.

            This value is NOT limited because it is used to show the real
            number of assets requiring attention.
        */
        $warrantyToday = today();
        $warrantyFourteenDaysLimit = today()->copy()->addDays(14);
        $warrantyThreeMonthsLimit = today()->copy()->addMonths(3);

        $warrantiesExpiringSoonCount = $inventoryQuery()
            ->whereBetween('warranty_expiry_date', [
                $warrantyToday->toDateString(),
                $warrantyFourteenDaysLimit->toDateString(),
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
            Dashboard table: warranties expiring from today through
            the next 14 days.

            Only the first 10 records are displayed in the compact
            dashboard table. The real total is stored separately in
            $warrantiesExpiringSoonCount.
        */
        $warrantiesExpiringSoon = $inventoryQuery()
            ->whereBetween('warranty_expiry_date', [
                $warrantyToday->toDateString(),
                $warrantyFourteenDaysLimit->toDateString(),
            ])
            ->orderBy('warranty_expiry_date')
            ->orderBy('id')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Upcoming maintenance
        |--------------------------------------------------------------------------
        |
        | Main dashboard:
        | - From today through the next 14 days.
        | - Real total is counted separately.
        | - Only the first 10 records are displayed.
        |
        | Full modal:
        | - From today through the next 3 months.
        | - No record limit.
        |
        */

        $maintenanceToday = today();

        $maintenanceFourteenDaysLimit =
            today()->copy()->addDays(14);

        $maintenanceThreeMonthsLimit =
            today()->copy()->addMonths(3);


        /*
            Real total of maintenance records scheduled within
            the next 14 days.
        */
        $upcomingMaintenanceCount = $inventoryQuery()
            ->whereBetween('next_maintenance', [
                $maintenanceToday->toDateString(),
                $maintenanceFourteenDaysLimit->toDateString(),
            ])
            ->count();


        /*
            Compact dashboard table.

            Only 10 records are displayed even when the real
            number of upcoming maintenance records is higher.
        */
        $upcomingMaintenance = $inventoryQuery()
            ->with('maintenanceResponsible')
            ->whereBetween('next_maintenance', [
                $maintenanceToday->toDateString(),
                $maintenanceFourteenDaysLimit->toDateString(),
            ])
            ->orderBy('next_maintenance')
            ->orderBy('id')
            ->limit(10)
            ->get();


        /*
            Complete list for the "View next 3 months" modal.

            No limit is applied here.
        */
        $maintenanceNextThreeMonths = $inventoryQuery()
            ->with('maintenanceResponsible')
            ->whereNotNull('next_maintenance')
            ->whereBetween('next_maintenance', [
                $maintenanceToday->toDateString(),
                $maintenanceThreeMonthsLimit->toDateString(),
            ])
            ->orderBy('next_maintenance')
            ->orderBy('id')
            ->get();

        /*
            Users eligible to receive maintenance assignments.

            Only active User accounts are selectable.
            Admin and Read accounts are intentionally excluded.
        */
        $maintenanceAssignees = User::query()
            ->where('is_active', true)
            ->where('user_level', 'User')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'employee_number',
            ]);

        /*
            Full modal list: warranties expiring from today through
            the next three months.

            Unlike the compact dashboard table, this query has no record limit.
            Therefore the modal shows every asset found in the requested period.
        */
        $warrantiesNextThreeMonths = $inventoryQuery()
            ->whereNotNull('warranty_expiry_date')
            ->whereBetween('warranty_expiry_date', [
                $warrantyToday->toDateString(),
                $warrantyThreeMonthsLimit->toDateString(),
            ])
            ->orderBy('warranty_expiry_date')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Maintenance dashboard charts
        |--------------------------------------------------------------------------
        |
        | These queries use $inventoryQuery(), so they automatically respect the
        | selected plant filter from the dashboard.
        */

        /*
            Doughnut chart data:
            Count maintenance records that already have a responsible account,
            grouped by plant.
        */
        $assignedMaintenancesByPlant = $inventoryQuery()
            ->select('plant', DB::raw('COUNT(*) as total'))
            ->whereNotNull('next_maintenance')
            ->whereNotNull('maintenance_responsible_id')
            ->whereNotNull('plant')
            ->where('plant', '<>', '')
            ->groupBy('plant')
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Maintenance status chart
        |--------------------------------------------------------------------------
        |
        | Pending, overdue and in-review maintenance records come from the active
        | inventory data. Completed maintenance records come from maintenance_records,
        | because approved records are kept as historical records there.
        */

        /*
            Count approved maintenance history records.

            The join with inventory allows the global plant filter to remain consistent
            with every other dashboard metric.
        */
        $completedMaintenancesQuery = DB::table('maintenance_records as maintenance_record')
            ->join(
                'inventory as inventory_asset',
                'inventory_asset.id',
                '=',
                'maintenance_record.inventory_id'
            )
            ->where('maintenance_record.status', 'completed');

        if (!$isAllPlantsSelected && $selectedPlants->isNotEmpty()) {
            $completedMaintenancesQuery->whereIn(
                'inventory_asset.plant',
                $selectedPlants->toArray()
            );
        }

        $completedMaintenancesCount = $completedMaintenancesQuery->count();

        /*
            Active maintenance records still remain in inventory.

            Completed is intentionally excluded here because it is counted from
            maintenance_records above, preventing an approved maintenance from being
            counted twice.
        */
        $maintenanceStatusSummary = $inventoryQuery()
            ->selectRaw(
                "CASE
                    WHEN maintenance_status = 'awaiting' THEN 'In Review'
                    WHEN next_maintenance < ? THEN 'Overdue'
                    ELSE 'Pending'
                END as status, COUNT(*) as total",
                [$maintenanceToday->toDateString()]
            )
            ->whereNotNull('next_maintenance')
            ->where('maintenance_status', '<>', 'completed')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        /*
            Fixed display order for the maintenance status chart.
        */
        $maintenanceStatusLabels = [
            'Overdue',
            'Pending',
            'In Review',
            'Completed',
        ];

        $maintenanceStatusData = collect($maintenanceStatusLabels)
            ->map(function ($status) use (
                $maintenanceStatusSummary,
                $completedMaintenancesCount
            ) {
                if ($status === 'Completed') {
                    return (int) $completedMaintenancesCount;
                }

                return (int) ($maintenanceStatusSummary->get($status)?->total ?? 0);
            })
            ->toArray();

        /*
            Convert the plant chart collection into plain arrays for @json() in Blade.
        */
        $assignedMaintenancesByPlantLabels = $assignedMaintenancesByPlant
            ->pluck('plant')
            ->toArray();

        $assignedMaintenancesByPlantData = $assignedMaintenancesByPlant
            ->pluck('total')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Warranty dashboard charts
        |--------------------------------------------------------------------------
        |
        | These queries use $inventoryQuery(), so they automatically respect the
        | selected plant filter from the dashboard.
        */

        /*
            Doughnut chart data:
            Count assets that have a warranty expiry date, grouped by category.
        */
        $warrantiesByCategory = $inventoryQuery()
            ->select('category', DB::raw('COUNT(*) as total'))
            ->whereNotNull('warranty_expiry_date')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        /*
            Bar chart data:
            - Expired: warranty date is before today.
            - Expiring Soon: warranty expires from today through the next 14 days.
            - Active: warranty expires after the next 14 days.
        */
        $warrantyStatusSummary = $inventoryQuery()
            ->selectRaw(
                "CASE
                    WHEN warranty_expiry_date < ? THEN 'Expired'
                    WHEN warranty_expiry_date <= ? THEN 'Expiring Soon'
                    ELSE 'Active'
                END as status, COUNT(*) as total",
                [
                    $warrantyToday->toDateString(),
                    $warrantyFourteenDaysLimit->toDateString(),
                ]
            )
            ->whereNotNull('warranty_expiry_date')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        /*
            Fixed display order for the warranty status chart.
        */
        $warrantyStatusLabels = [
            'Active',
            'Expiring Soon',
            'Expired',
        ];

        $warrantyStatusData = collect($warrantyStatusLabels)
            ->map(fn ($status) => (int) ($warrantyStatusSummary->get($status)?->total ?? 0))
            ->toArray();

        /*
            Convert the doughnut chart collection into plain arrays for @json() in Blade.
        */
        $warrantiesByCategoryLabels = $warrantiesByCategory
            ->pluck('category')
            ->toArray();

        $warrantiesByCategoryData = $warrantiesByCategory
            ->pluck('total')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Asset Intelligence - zero data-entry analysis
        |--------------------------------------------------------------------------
        |
        | This first version only reads the current inventory fields. It does not
        | create records, change asset information or require new database columns.
        |
        | The result is calculated in memory so every finding always follows the
        | same plant filter selected on the dashboard.
        */
        $intelligenceAssets = $inventoryQuery()->get();

        /*
            Build normalized identifier lists first. Empty values are ignored,
            because an empty field is handled separately as missing information.
        */
        $duplicateValues = [
            'serial_number' => $intelligenceAssets
                ->filter(fn ($asset) => filled(trim((string) $asset->serial_number)))
                ->groupBy(fn ($asset) => mb_strtoupper(trim((string) $asset->serial_number)))
                ->filter(fn ($items) => $items->count() > 1)
                ->keys()
                ->flip(),
            'it_internal_number' => $intelligenceAssets
                ->filter(fn ($asset) => filled(trim((string) $asset->it_internal_number)))
                ->groupBy(fn ($asset) => mb_strtoupper(trim((string) $asset->it_internal_number)))
                ->filter(fn ($items) => $items->count() > 1)
                ->keys()
                ->flip(),
            'asset_number' => $intelligenceAssets
                ->filter(fn ($asset) => filled(trim((string) $asset->asset_number)))
                ->groupBy(fn ($asset) => mb_strtoupper(trim((string) $asset->asset_number)))
                ->filter(fn ($items) => $items->count() > 1)
                ->keys()
                ->flip(),
        ];

        /*
            Only fields that are already part of the normal asset record are
            reviewed here. No new mandatory information is introduced.
        */
        $intelligenceRequiredFields = [
            'it_internal_number' => 'IT Number',
            'serial_number' => 'Serial Number',
            'asset_number' => 'Asset Number',
            'category' => 'Category',
            'brand' => 'Brand',
            'model' => 'Model',
            'plant' => 'Plant',
        ];

        $severityWeight = [
            'Critical' => 1,
            'High' => 2,
            'Medium' => 3,
        ];

        $assetIntelligenceFindings = collect();

        foreach ($intelligenceAssets as $asset) {
            $findings = [];

            /*
                Missing information is reported as one concise finding per asset,
                avoiding several duplicate rows for the same data-quality issue.
            */
            $missingFields = collect($intelligenceRequiredFields)
                ->filter(fn ($label, $field) => blank(trim((string) $asset->{$field})))
                ->values()
                ->all();

            if (!empty($missingFields)) {
                $findings[] = [
                    'type' => 'Incomplete information',
                    'severity' => 'Medium',
                    'recommendation' => 'Validate and complete the missing asset identification details.',
                    'missing_fields' => implode(', ', $missingFields),
                ];
            }

            /*
                Identifier duplicates are high priority because they can affect
                traceability, maintenance evidence and inventory reporting.
            */
            foreach ([
                'serial_number' => 'Duplicate serial number',
                'it_internal_number' => 'Duplicate IT Number',
                'asset_number' => 'Duplicate asset number',
            ] as $field => $type) {
                $value = mb_strtoupper(trim((string) $asset->{$field}));

                if ($value !== '' && isset($duplicateValues[$field][$value])) {
                    $findings[] = [
                        'type' => $type,
                        'severity' => 'High',
                        'recommendation' => 'Verify whether this identifier was duplicated or assigned incorrectly.',
                        'missing_fields' => null,
                    ];
                }
            }

            /*
                Warranty analysis uses the already existing start and expiry dates.
                A malformed date is treated as a data-quality finding instead of
                causing the dashboard to fail.
            */
            $warrantyStart = filled($asset->warranty_start_date)
                ? Carbon::parse($asset->warranty_start_date)->startOfDay()
                : null;
            $warrantyExpiry = filled($asset->warranty_expiry_date)
                ? Carbon::parse($asset->warranty_expiry_date)->startOfDay()
                : null;

            if ($warrantyStart && $warrantyExpiry && $warrantyExpiry->lt($warrantyStart)) {
                $findings[] = [
                    'type' => 'Invalid warranty dates',
                    'severity' => 'High',
                    'recommendation' => 'Validate the warranty start and expiry dates.',
                    'missing_fields' => null,
                ];
            }

            if ($warrantyExpiry) {
                if ($warrantyExpiry->lt($warrantyToday)) {
                    $findings[] = [
                        'type' => 'Warranty expired',
                        'severity' => 'High',
                        'recommendation' => 'Review renewal, replacement or continued-use requirements.',
                        'missing_fields' => null,
                    ];
                } elseif ($warrantyExpiry->lte($warrantyThreeMonthsLimit)) {
                    $findings[] = [
                        'type' => 'Warranty expiring soon',
                        'severity' => 'Medium',
                        'recommendation' => 'Plan the warranty review before the expiry date.',
                        'missing_fields' => null,
                    ];
                }
            }

            /*
                Completed maintenance is excluded because its evidence is kept in
                maintenance_records. Pending and awaiting assets remain in scope.
            */
            if (
                filled($asset->next_maintenance)
                && $asset->maintenance_status !== 'completed'
                && Carbon::parse($asset->next_maintenance)->startOfDay()->lt($maintenanceToday)
            ) {
                $findings[] = [
                    'type' => 'Overdue maintenance',
                    'severity' => 'High',
                    'recommendation' => 'Complete the preventive maintenance and submit the required evidence.',
                    'missing_fields' => null,
                ];
            }

            /*
                Existing physical condition states are converted into lifecycle
                risks. This does not claim end-of-life or vendor support status.
            */
            if ($asset->state === 'damaged') {
                $findings[] = [
                    'type' => 'Damaged asset',
                    'severity' => 'Critical',
                    'recommendation' => 'Review repair, replacement or disposal requirements.',
                    'missing_fields' => null,
                ];
            } elseif ($asset->state === 'degraded') {
                $findings[] = [
                    'type' => 'Degraded asset',
                    'severity' => 'High',
                    'recommendation' => 'Review the equipment condition and plan corrective action.',
                    'missing_fields' => null,
                ];
            }

            foreach ($findings as $finding) {
                $assetIntelligenceFindings->push((object) array_merge($finding, [
                    'asset_id' => $asset->id,
                    'it_number' => $asset->it_internal_number ?: 'N/A',
                    'category' => $asset->category ?: 'N/A',
                    'plant' => $asset->plant ?: 'N/A',
                    'state' => $asset->state ?: 'N/A',
                ]));
            }
        }

        /*
            Classify every asset from its most severe finding. The classification
            is display-only and is never stored back into the inventory table.
        */
        $assetIntelligenceFindingsByAsset = $assetIntelligenceFindings
            ->groupBy('asset_id');

        $assetIntelligenceAssetStatus = $intelligenceAssets
            ->map(function ($asset) use ($assetIntelligenceFindingsByAsset, $severityWeight) {
                $assetFindings = $assetIntelligenceFindingsByAsset
                    ->get($asset->id, collect());

                $highestSeverity = $assetFindings
                    ->sortBy(fn ($finding) => $severityWeight[$finding->severity] ?? 99)
                    ->first()
                    ?->severity;

                $lifecycleStatus = match ($highestSeverity) {
                    'Critical' => 'Critical',
                    'High' => 'At Risk',
                    'Medium' => 'Attention',
                    default => 'Healthy',
                };

                return (object) [
                    'asset_id' => $asset->id,
                    'lifecycle_status' => $lifecycleStatus,
                ];
            });

        $assetIntelligenceLifecycleLabels = ['Healthy', 'Attention', 'At Risk', 'Critical'];
        $assetIntelligenceLifecycleData = collect($assetIntelligenceLifecycleLabels)
            ->map(fn ($status) => $assetIntelligenceAssetStatus
                ->where('lifecycle_status', $status)
                ->count())
            ->toArray();

        $assetIntelligenceTypeSummary = $assetIntelligenceFindings
            ->groupBy('type')
            ->map(fn ($findings) => $findings->count())
            ->sortDesc();

        $assetIntelligenceTypeLabels = $assetIntelligenceTypeSummary->keys()->toArray();
        $assetIntelligenceTypeData = $assetIntelligenceTypeSummary->values()->toArray();
        $assetIntelligenceFindingCount = $assetIntelligenceFindings->count();

        /*
            Send the complete, plant-scoped finding list to the browser. Local
            filters and 50-row pagination then update instantly without a page
            refresh or recalculating the dashboard.
        */
        $assetIntelligenceFindingsForClient = $assetIntelligenceFindings
            ->sortBy(fn ($finding) => $severityWeight[$finding->severity] ?? 99)
            ->values()
            ->map(fn ($finding) => [
                'it_number' => $finding->it_number,
                'plant' => $finding->plant,
                'type' => $finding->type,
                'severity' => $finding->severity,
                'recommendation' => $finding->recommendation,
                'missing_fields' => $finding->missing_fields,
            ])
            ->all();

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
            'warrantiesNextThreeMonths',
            'upcomingMaintenance',
            'upcomingMaintenanceCount',
            'maintenanceNextThreeMonths',
            'maintenanceAssignees',
            'assetsByPlantLabels',
            'assetsByPlantData',
            'assetsByCategoryLabels',
            'assetsByCategoryData',
            'assetsByStateLabels',
            'assetsByStateData',
            'assetsByBusinessUnitLabels',
            'assetsByBusinessUnitData',
            'assignedMaintenancesByPlantLabels',
            'assignedMaintenancesByPlantData',
            'maintenanceStatusLabels',
            'maintenanceStatusData',
            'warrantiesByCategoryLabels',
            'warrantiesByCategoryData',
            'warrantyStatusLabels',
            'warrantyStatusData',
            'plants',
            'selectedPlants',
            'selectedPlantsArray',
            'isAllPlantsSelected',
            'selectedPlantLabel',
            'dashboardThemeStyle',
            'itRoomAssetsCount',
            'assetIntelligenceFindings',
            'assetIntelligenceFindingCount',
            'assetIntelligenceLifecycleLabels',
            'assetIntelligenceLifecycleData',
            'assetIntelligenceTypeLabels',
            'assetIntelligenceTypeData',
            'assetIntelligenceFindingsForClient',
        ));
    }
}
