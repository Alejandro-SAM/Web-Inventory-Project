<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\InventoryImport;
use App\Models\Inventory;
use App\Models\InventoryImportRow;
use App\Services\ActivityLogger;
use App\Services\InventoryImportNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        /*
            Inventory page:
            Admin, User and Read can view inventory.
        */

        $inventoryQuery = Inventory::query()
            ->with('creator')
            ->orderBy('created_at', 'desc');

        /*
            Inventory filters
        */

        if ($request->filled('it_internal_number')) {
            $inventoryQuery->where('it_internal_number', 'like', '%' . $request->it_internal_number . '%');
        }

        if ($request->filled('serial_number')) {
            $inventoryQuery->where('serial_number', 'like', '%' . $request->serial_number . '%');
        }

        if ($request->filled('asset_number')) {
            $inventoryQuery->where('asset_number', 'like', '%' . $request->asset_number . '%');
        }

        if ($request->filled('description')) {
            $inventoryQuery->where('description', 'like', '%' . $request->description . '%');
        }

        if ($request->filled('model')) {
            $inventoryQuery->where('model', 'like', '%' . $request->model . '%');
        }

        if ($request->filled('brand')) {
            $inventoryQuery->where('brand', 'like', '%' . $request->brand . '%');
        }

        if ($request->filled('category')) {
            $inventoryQuery->where('category', $request->category);
        }

        if ($request->filled('department')) {
            $inventoryQuery->where('department', 'like', '%' . $request->department . '%');
        }

        if ($request->filled('location')) {
            $inventoryQuery->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('business_unit')) {
            $inventoryQuery->where('business_unit', 'like', '%' . $request->business_unit . '%');
        }

        if ($request->filled('plant')) {
            $inventoryQuery->where('plant', 'like', '%' . $request->plant . '%');
        }

        if ($request->filled('end_user')) {
            $inventoryQuery->where('end_user', 'like', '%' . $request->end_user . '%');
        }

        if ($request->filled('employee_id')) {
            $inventoryQuery->where('employee_id', 'like', '%' . $request->employee_id . '%');
        }

        if ($request->filled('responsive')) {
            $inventoryQuery->where('responsive', $request->responsive);
        }

        if ($request->filled('operating_system')) {
            $inventoryQuery->where('operating_system', 'like', '%' . $request->operating_system . '%');
        }

        if ($request->filled('confidentiality')) {
            $inventoryQuery->where('confidentiality', $request->confidentiality);
        }

        if ($request->filled('integrity')) {
            $inventoryQuery->where('integrity', $request->integrity);
        }

        if ($request->filled('availability')) {
            $inventoryQuery->where('availability', $request->availability);
        }

        if ($request->filled('classification')) {
            $inventoryQuery->where('classification', 'like', '%' . $request->classification . '%');
        }

        if ($request->filled('state')) {
            $inventoryQuery->where('state', $request->state);
        }

        if ($request->filled('maintenance_from')) {
            $inventoryQuery->whereDate('next_maintenance', '>=', $request->maintenance_from);
        }

        if ($request->filled('maintenance_to')) {
            $inventoryQuery->whereDate('next_maintenance', '<=', $request->maintenance_to);
        }

        if ($request->filled('warranty_start_from')) {
        $inventoryQuery->whereDate('warranty_start_date', '>=', $request->warranty_start_from);
        }

        if ($request->filled('warranty_start_to')) {
            $inventoryQuery->whereDate('warranty_start_date', '<=', $request->warranty_start_to);
        }

        if ($request->filled('warranty_expiry_from')) {
            $inventoryQuery->whereDate('warranty_expiry_date', '>=', $request->warranty_expiry_from);
        }

        if ($request->filled('warranty_expiry_to')) {
            $inventoryQuery->whereDate('warranty_expiry_date', '<=', $request->warranty_expiry_to);
        }

        if ($request->filled('purchase_origin_country')) {
            $inventoryQuery->where('purchase_origin_country', 'like', '%' . $request->purchase_origin_country . '%');
        }

        /* Only show the creation dates filter to admin users */
        if ($user->user_level === 'Admin') {
            if ($request->filled('created_from')) {
                $inventoryQuery->whereDate('created_at', '>=', $request->created_from);
            }

            if ($request->filled('created_to')) {
                $inventoryQuery->whereDate('created_at', '<=', $request->created_to);
            }
        }
        /* End of creation date filter */

        return view('inventory', [
            'inventoryItems' => $inventoryQuery
                ->paginate(100)
                ->appends($request->query()),

                'categoryOptions' => $this->categoryOptions(),
                'classificationOptions' => $this->classificationOptions(),
        ]);
    }

public function create()
{
    return view('inventory-create', [
        'categoryOptions' => $this->categoryOptions(),
        'classificationOptions' => $this->classificationOptions(),
    ]);
}

