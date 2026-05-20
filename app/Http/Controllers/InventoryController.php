<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Models\Inventory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                ->paginate(10)
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'it_internal_number' => ['nullable', 'string', 'max:255', 'unique:inventory,it_internal_number'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'asset_number' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'model' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', Rule::in($this->categoryOptions())],
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

        Inventory::create($validated);

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
}
