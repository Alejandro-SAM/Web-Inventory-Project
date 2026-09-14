<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SelectedInventoryExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    protected array $inventoryIds;

    public function __construct(array $inventoryIds)
    {
        $this->inventoryIds = $inventoryIds;
    }

    /**
     * Retrieve only the assets selected by the user.
     */
    public function collection()
    {
        return Inventory::query()
            ->with('maintenanceResponsible')
            ->whereIn('id', $this->inventoryIds)
            ->orderBy('it_internal_number')
            ->get();
    }

    /**
     * Excel column headings.
     */
    public function headings(): array
    {
        return [
            'IT Internal Number',
            'Serial Number',
            'Asset Number',
            'Description',
            'Model',
            'Brand',
            'Category',
            'Warranty Start Date',
            'Warranty Expiry Date',
            'Purchase Origin Country',
            'Department',
            'Location',
            'BU',
            'Plant',
            'End User',
            'Employee ID',
            'Responsive',
            'Next Maintenance',
            'Maintenance Responsible',
            'Maintenance Status',
            'Operating System',
            'Confidentiality',
            'Integrity',
            'Availability',
            'Classification',
            'Comments',
            'State',
        ];
    }

    /**
     * Transform each inventory record into one Excel row.
     */
    public function map($inventory): array
    {
        $classificationOptions = [
            1 => 'A (TOP SECRET)',
            2 => 'B (SECRET)',
            3 => 'C (INTERNAL)',
            4 => 'D (GENERAL)',
        ];

        $state = match ($inventory->state) {
            'to_be_deleted' => 'To Be Deleted',
            'active' => 'Active',
            'degraded' => 'Degraded',
            'damaged' => 'Damaged',
            'inactive' => 'Inactive',
            'maintenance' => 'Maintenance',
            'disposed' => 'Disposed',
            'lost' => 'Lost',
            default => $inventory->state ?? '',
        };

        return [
            $inventory->it_internal_number ?? '',
            $inventory->serial_number ?? '',
            $inventory->asset_number ?? '',
            $inventory->description ?? '',
            $inventory->model ?? '',
            $inventory->brand ?? '',
            $inventory->category ?? '',

            $inventory->warranty_start_date
                ? $inventory->warranty_start_date->format('Y-m-d')
                : '',

            $inventory->warranty_expiry_date
                ? $inventory->warranty_expiry_date->format('Y-m-d')
                : '',

            $inventory->purchase_origin_country ?? '',
            $inventory->department ?? '',
            $inventory->location ?? '',
            $inventory->business_unit ?? '',
            $inventory->plant ?? '',
            $inventory->end_user ?? '',
            $inventory->employee_id ?? '',

            $inventory->responsive ? 'Yes' : 'No',

            $inventory->next_maintenance
                ? $inventory->next_maintenance->format('Y-m-d')
                : '',

            $inventory->maintenanceResponsible?->name ?? '',
            ucfirst($inventory->effective_maintenance_status ?? ''),
            $inventory->operating_system ?? '',
            $inventory->confidentiality ?? '',
            $inventory->integrity ?? '',
            $inventory->availability ?? '',

            $classificationOptions[$inventory->classification] ?? '',

            $inventory->comments ?? '',
            $state,
        ];
    }
}