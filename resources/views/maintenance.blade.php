<x-app-layout>
    <div class="app-page">
        <div class="app-page-container">

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form
                id="maintenanceFiltersForm"
                method="GET"
                action="{{ route('maintenance.index') }}"
            ></form>

            <div class="app-card">
                <div class="app-card-header">
                    <strong>Assigned Maintenance</strong>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">
                            {{ $maintenanceItems->total() }} records
                        </span>

                        <a
                            href="{{ route('maintenance.history') }}"
                            class="btn btn-outline-primary btn-sm"
                        >
                            History
                        </a>
                    </div>
                </div>

                <div class="app-card-body">
                    <div class="app-table-wrapper">
                        <table class="table app-table mb-0">
                            <thead>
                                <tr>
                                    <th>IT Number</th>
                                    <th>Serial</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Responsible</th>
                                    <th>Maintenance Date</th>
                                    <th>Maintenance Status</th>
                                    <th>Actions</th>
                                </tr>

                                <tr>
                                    <th>
                                        <input
                                            form="maintenanceFiltersForm"
                                            type="text"
                                            name="it_internal_number"
                                            value="{{ request('it_internal_number') }}"
                                            class="form-control form-control-sm maintenance-text-filter"
                                            placeholder="Search..."
                                            autocomplete="off"
                                        >
                                    </th>

                                    <th>
                                        <input
                                            form="maintenanceFiltersForm"
                                            type="text"
                                            name="serial_number"
                                            value="{{ request('serial_number') }}"
                                            class="form-control form-control-sm maintenance-text-filter"
                                            placeholder="Search..."
                                            autocomplete="off"
                                        >
                                    </th>

                                    <th>
                                        <input
                                            form="maintenanceFiltersForm"
                                            type="text"
                                            name="description"
                                            value="{{ request('description') }}"
                                            class="form-control form-control-sm maintenance-text-filter"
                                            placeholder="Search..."
                                            autocomplete="off"
                                        >
                                    </th>

                                    <th class="col-md-custom">
                                        <select
                                            form="maintenanceFiltersForm"
                                            name="category"
                                            class="form-select form-select-sm maintenance-auto-filter"
                                        >
                                            <option value="">All</option>

                                            @foreach ($categoryOptions as $category)
                                                <option
                                                    value="{{ $category }}"
                                                    {{ request('category') === $category ? 'selected' : '' }}
                                                >
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </th>

                                    <th class="col-md-custom">
                                        <select
                                            form="maintenanceFiltersForm"
                                            name="location"
                                            class="form-select form-select-sm maintenance-auto-filter"
                                        >
                                            <option value="">All</option>

                                            @foreach ($locationOptions as $location)
                                                <option
                                                    value="{{ $location }}"
                                                    {{ request('location') === $location ? 'selected' : '' }}
                                                >
                                                    {{ $location }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </th>

                                    <th class="col-md-custom">
                                        <select
                                            form="maintenanceFiltersForm"
                                            name="maintenance_responsible_id"
                                            class="form-select form-select-sm maintenance-auto-filter"
                                        >
                                            <option value="">All</option>

                                            @foreach ($maintenanceResponsibleOptions as $responsible)
                                                <option
                                                    value="{{ $responsible->id }}"
                                                    {{ (string) request('maintenance_responsible_id') === (string) $responsible->id ? 'selected' : '' }}
                                                >
                                                    {{ $responsible->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </th>

                                    <th>
                                        <input
                                            form="maintenanceFiltersForm"
                                            type="date"
                                            name="maintenance_date"
                                            value="{{ request('maintenance_date') }}"
                                            class="form-control form-control-sm maintenance-auto-filter"
                                        >
                                    </th>

                                    <th class="col-md-custom">
                                        <select
                                            form="maintenanceFiltersForm"
                                            name="maintenance_status"
                                            class="form-select form-select-sm maintenance-auto-filter"
                                        >
                                            <option value="">All</option>
                                            <option value="pending" {{ request('maintenance_status') === 'pending' ? 'selected' : '' }}>
                                                Pending
                                            </option>
                                            <option value="overdue" {{ request('maintenance_status') === 'overdue' ? 'selected' : '' }}>
                                                Overdue
                                            </option>
                                            <option value="awaiting" {{ request('maintenance_status') === 'awaiting' ? 'selected' : '' }}>
                                                Awaiting Approval
                                            </option>
                                        </select>
                                    </th>

                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($maintenanceItems as $item)
                                    @php
                                        $maintenanceStatus = $item->effective_maintenance_status;
                                    @endphp

                                    <tr>
                                        <td>
                                            {{ $item->it_internal_number ?: '—' }}
                                        </td>

                                        <td>
                                            {{ $item->serial_number ?: '—' }}
                                        </td>

                                        <td class="text-wrap-cell">
                                            {{ $item->description ?: '—' }}
                                        </td>

                                        <td>
                                            {{ $item->category ?: '—' }}
                                        </td>

                                        <td>
                                            {{ $item->location ?: '—' }}
                                        </td>

                                        <td>
                                            {{ $item->maintenanceResponsible?->name ?? 'Not assigned' }}
                                        </td>

                                        <td>
                                            {{ $item->next_maintenance?->format('Y-m-d') ?? 'Not scheduled' }}
                                        </td>

                                        <td>
                                        @php
                                            $maintenanceBadge = match ($maintenanceStatus) {
                                                'completed' => 'bg-success',
                                                'awaiting' => 'bg-info text-dark',
                                                'overdue' => 'bg-danger',
                                                default => 'bg-warning text-dark',
                                            };

                                            $maintenanceLabel = match ($maintenanceStatus) {
                                                'completed' => 'Completed',
                                                'awaiting' => 'Awaiting Approval',
                                                'overdue' => 'Overdue',
                                                default => 'Pending',
                                            };
                                        @endphp

                                            <span class="badge {{ $maintenanceBadge }}">
                                                {{ $maintenanceLabel }}
                                            </span>
                                        </td>

                                        <td>
                                        <div class="d-flex align-items-center gap-2 flex-nowrap">
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewMaintenanceModal{{ $item->id }}"
                                        >
                                            View
                                        </button>

                                                @if (
                                                    auth()->user()->user_level === 'User'
                                                    && in_array($maintenanceStatus, ['pending', 'overdue'], true)
                                                )
                                                    <button
                                                        type="button"
                                                        class="btn btn-success btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#finalizeMaintenanceModal{{ $item->id }}"
                                                    >
                                                        Finalize
                                                    </button>
                                                @endif

                                                @if (
                                                    auth()->user()->user_level === 'Admin'
                                                    && $maintenanceStatus === 'awaiting'
                                                )
                                                <button
                                                    type="button"
                                                    class="btn btn-success btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#approveMaintenanceModal{{ $item->id }}"
                                                >
                                                    Approve
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectMaintenanceModal{{ $item->id }}"
                                                >
                                                    Reject
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            No maintenance activities were found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        @foreach ($maintenanceItems as $item)

                        <div
                            class="modal fade"
                            id="viewMaintenanceModal{{ $item->id }}"
                            tabindex="-1"
                            aria-labelledby="viewMaintenanceModalLabel{{ $item->id }}"
                            aria-hidden="true"
                        >
                            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5
                                            class="modal-title"
                                            id="viewMaintenanceModalLabel{{ $item->id }}"
                                        >
                                            Asset Details
                                        </h5>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"
                                        ></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row g-3">

                                            <div class="col-md-4">
                                                <strong>IT Internal Number</strong>
                                                <div>{{ $item->it_internal_number ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Serial Number</strong>
                                                <div>{{ $item->serial_number ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Asset Number</strong>
                                                <div>{{ $item->asset_number ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Description</strong>
                                                <div>{{ $item->description ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Category</strong>
                                                <div>{{ $item->category ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Brand</strong>
                                                <div>{{ $item->brand ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Model</strong>
                                                <div>{{ $item->model ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Plant</strong>
                                                <div>{{ $item->plant ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Business Unit</strong>
                                                <div>{{ $item->business_unit ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Department</strong>
                                                <div>{{ $item->department ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Location</strong>
                                                <div>{{ $item->location ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>End User</strong>
                                                <div>{{ $item->end_user ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Employee ID</strong>
                                                <div>{{ $item->employee_id ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Operating System</strong>
                                                <div>{{ $item->operating_system ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Asset State</strong>
                                                <div>{{ ucfirst(str_replace('_', ' ', $item->state ?? '')) ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Maintenance Responsible</strong>
                                                <div>
                                                    {{ $item->maintenanceResponsible?->name ?? 'Not assigned' }}
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Next Maintenance</strong>
                                                <div>
                                                    {{ $item->next_maintenance?->format('Y-m-d') ?? 'Not scheduled' }}
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Maintenance Status</strong>
                                                <div>
                                                    {{ ucfirst(str_replace('_', ' ', $item->effective_maintenance_status)) }}
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Warranty Start Date</strong>
                                                <div>
                                                    {{ $item->warranty_start_date?->format('Y-m-d') ?? '—' }}
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Warranty Expiry Date</strong>
                                                <div>
                                                    {{ $item->warranty_expiry_date?->format('Y-m-d') ?? '—' }}
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Purchase Origin Country</strong>
                                                <div>{{ $item->purchase_origin_country ?: '—' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Responsive</strong>
                                                <div>{{ $item->responsive ? 'Yes' : 'No' }}</div>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Classification</strong>
                                                <div>{{ $item->classification ?? '—' }}</div>
                                            </div>

                                            <div class="col-12">
                                                <strong>Comments</strong>
                                                <div class="mt-1">
                                                    {{ $item->comments ?: '—' }}
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            data-bs-dismiss="modal"
                                        >
                                            Close
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                            @if (
                                auth()->user()->user_level === 'User'
                                && in_array($item->effective_maintenance_status, ['pending', 'overdue'], true)
                            )
                                <div
                                    class="modal fade"
                                    id="finalizeMaintenanceModal{{ $item->id }}"
                                    tabindex="-1"
                                    aria-labelledby="finalizeMaintenanceModalLabel{{ $item->id }}"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <h5
                                                    class="modal-title"
                                                    id="finalizeMaintenanceModalLabel{{ $item->id }}"
                                                >
                                                    Finish Maintenance
                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"
                                                ></button>
                                            </div>

                                            <div class="modal-body">
                                                <p class="mb-0">
                                                    Finish maintenance for serial
                                                    <strong>
                                                        {{ $item->serial_number ?: 'Not available' }}
                                                    </strong>?
                                                </p>
                                            </div>

                                            <div class="modal-footer">
                                                <button
                                                    type="button"
                                                    class="btn btn-secondary"
                                                    data-bs-dismiss="modal"
                                                >
                                                    No
                                                </button>

                                                <form
                                                    method="POST"
                                                    action="{{ route('maintenance.finalize', $item) }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-success"
                                                    >
                                                        Yes
                                                    </button>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (
                                auth()->user()->user_level === 'Admin'
                                && $item->effective_maintenance_status === 'awaiting'
                            )
                                <div
                                    class="modal fade"
                                    id="approveMaintenanceModal{{ $item->id }}"
                                    tabindex="-1"
                                    aria-labelledby="approveMaintenanceModalLabel{{ $item->id }}"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <h5
                                                    class="modal-title"
                                                    id="approveMaintenanceModalLabel{{ $item->id }}"
                                                >
                                                    Approve Maintenance
                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"
                                                ></button>
                                            </div>

                                            <div class="modal-body">
                                                <p class="mb-0">
                                                    Approve maintenance for serial
                                                    <strong>
                                                        {{ $item->serial_number ?: 'Not available' }}
                                                    </strong>?
                                                </p>
                                            </div>

                                            <div class="modal-footer">
                                                <button
                                                    type="button"
                                                    class="btn btn-secondary"
                                                    data-bs-dismiss="modal"
                                                >
                                                    No
                                                </button>

                                                <form
                                                    method="POST"
                                                    action="{{ route('maintenance.approve', $item) }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-success"
                                                    >
                                                        Yes
                                                    </button>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (
                                auth()->user()->user_level === 'Admin'
                                && $item->effective_maintenance_status === 'awaiting'
                            )
                                <div
                                    class="modal fade"
                                    id="rejectMaintenanceModal{{ $item->id }}"
                                    tabindex="-1"
                                    aria-labelledby="rejectMaintenanceModalLabel{{ $item->id }}"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <h5
                                                    class="modal-title"
                                                    id="rejectMaintenanceModalLabel{{ $item->id }}"
                                                >
                                                    Reject Maintenance
                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"
                                                ></button>
                                            </div>

                                            <div class="modal-body">
                                                <p class="mb-0">
                                                    Reject maintenance for serial
                                                    <strong>
                                                        {{ $item->serial_number ?: 'Not available' }}
                                                    </strong>?
                                                </p>
                                            </div>

                                            <div class="modal-footer">
                                                <button
                                                    type="button"
                                                    class="btn btn-secondary"
                                                    data-bs-dismiss="modal"
                                                >
                                                    No
                                                </button>

                                                <form
                                                    method="POST"
                                                    action="{{ route('maintenance.reject', $item) }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger"
                                                    >
                                                        Yes
                                                    </button>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                    </div>
                </div>

                @if ($maintenanceItems->hasPages())
                    <div class="app-card-footer">
                        {{ $maintenanceItems->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('maintenanceFiltersForm');

            if (!form) {
                return;
            }

            /*
             * Dropdowns y fecha se aplican inmediatamente.
             */
            document
                .querySelectorAll('.maintenance-auto-filter')
                .forEach(function (filter) {
                    filter.addEventListener('change', function () {
                        form.submit();
                    });
                });

            /*
             * Los filtros de escritura esperan 1 segundo después
             * de que el usuario deja de escribir para evitar recargas
             * innecesarias en cada tecla.
             */
            let textFilterTimer;

            document
                .querySelectorAll('.maintenance-text-filter')
                .forEach(function (filter) {
                    filter.addEventListener('input', function () {
                        clearTimeout(textFilterTimer);

                        textFilterTimer = setTimeout(function () {
                            form.submit();
                        }, 1000);
                    });
                });
        });
    </script>
</x-app-layout>