//Function to store the the inventory log fields for the purpose of registering the edit action
private function inventoryLogFields(): array
{
    return [
        'it_internal_number',
        'serial_number',
        'asset_number',
        'description',
        'model',
        'brand',
        'category',
        'warranty_start_date',
        'warranty_expiry_date',
        'purchase_origin_country',
        'department',
        'location',
        'business_unit',
        'plant',
        'end_user',
        'responsive',
        'employee_id',
        'next_maintenance',
        'operating_system',
        'confidentiality',
        'integrity',
        'availability',
        'classification',
        'comments',
        'state',
    ];
}
// End of function to store the the inventory log fields for the purpose of registering the edit action

    public function store(Request $request)
    {

    // FORBID READ LEVEL USERS FROM FORCE ADDING ASSETS THROUGH URL OR OTHER MEANS
    if (auth()->user()->user_level === 'Read') {
    abort(403, 'You do not have permission to create inventory records.');
    }

        $validated = $request->validate([
            'it_internal_number' => ['nullable', 'string', 'max:255', 'unique:inventory,it_internal_number'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'asset_number' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'model' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', Rule::in($this->categoryOptions())],
            'warranty_start_date' => ['nullable', 'date'],
            'warranty_expiry_date' => ['nullable', 'date', 'after_or_equal:warranty_start_date'],
            'purchase_origin_country' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'business_unit' => ['nullable', 'string', 'max:255'],
            'plant' => ['nullable', 'string', 'max:255'],
            'end_user' => ['nullable', 'string', 'max:255'],
            'responsive' => ['nullable', 'boolean'],
            'employee_id' => ['nullable', 'string', 'max:255'],
            'next_maintenance' => ['nullable', 'date'],
            'operating_system' => ['nullable', 'string', 'max:255'],
            'confidentiality' => ['nullable', 'integer', 'between:0,3'],
            'integrity' => ['nullable', 'integer', 'between:0,3'],
            'availability' => ['nullable', 'integer', 'between:0,3'],
            'classification' => ['nullable', 'integer', 'between:1,4'],
            'comments' => ['nullable', 'string'],
            'state' => ['required', 'in:active,inactive,maintenance,disposed,lost'],
        ]);

        $validated['responsive'] = $request->has('responsive');
        $validated['created_by'] = auth()->id();

        $inventory = Inventory::create($validated);

        ActivityLogger::log(
                module: 'inventory',
                action: 'created',
                description: 'Item ' . ($inventory->it_internal_number ?? $inventory->asset_number ?? $inventory->serial_number ?? $inventory->id) . ' was created.',
                targetType: 'inventory',
                targetId: $inventory->id,
                oldValues: null,
                newValues: [
                    'it_internal_number' => $inventory->it_internal_number,
                    'serial_number' => $inventory->serial_number,
                    'asset_number' => $inventory->asset_number,
                    'description' => $inventory->description,
                    'model' => $inventory->model,
                    'brand' => $inventory->brand,
                    'category' => $inventory->category,
                    'warranty_start_date' => $inventory->warranty_start_date,
                    'warranty_expiry_date' => $inventory->warranty_expiry_date,
                    'purchase_origin_country' => $inventory->purchase_origin_country,
                    'department' => $inventory->department,
                    'location' => $inventory->location,
                    'business_unit' => $inventory->business_unit,
                    'plant' => $inventory->plant,
                    'end_user' => $inventory->end_user,
                    'responsive' => $inventory->responsive,
                    'employee_id' => $inventory->employee_id,
                    'next_maintenance' => $inventory->next_maintenance,
                    'operating_system' => $inventory->operating_system,
                    'confidentiality' => $inventory->confidentiality,
                    'integrity' => $inventory->integrity,
                    'availability' => $inventory->availability,
                    'classification' => $inventory->classification,
                    'comments' => $inventory->comments,
                    'state' => $inventory->state,
                    ]
                );

        return redirect()
            ->route('inventory')
            ->with('success', 'Asset created successfully.');
    }

    /* FILTER FUNCTIONS */
    private function categoryOptions(): array
    {
        return [
            'Access Controller',
            'Camera',
            'Charger',
            'Clock',
            'Desktop',
            'Digital Video Recorder',
            'Documento',
            'Hard Disk',
            'Hololens VR',
            'Industrial PC',
            'Keyboard',
            'Kit Tools',
            'Laptop',
            'Led',
            'License',
            'Memory',
            'Mobile WiFi',
            'Monitor',
            'NAS',
            'NVR',
            'PatchPanel',
            'PDA',
            'Power Module',
            'Printer',
            'Projector',
            'Radio',
            'Scanner',
            'SD-WAN',
            'Server',
            'Service',
            'Speaker',
            'Switch',
            'Tablet',
            'UPS',
        ];
    }

    private function classificationOptions(): array
    {
        return [
            1 => 'A (TOP SECRET)',
            2 => 'B (SECRET)',
            3 => 'C(INTERNAL)',
            4 => 'D(GENERAL)',
        ];
    }

public function update(Request $request, Inventory $inventory)
{
    /*
        Temporary permission rule:
        Users with Read level cannot edit assets.
    */
    if (auth()->user()->user_level === 'Read') {
        abort(403, 'You do not have permission to edit inventory assets.');
    }
    //END OF PERMISSION RULE

    $oldValues = $inventory->only($this->inventoryLogFields());

    $validated = $request->validate([
        'it_internal_number' => [
            'nullable',
            'string',
            'max:255',
            Rule::unique('inventory', 'it_internal_number')->ignore($inventory->id),
        ],
        'serial_number' => ['nullable', 'string', 'max:255'],
        'asset_number' => ['nullable', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'model' => ['nullable', 'string', 'max:255'],
        'brand' => ['nullable', 'string', 'max:255'],
        'category' => ['nullable', Rule::in($this->categoryOptions())],
        'warranty_start_date' => ['nullable', 'date'],
        'warranty_expiry_date' => ['nullable', 'date', 'after_or_equal:warranty_start_date'],
        'purchase_origin_country' => ['nullable', 'string', 'max:255'],
        'department' => ['nullable', 'string', 'max:255'],
        'location' => ['nullable', 'string', 'max:255'],
        'business_unit' => ['nullable', 'string', 'max:255'],
        'plant' => ['nullable', 'string', 'max:255'],

        'end_user' => ['nullable', 'string', 'max:255'],
        'responsive' => ['nullable', 'boolean'],
        'employee_id' => ['nullable', 'string', 'max:255'],
        'next_maintenance' => ['nullable', 'date'],
        'operating_system' => ['nullable', 'string', 'max:255'],
        'confidentiality' => ['nullable', 'integer', 'between:0,3'],
        'integrity' => ['nullable', 'integer', 'between:0,3'],
        'availability' => ['nullable', 'integer', 'between:0,3'],
        'classification' => ['nullable', 'integer', 'between:1,4'],
        'comments' => ['nullable', 'string'],
        'state' => ['required', 'in:active,inactive,maintenance,disposed,lost'],
    ]);

    $validated['responsive'] = $request->has('responsive');

    $inventory->update($validated);

    $inventory->refresh();

    $newValues = $inventory->only($this->inventoryLogFields());

    $changedOldValues = [];
    $changedNewValues = [];

    foreach ($newValues as $field => $newValue) {
        $oldValue = $oldValues[$field] ?? null;

        if ($oldValue != $newValue) {
            $changedOldValues[$field] = $oldValue;
            $changedNewValues[$field] = $newValue;
        }
    }

    if (!empty($changedNewValues)) {
        ActivityLogger::log(
            module: 'inventory',
            action: 'updated',
            description: 'Item ' . ($inventory->it_internal_number ?? $inventory->asset_number ?? $inventory->serial_number ?? $inventory->id) . ' was updated.',
            targetType: 'inventory',
            targetId: $inventory->id,
            oldValues: $changedOldValues,
            newValues: $changedNewValues
        );
    }

    return redirect()
        ->route('inventory', $request->query())
        ->with('success', 'Asset updated successfully.');
}

    // Process the Excel file and send it for review
    public function importPreview(Request $request)
    {
        /*
            Read users cannot upload inventory files.
        */
        if (auth()->user()->user_level === 'Read') {
            abort(403, 'You do not have permission to upload inventory files.');
        }

        $request->validate([
            'inventory_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $batchId = (string) Str::uuid();

        Excel::import(
            new InventoryImport($batchId, auth()->id()),
            $request->file('inventory_file')
        );

        return redirect()
            ->route('inventory.import.review', $batchId)
            ->with('success', 'File processed. Please review the import results.');
    }

    // General review screen
    public function importReview(string $batchId)
    {
        /*
            Read users cannot review inventory imports.
        */
        if (auth()->user()->user_level === 'Read') {
            abort(403, 'You do not have permission to review inventory imports.');
        }

        $rows = InventoryImportRow::where('batch_id', $batchId)
            ->where('created_by', auth()->id())
            ->orderBy('row_number')
            ->paginate(20);

        $validCount = InventoryImportRow::where('batch_id', $batchId)
            ->where('created_by', auth()->id())
            ->where('status', 'valid')
            ->count();

        $invalidCount = InventoryImportRow::where('batch_id', $batchId)
            ->where('created_by', auth()->id())
            ->where('status', 'invalid')
            ->count();

        $importedCount = InventoryImportRow::where('batch_id', $batchId)
            ->where('created_by', auth()->id())
            ->where('status', 'imported')
            ->count();

        return view('inventory-import-review', [
            'batchId' => $batchId,
            'rows' => $rows,
            'validCount' => $validCount,
            'invalidCount' => $invalidCount,
            'importedCount' => $importedCount,
        ]);
    }

    // See only invalid rows for focused review
    public function reviewInvalidRows(string $batchId)
    {
        /*
            Read users cannot review invalid import rows.
        */
        if (auth()->user()->user_level === 'Read') {
            abort(403, 'You do not have permission to review import errors.');
        }

        $rows = InventoryImportRow::where('batch_id', $batchId)
            ->where('created_by', auth()->id())
            ->where('status', 'invalid')
            ->orderBy('row_number')
            ->paginate(20);

        return view('inventory-import-invalid', [
            'batchId' => $batchId,
            'rows' => $rows,
        ]);
    }

    // Edit and revalidate an invalid row
    public function updateImportRow(Request $request, InventoryImportRow $row, InventoryImportNormalizer $normalizer)
    {
        /*
            Read users cannot edit temporary import rows.
        */
        if (auth()->user()->user_level === 'Read') {
            abort(403, 'You do not have permission to edit import rows.');
        }

        /*
            Prevent users from editing rows uploaded by someone else.
        */
        if ((int) $row->created_by !== (int) auth()->id()) {
            abort(403, 'This import row does not belong to you.');
        }

        $editedData = [
            'it_internal_number' => $request->input('it_internal_number'),
            'serial_number' => $request->input('serial_number'),
            'asset_number' => $request->input('asset_number'),
            'description' => $request->input('description'),
            'model' => $request->input('model'),
            'brand' => $request->input('brand'),
            'category' => $request->input('category'),
            'warranty_start_date' => $request->input('warranty_start_date'),
            'warranty_expiry_date' => $request->input('warranty_expiry_date'),
            'purchase_origin_country' => $request->input('purchase_origin_country'),
            'department' => $request->input('department'),
            'location' => $request->input('location'),
            'business_unit' => $request->input('business_unit'),
            'plant' => $request->input('plant'),
            'end_user' => $request->input('end_user'),
            'responsive' => $request->input('responsive'),
            'employee_id' => $request->input('employee_id'),
            'comments' => $request->input('comments'),
            'next_maintenance' => $request->input('next_maintenance'),
            'operating_system' => $request->input('operating_system'),
            'confidentiality' => $request->input('confidentiality'),
            'integrity' => $request->input('integrity'),
            'availability' => $request->input('availability'),
            'classification' => $request->input('classification'),
            'state' => $request->input('state'),
        ];

        /*
            Re-normalize edited data.
            If errors are solved, the row becomes valid.
        */
        $result = $normalizer->normalize($editedData);

        $row->update([
            'raw_data' => $editedData,
            'normalized_data' => $result['data'],
            'errors' => $result['errors'],
            'status' => $result['status'],
        ]);

        return redirect()
            ->route('inventory.import.invalid', $row->batch_id)
            ->with('success', 'Import row updated successfully.');
    }

    // Confirm import of valid rows
    public function confirmImport(string $batchId)
    {
        /*
            Read users cannot confirm inventory imports.
        */
        if (auth()->user()->user_level === 'Read') {
            abort(403, 'You do not have permission to import inventory assets.');
        }

        $validRows = InventoryImportRow::where('batch_id', $batchId)
            ->where('created_by', auth()->id())
            ->where('status', 'valid')
            ->get();

        $imported = 0;
        $failed = 0;

        DB::transaction(function () use ($validRows, &$imported, &$failed) {
            foreach ($validRows as $row) {
                try {
                    $data = $row->normalized_data;
                    $data['created_by'] = auth()->id();

                    Inventory::create($data);

                /*
                    Remove the temporary row after successful import.
                    Failed rows stay in inventory_import_rows for review.
                */
                $row->delete();

                    $imported++;
                } catch (\Throwable $e) {
                    /*
                        Keep the row in the temporary table if final insert fails.
                    */
                    $row->update([
                        'status' => 'invalid',
                        'errors' => [
                            'Database insert failed: ' . $e->getMessage(),
                        ],
                    ]);

                    $failed++;
                }
            }
        });

        return redirect()
            ->route('inventory')
            ->with('success', "Import completed. Imported: {$imported}. Failed: {$failed}.");
    }

    // Cancel process
    public function cancelImport(string $batchId)
    {
        /*
            Read users cannot cancel inventory imports.
        */
        if (auth()->user()->user_level === 'Read') {
            abort(403, 'You do not have permission to cancel inventory imports.');
        }

        InventoryImportRow::where('batch_id', $batchId)
            ->where('created_by', auth()->id())
            ->whereIn('status', ['valid', 'invalid'])
            ->update([
                'status' => 'cancelled',
            ]);

        return redirect()
            ->route('inventory')
            ->with('success', 'Import cancelled successfully.');
    }

}