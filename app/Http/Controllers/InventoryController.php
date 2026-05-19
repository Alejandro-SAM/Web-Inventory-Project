<?php

namespace App\Http\Controllers;

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
            $inventoryQuery->where('category', 'like', '%' . $request->category . '%');
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

        if ($request->filled('created_from')) {
            $inventoryQuery->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $inventoryQuery->whereDate('created_at', '<=', $request->created_to);
        }

        return view('inventory', [
            'inventoryItems' => $inventoryQuery
                ->paginate(10)
                ->appends($request->query()),
        ]);
    }
}
