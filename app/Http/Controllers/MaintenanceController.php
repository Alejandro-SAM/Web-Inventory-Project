<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    /**
     * Mostrar los mantenimientos disponibles para el usuario.
     *
     * Admin ve todos los activos asignados.
     * User ve únicamente los activos asignados a su ID.
     * Read no tiene acceso.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->user_level, ['Admin', 'User'], true)) {
            abort(403);
        }

        $query = Inventory::query()
            ->with('maintenanceResponsible')
            ->whereNotNull('maintenance_responsible_id');

        /*
         * Admin puede consultar todos los mantenimientos.
         * User solamente puede consultar los asignados a su propia cuenta.
         */
        if ($user->user_level !== 'Admin') {
            $query->where('maintenance_responsible_id', $user->id);
        }

        $maintenanceItems = $query
            ->orderByRaw('next_maintenance IS NULL')
            ->orderBy('next_maintenance')
            ->paginate(50);

        return view('maintenance', compact('maintenanceItems'));
    }

    /**
     * El usuario solicita finalizar un mantenimiento.
     *
     * El mantenimiento todavía no queda completado.
     * Cambia a awaiting hasta que un Admin lo revise.
     */
    public function requestCompletion(Inventory $inventory)
    {
        $user = auth()->user();

        $this->ensureModuleAccess();
        $this->ensureInventoryAccess($inventory);

        if ($inventory->maintenance_status === 'awaiting') {
            return redirect()
                ->route('maintenance.index')
                ->with(
                    'warning',
                    'This maintenance is already awaiting approval.'
                );
        }

        if ($inventory->maintenance_status === 'completed') {
            return redirect()
                ->route('maintenance.index')
                ->with(
                    'warning',
                    'This maintenance has already been completed.'
                );
        }

        DB::transaction(function () use ($inventory, $user) {
            /*
             * Busca el ciclo correspondiente a la fecha actual.
             * Si no existe, lo crea.
             */
            $record = $this->getCurrentMaintenanceRecord($inventory);

            $record->update([
                'status' => 'awaiting',
                'completion_requested_by' => $user->id,
                'completion_requested_at' => now(),

                /*
                 * Se limpian posibles datos de una revisión anterior.
                 */
                'reviewed_by' => null,
                'reviewed_at' => null,
                'completed_at' => null,
                'rejection_reason' => null,
            ]);

            $inventory->update([
                'maintenance_status' => 'awaiting',
            ]);

            ActivityLogger::log(
                module: 'maintenance',
                action: 'completion_requested',
                description:
                    'Maintenance completion was requested for item '
                    . $this->inventoryIdentifier($inventory)
                    . '.',
                targetType: 'inventory',
                targetId: $inventory->id,
                oldValues: [
                    'maintenance_status' => 'pending',
                ],
                newValues: [
                    'maintenance_status' => 'awaiting',
                    'completion_requested_by' => $user->id,
                    'completion_requested_at' => now()->toDateTimeString(),
                ]
            );
        });

        return redirect()
            ->route('maintenance.index')
            ->with(
                'success',
                'Maintenance completion was submitted for administrator approval.'
            );
    }

        /**
         * Aprobar definitivamente el mantenimiento.
         *
         * Solo Admin.
         */
        public function approve(Inventory $inventory)
        {
            $this->ensureAdmin();

            if ($inventory->maintenance_status !== 'awaiting') {
                return redirect()
                    ->route('maintenance.index')
                    ->with(
                        'warning',
                        'This maintenance is not awaiting approval.'
                    );
            }

            $admin = auth()->user();

            DB::transaction(function () use ($inventory, $admin) {
                $record = MaintenanceRecord::query()
                    ->where('inventory_id', $inventory->id)
                    ->where('status', 'awaiting')
                    ->latest()
                    ->first();

                if ($record) {
                    $record->update([
                        'status' => 'completed',
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                        'completed_at' => now(),
                        'rejection_reason' => null,
                    ]);
                }

                $inventory->update([
                    'maintenance_status' => 'completed',
                ]);

                ActivityLogger::log(
                    module: 'maintenance',
                    action: 'approved',
                    description:
                        'Maintenance was approved for item '
                        . $this->inventoryIdentifier($inventory)
                        . '.',
                    targetType: 'inventory',
                    targetId: $inventory->id,
                    oldValues: [
                        'maintenance_status' => 'awaiting',
                    ],
                    newValues: [
                        'maintenance_status' => 'completed',
                        'reviewed_by' => $admin->id,
                        'completed_at' => now()->toDateTimeString(),
                    ]
                );
            });

            return redirect()
                ->route('maintenance.index')
                ->with(
                    'success',
                    'Maintenance approved successfully.'
                );
        }

        /**
         * Rechazar la solicitud de finalización.
         *
         * Solo Admin.
         */
        public function reject(
            Request $request,
            Inventory $inventory
        ) {
            $this->ensureAdmin();

            if ($inventory->maintenance_status !== 'awaiting') {
                return redirect()
                    ->route('maintenance.index')
                    ->with(
                        'warning',
                        'This maintenance is not awaiting approval.'
                    );
            }

            $validated = $request->validate([
                'rejection_reason' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ]);

            $admin = auth()->user();

            DB::transaction(function () use (
                $inventory,
                $admin,
                $validated
            ) {
                $record = MaintenanceRecord::query()
                    ->where('inventory_id', $inventory->id)
                    ->where('status', 'awaiting')
                    ->latest()
                    ->first();

                if ($record) {
                    $record->update([
                        'status' => 'rejected',
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                        'completed_at' => null,
                        'rejection_reason' =>
                            $validated['rejection_reason'] ?? null,
                    ]);
                }

                $inventory->update([
                    'maintenance_status' => 'pending',
                ]);

                ActivityLogger::log(
                    module: 'maintenance',
                    action: 'rejected',
                    description:
                        'Maintenance completion was rejected for item '
                        . $this->inventoryIdentifier($inventory)
                        . '.',
                    targetType: 'inventory',
                    targetId: $inventory->id,
                    oldValues: [
                        'maintenance_status' => 'awaiting',
                    ],
                    newValues: [
                        'maintenance_status' => 'pending',
                        'reviewed_by' => $admin->id,
                        'rejection_reason' =>
                            $validated['rejection_reason'] ?? null,
                    ]
                );
            });

            return redirect()
                ->route('maintenance.index')
                ->with(
                    'success',
                    'Maintenance completion was rejected.'
                );
        }

        /**
         * Programar un nuevo ciclo después de completar el anterior.
         *
         * Solo Admin.
         */
        public function scheduleNext(
            Request $request,
            Inventory $inventory
        ) {
            $this->ensureAdmin();

            $validated = $request->validate([
                'next_maintenance' => [
                    'required',
                    'date',
                ],

                'maintenance_responsible_id' => [
                    'required',
                    'integer',

                    Rule::exists('users', 'id')->where(
                        fn ($query) => $query->where(
                            'is_active',
                            true
                        )
                    ),
                ],
            ]);

            if ($inventory->maintenance_status !== 'completed') {
                return redirect()
                    ->route('maintenance.index')
                    ->with(
                        'warning',
                        'The current maintenance must be completed before scheduling the next one.'
                    );
            }

            DB::transaction(function () use (
                $inventory,
                $validated
            ) {
                $inventory->update([
                    'next_maintenance' =>
                        $validated['next_maintenance'],

                    'maintenance_responsible_id' =>
                        $validated['maintenance_responsible_id'],

                    'maintenance_status' => 'pending',
                ]);

                MaintenanceRecord::create([
                    'inventory_id' => $inventory->id,
                    'maintenance_date' =>
                        $validated['next_maintenance'],

                    'responsible_id' =>
                        $validated['maintenance_responsible_id'],

                    'status' => 'pending',
                ]);

                ActivityLogger::log(
                    module: 'maintenance',
                    action: 'scheduled',
                    description:
                        'A new maintenance cycle was scheduled for item '
                        . $this->inventoryIdentifier($inventory)
                        . '.',
                    targetType: 'inventory',
                    targetId: $inventory->id,
                    oldValues: [
                        'maintenance_status' => 'completed',
                    ],
                    newValues: [
                        'maintenance_status' => 'pending',
                        'next_maintenance' =>
                            $validated['next_maintenance'],

                        'maintenance_responsible_id' =>
                            $validated['maintenance_responsible_id'],
                    ]
                );
            });

            return redirect()
                ->route('maintenance.index')
                ->with(
                    'success',
                    'Next maintenance cycle scheduled successfully.'
                );
        }

    /**
     * Obtiene el ciclo correspondiente al mantenimiento actual.
     *
     * Si hubo un rechazo previo, reutiliza ese mismo ciclo.
     */
    private function getCurrentMaintenanceRecord(
        Inventory $inventory
    ): MaintenanceRecord {
        $maintenanceDate =
            $inventory->next_maintenance?->format('Y-m-d');

        $recordQuery = MaintenanceRecord::query()
            ->where('inventory_id', $inventory->id)
            ->whereIn('status', [
                'pending',
                'rejected',
            ]);

        if ($maintenanceDate !== null) {
            $recordQuery->whereDate(
                'maintenance_date',
                $maintenanceDate
            );
        } else {
            $recordQuery->whereNull('maintenance_date');
        }

        $record = $recordQuery
            ->latest()
            ->first();

        if ($record) {
            return $record;
        }

        return MaintenanceRecord::create([
            'inventory_id' => $inventory->id,
            'maintenance_date' => $maintenanceDate,
            'responsible_id' =>
                $inventory->maintenance_responsible_id,
            'status' => 'pending',
        ]);
    }

    /**
     * Bloquear completamente al nivel Read.
     */
    private function ensureModuleAccess(): void
    {
        if (auth()->user()->user_level === 'Read') {
            abort(
                403,
                'You do not have permission to access maintenance.'
            );
        }
    }

    /**
     * Un User solo puede actuar sobre activos asignados a él.
     */
    private function ensureInventoryAccess(
        Inventory $inventory
    ): void {
        $user = auth()->user();

        if ($user->user_level === 'Admin') {
            return;
        }

        if (
            (int) $inventory->maintenance_responsible_id
            !== (int) $user->id
        ) {
            abort(
                403,
                'This maintenance is not assigned to you.'
            );
        }
    }

    /**
     * Verificar que el usuario sea administrador.
     */
    private function ensureAdmin(): void
    {
        if (auth()->user()->user_level !== 'Admin') {
            abort(
                403,
                'Only administrators can perform this action.'
            );
        }
    }

    /**
     * Obtener un identificador entendible para logs.
     */
    private function inventoryIdentifier(
        Inventory $inventory
    ): string {
        return (string) (
            $inventory->it_internal_number
            ?? $inventory->serial_number
            ?? $inventory->asset_number
            ?? $inventory->id
        );
    }

}