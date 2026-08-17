<x-app-layout>

    <div class="app-page">
        <div class="app-page-container">
            
    <div class="container mt-4">

<!-- PAGE TITLE -->
<div class="app-page-title">
    <h1 class="mb-0">
        Inventory
    </h1>

    <p class="text-muted mb-0">
        Review, filter and manage IT inventory assets.
    </p>
</div>
        <div class="card app-card">

            <!-- Hidden form for automatic filters -->
            <form id="inventoryFiltersForm" method="GET" action="{{ route('inventory') }}" class="auto-filter-form"></form>
            <!-- Hidden form for automatic filters end -->

            @php
                $selectedCategories = collect(request()->input('category', []))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (string) $value)
                    ->all();

                $selectedPlants = collect(request()->input('plant', []))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (string) $value)
                    ->all();

                $selectedConfidentiality = collect(request()->input('confidentiality', []))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (string) $value)
                    ->all();

                $selectedIntegrity = collect(request()->input('integrity', []))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (string) $value)
                    ->all();

                $selectedAvailability = collect(request()->input('availability', []))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (string) $value)
                    ->all();

                $selectedClassifications = collect(request()->input('classification', []))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (string) $value)
                    ->all();

                $selectedStates = collect(request()->input('state', []))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (string) $value)
                    ->all();

                $plantFilterOptions = collect($plantOptions ?? [])
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->values();

                $ciaFilterOptions = ['0', '1', '2', '3'];

                $stateFilterOptions = [
                    'active' => 'Active',
                    'degraded' => 'Degraded',
                    'damaged' => 'Damaged',
                    'inactive' => 'Inactive',
                    'maintenance' => 'Maintenance',
                    'disposed' => 'Disposed',
                    'lost' => 'Lost',
                    'to_be_deleted' => 'To Be Deleted',
                ];
            @endphp

<!-- BUTTONS ON TOP OF TABLE FOR ACTIONS -->
<div class="card-header d-flex justify-content-between align-items-center">
    <strong>Inventory Assets</strong>

    <div class="d-flex gap-2 align-items-center">
        <button
            type="button"
            class="btn btn-sm btn-outline-info"
            data-bs-toggle="modal"
            data-bs-target="#securityFormatInfoModal"
        >
            Classification information
        </button>

        @if (Auth::user()->user_level !== 'Read')
            <button 
                type="button" 
                class="btn btn-sm btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#addAssetModal"
            >
                Add Asset
            </button>

            <!-- Show Delete All Marked button only for Admin users -->
        @if (Auth::user()->user_level === 'Admin')
            <form
                action="{{ route('inventory.destroy-marked') }}"
                method="POST"
                class="d-inline"
                onsubmit="return confirm(
                    'Are you sure you want to permanently delete all assets marked as To Be Deleted? This action cannot be undone.'
                );"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="btn btn-sm btn-danger"
                >
                    Delete All Marked
                </button>
            </form>

            <!-- Show Upload Excel button -->
            <button
                type="button"
                class="btn btn-sm btn-success"
                data-bs-toggle="modal"
                data-bs-target="#uploadInventoryExcelModal"
            >
                Upload Excel
            </button>
        @endif     
        @endif

        <a href="{{ route('inventory') }}" class="btn btn-sm btn-outline-danger">
            Reset filters
        </a>

                <!-- DROPDOWN LIST OF COLLAPSABLE COLUMNS -->
                    <div class="dropdown">
        <button
            class="btn btn-sm btn-outline-secondary dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            Columns
        </button>

        <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 260px;">
            <div class="small text-muted mb-2">
                Show / hide table columns
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="serial_number" id="toggle_serial_number" checked>
                <label class="form-check-label" for="toggle_serial_number">Serial Number</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="asset_number" id="toggle_asset_number" checked>
                <label class="form-check-label" for="toggle_asset_number">Asset Number</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="description" id="toggle_description" checked>
                <label class="form-check-label" for="toggle_description">Description</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="model" id="toggle_model" checked>
                <label class="form-check-label" for="toggle_model">Model</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="brand" id="toggle_brand" checked>
                <label class="form-check-label" for="toggle_brand">Brand</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="category" id="toggle_category" checked>
                <label class="form-check-label" for="toggle_category">Category</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="warranty_start_date" id="toggle_warranty_start_date" checked>
                <label class="form-check-label" for="toggle_warranty_start_date">Warranty Start Date</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="warranty_expiry_date" id="toggle_warranty_expiry_date" checked>
                <label class="form-check-label" for="toggle_warranty_expiry_date">Warranty Expiry Date</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="purchase_origin_country" id="toggle_purchase_origin_country" checked>
                <label class="form-check-label" for="toggle_purchase_origin_country">Purchase Origin Country</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="department" id="toggle_department" checked>
                <label class="form-check-label" for="toggle_department">Department</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="location" id="toggle_location" checked>
                <label class="form-check-label" for="toggle_location">Location</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="business_unit" id="toggle_business_unit" checked>
                <label class="form-check-label" for="toggle_business_unit">BU</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="plant" id="toggle_plant" checked>
                <label class="form-check-label" for="toggle_plant">Plant</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="end_user" id="toggle_end_user" checked>
                <label class="form-check-label" for="toggle_end_user">End User</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="employee_id" id="toggle_employee_id" checked>
                <label class="form-check-label" for="toggle_employee_id">Employee ID</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="responsive" id="toggle_responsive" checked>
                <label class="form-check-label" for="toggle_responsive">Responsive</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="next_maintenance" id="toggle_next_maintenance" checked>
                <label class="form-check-label" for="toggle_next_maintenance">Next Maintenance</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="maintenance_responsible" id="toggle_maintenance_responsible" checked>
                <label class="form-check-label" for="toggle_maintenance_responsible">Maintenance Responsible</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="maintenance_status" id="toggle_maintenance_status" checked>
                <label class="form-check-label" for="toggle_maintenance_status">Maintenance Status</label>
            </div>

            <div class="form-check">
                <input
                    class="form-check-input inventory-column-toggle"
                    type="checkbox"
                    value="operating_system"
                    id="toggle_operating_system"
                    checked
                >
                <label class="form-check-label" for="toggle_operating_system">
                    OS
                </label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="confidentiality" id="toggle_confidentiality" checked>
                <label class="form-check-label" for="toggle_confidentiality">C</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="integrity" id="toggle_integrity" checked>
                <label class="form-check-label" for="toggle_integrity">I</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="availability" id="toggle_availability" checked>
                <label class="form-check-label" for="toggle_availability">A</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="classification" id="toggle_classification" checked>
                <label class="form-check-label" for="toggle_classification">Classification</label>
            </div>

            <div class="form-check">
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="state" id="toggle_state" checked>
                <label class="form-check-label" for="toggle_state">State</label>
            </div>

            @if (Auth::user()->user_level === 'Admin')
                <div class="form-check">
                    <input class="form-check-input inventory-column-toggle" type="checkbox" value="created_at" id="toggle_created_at" checked>
                    <label class="form-check-label" for="toggle_created_at">Created At</label>
                </div>
            @endif

            <hr class="my-2">

            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="resetInventoryColumns">
                Reset columns
            </button>
        </div>
    </div>
            <!-- END OF DROPDOWN LIST OF COLLAPSABLE COLUMNS -->
            </div>
        </div>

            <div class="card-body app-card-body p-0">
                <div class="table-responsive inventory-table-responsive app-table-wrapper">

                <!-- Inventory table -->
                <table id="inventoryTable" class="table table-hover align-middle wide-table app-table mb-0">

                    <thead class="table-light">
                    <tr>
                        <th class="col-md-custom" data-column="it_internal_number">IT Internal Number</th>
                        <th class="col-md-custom" data-column="serial_number">Serial Number</th>
                        <th class="col-md-custom" data-column="asset_number">Asset Number</th>
                        <th class="col-lg-custom" data-column="description">Description</th>
                        <th class="col-md-custom" data-column="model">Model</th>
                        <th class="col-md-custom" data-column="brand">Brand</th>
                        <th class="col-md-custom" data-column="category">Category</th>
                        <th class="col-md-custom" data-column="warranty_start_date">Warranty Start Date</th>
                        <th class="col-md-custom" data-column="warranty_expiry_date">Warranty Expiry Date</th>
                        <th class="col-md-custom" data-column="purchase_origin_country">Purchase Origin Country</th>
                        <th class="col-md-custom" data-column="department">Department</th>
                        <th class="col-md-custom" data-column="location">Location</th>
                        <th class="col-md-custom" data-column="business_unit">BU</th>
                        <th class="col-md-custom" data-column="plant">Plant</th>
                        <th class="col-md-custom" data-column="end_user">End User</th>
                        <th class="col-md-custom" data-column="employee_id">Employee ID</th>
                        <th class="col-md-custom" data-column="responsive">Responsive</th>
                        <th class="col-md-custom" data-column="next_maintenance">Next Maintenance</th>
                        <th class="col-md-custom" data-column="maintenance_responsible">Maintenance Responsible</th>
                        <th class="col-md-custom" data-column="maintenance_status">Maintenance Status</th>
                        <th class="col-md-custom" data-column="operating_system">OS</th>
                        <th class="col-md-custom" data-column="confidentiality">C</th>
                        <th class="col-md-custom" data-column="integrity">I</th>
                        <th class="col-md-custom" data-column="availability">A</th>
                        <th class="col-md-custom" data-column="classification">Classification</th>
                        <th class="col-md-custom" data-column="state">State</th>

                        @if (Auth::user()->user_level === 'Admin')
                        <th class="col-date-custom" data-column="created_at">Created At</th>
                        @endif

                        <th class="col-actions-custom" data-column="actions">Actions</th>
                        </tr>

                        <tr>
                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="it_internal_number"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="IT number..."
                                    value="{{ request('it_internal_number') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="serial_number"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Serial..."
                                    value="{{ request('serial_number') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="asset_number"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Asset..."
                                    value="{{ request('asset_number') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="description"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Description..."
                                    value="{{ request('description') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="model"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Model..."
                                    value="{{ request('model') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="brand"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Brand..."
                                    value="{{ request('brand') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        data-bs-boundary="viewport"
                                        data-bs-display="static"
                                        aria-expanded="false"
                                    >
                                        {{ count($selectedCategories) > 0 ? count($selectedCategories) . ' selected' : 'All' }}
                                    </button>

                                    <div class="dropdown-menu p-2" style="min-width: 220px; max-height: 260px; overflow-y: auto;">
                                        @foreach ($categoryOptions as $category)
                                            <div class="form-check">
                                                <input
                                                    form="inventoryFiltersForm"
                                                    class="form-check-input auto-filter-select"
                                                    type="checkbox"
                                                    name="category[]"
                                                    value="{{ $category }}"
                                                    id="filter_category_{{ $loop->index }}"
                                                    {{ in_array((string) $category, $selectedCategories, true) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="filter_category_{{ $loop->index }}">
                                                    {{ $category }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </th>

                            <th class="col-md-custom" style="min-width: 220px;">
                                <div class="d-flex gap-2">
                                    <input
                                        form="inventoryFiltersForm"
                                        type="date"
                                        name="warranty_start_from"
                                        class="form-control form-control-sm auto-filter-select"
                                        value="{{ request('warranty_start_from') }}"
                                    >

                                    <input
                                        form="inventoryFiltersForm"
                                        type="date"
                                        name="warranty_start_to"
                                        class="form-control form-control-sm auto-filter-select"
                                        value="{{ request('warranty_start_to') }}"
                                    >
                                </div>
                            </th>

                            <th class="col-md-custom" style="min-width: 220px;">
                                <div class="d-flex gap-2">
                                    <input
                                        form="inventoryFiltersForm"
                                        type="date"
                                        name="warranty_expiry_from"
                                        class="form-control form-control-sm auto-filter-select"
                                        value="{{ request('warranty_expiry_from') }}"
                                    >

                                    <input
                                        form="inventoryFiltersForm"
                                        type="date"
                                        name="warranty_expiry_to"
                                        class="form-control form-control-sm auto-filter-select"
                                        value="{{ request('warranty_expiry_to') }}"
                                    >
                                </div>
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="purchase_origin_country"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Origin country..."
                                    value="{{ request('purchase_origin_country') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="department"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Department..."
                                    value="{{ request('department') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="location"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Location..."
                                    value="{{ request('location') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="business_unit"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="BU..."
                                    value="{{ request('business_unit') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        data-bs-boundary="viewport"
                                        data-bs-display="static"
                                        aria-expanded="false"
                                    >
                                        {{ count($selectedPlants) > 0 ? count($selectedPlants) . ' selected' : 'All' }}
                                    </button>

                                    <div class="dropdown-menu p-2" style="min-width: 180px; max-height: 260px; overflow-y: auto;">
                                        @forelse ($plantFilterOptions as $plant)
                                            <div class="form-check">
                                                <input
                                                    form="inventoryFiltersForm"
                                                    class="form-check-input auto-filter-select"
                                                    type="checkbox"
                                                    name="plant[]"
                                                    value="{{ $plant }}"
                                                    id="filter_plant_{{ $loop->index }}"
                                                    {{ in_array((string) $plant, $selectedPlants, true) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="filter_plant_{{ $loop->index }}">
                                                    {{ $plant }}
                                                </label>
                                            </div>
                                        @empty
                                            <span class="dropdown-item-text text-muted small">No plant options</span>
                                        @endforelse
                                    </div>
                                </div>
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="end_user"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="End user..."
                                    value="{{ request('end_user') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="employee_id"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Employee..."
                                    value="{{ request('employee_id') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <select
                                    form="inventoryFiltersForm"
                                    name="responsive"
                                    class="form-select form-select-sm auto-filter-select"
                                >
                                    <option value="">All</option>
                                    <option value="1" {{ request('responsive') === '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ request('responsive') === '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </th>

                            <th class="col-date-custom" data-column="next_maintenance">
                                <div class="d-flex gap-1">
                                    <input
                                        form="inventoryFiltersForm"
                                        type="date"
                                        name="maintenance_from"
                                        class="form-control form-control-sm auto-filter-select"
                                        value="{{ request('maintenance_from') }}"
                                    >

                                    <input
                                        form="inventoryFiltersForm"
                                        type="date"
                                        name="maintenance_to"
                                        class="form-control form-control-sm auto-filter-select"
                                        value="{{ request('maintenance_to') }}"
                                    >
                                </div>
                            </th>

                            <th class="col-md-custom" data-column="maintenance_responsible">
                                <select
                                    form="inventoryFiltersForm"
                                    name="maintenance_responsible_id"
                                    class="form-select form-select-sm auto-filter-select"
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

                            <th class="col-md-custom" data-column="maintenance_status">
                                <select
                                    form="inventoryFiltersForm"
                                    name="maintenance_status"
                                    class="form-select form-select-sm auto-filter-select"
                                >
                                    <option value="">All</option>
                                    <option value="pending" {{ request('maintenance_status') === 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="overdue" {{ request('maintenance_status') === 'overdue' ? 'selected' : '' }}>
                                        Overdue
                                    </option>
                                    <option value="completed" {{ request('maintenance_status') === 'completed' ? 'selected' : '' }}>
                                        Completed
                                    </option>
                                </select>
                            </th>

                            <th class="col-md-custom">
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="operating_system"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="OS..."
                                    value="{{ request('operating_system') }}"
                                >
                            </th>

                            <th class="col-md-custom">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        data-bs-boundary="viewport"
                                        data-bs-display="static"
                                        aria-expanded="false"
                                    >
                                        {{ count($selectedConfidentiality) > 0 ? count($selectedConfidentiality) . ' selected' : 'All' }}
                                    </button>

                                    <div class="dropdown-menu p-2" style="min-width: 120px;">
                                        @foreach ($ciaFilterOptions as $value)
                                            <div class="form-check">
                                                <input
                                                    form="inventoryFiltersForm"
                                                    class="form-check-input auto-filter-select"
                                                    type="checkbox"
                                                    name="confidentiality[]"
                                                    value="{{ $value }}"
                                                    id="filter_confidentiality_{{ $value }}"
                                                    {{ in_array((string) $value, $selectedConfidentiality, true) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="filter_confidentiality_{{ $value }}">
                                                    {{ $value }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </th>

                            <th class="col-md-custom">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        data-bs-boundary="viewport"
                                        data-bs-display="static"
                                        aria-expanded="false"
                                    >
                                        {{ count($selectedIntegrity) > 0 ? count($selectedIntegrity) . ' selected' : 'All' }}
                                    </button>

                                    <div class="dropdown-menu p-2" style="min-width: 120px;">
                                        @foreach ($ciaFilterOptions as $value)
                                            <div class="form-check">
                                                <input
                                                    form="inventoryFiltersForm"
                                                    class="form-check-input auto-filter-select"
                                                    type="checkbox"
                                                    name="integrity[]"
                                                    value="{{ $value }}"
                                                    id="filter_integrity_{{ $value }}"
                                                    {{ in_array((string) $value, $selectedIntegrity, true) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="filter_integrity_{{ $value }}">
                                                    {{ $value }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </th>

                            <th class="col-md-custom">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        data-bs-boundary="viewport"
                                        data-bs-display="static"
                                        aria-expanded="false"
                                    >
                                        {{ count($selectedAvailability) > 0 ? count($selectedAvailability) . ' selected' : 'All' }}
                                    </button>

                                    <div class="dropdown-menu p-2" style="min-width: 120px;">
                                        @foreach ($ciaFilterOptions as $value)
                                            <div class="form-check">
                                                <input
                                                    form="inventoryFiltersForm"
                                                    class="form-check-input auto-filter-select"
                                                    type="checkbox"
                                                    name="availability[]"
                                                    value="{{ $value }}"
                                                    id="filter_availability_{{ $value }}"
                                                    {{ in_array((string) $value, $selectedAvailability, true) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="filter_availability_{{ $value }}">
                                                    {{ $value }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </th>

                            <th class="col-md-custom">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        data-bs-boundary="viewport"
                                        data-bs-display="static"
                                        aria-expanded="false"
                                    >
                                        {{ count($selectedClassifications) > 0 ? count($selectedClassifications) . ' selected' : 'All' }}
                                    </button>

                                    <div class="dropdown-menu p-2" style="min-width: 220px; max-height: 260px; overflow-y: auto;">
                                        @foreach ($classificationOptions as $value => $label)
                                            <div class="form-check">
                                                <input
                                                    form="inventoryFiltersForm"
                                                    class="form-check-input auto-filter-select"
                                                    type="checkbox"
                                                    name="classification[]"
                                                    value="{{ $value }}"
                                                    id="filter_classification_{{ $value }}"
                                                    {{ in_array((string) $value, $selectedClassifications, true) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="filter_classification_{{ $value }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </th>

                            <th class="col-md-custom">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        data-bs-boundary="viewport"
                                        data-bs-display="static"
                                        aria-expanded="false"
                                    >
                                        {{ count($selectedStates) > 0 ? count($selectedStates) . ' selected' : 'All' }}
                                    </button>

                                    <div class="dropdown-menu p-2" style="min-width: 180px;">
                                        @foreach ($stateFilterOptions as $value => $label)
                                            <div class="form-check">
                                                <input
                                                    form="inventoryFiltersForm"
                                                    class="form-check-input auto-filter-select"
                                                    type="checkbox"
                                                    name="state[]"
                                                    value="{{ $value }}"
                                                    id="filter_state_{{ $value }}"
                                                    {{ in_array((string) $value, $selectedStates, true) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="filter_state_{{ $value }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </th>

                            <!-- Only show Created At filters for Admin users -->
                            @if (Auth::user()->user_level === 'Admin')
                                <th class="col-md-custom" style="min-width: 220px;">
                                    <div class="d-flex gap-2">
                                        <div class="d-flex gap-1">
                                            <input
                                                form="inventoryFiltersForm"
                                                type="date"
                                                name="created_from"
                                                class="form-control form-control-sm auto-filter-select"
                                                value="{{ request('created_from') }}"
                                            >

                                            <input
                                                form="inventoryFiltersForm"
                                                type="date"
                                                name="created_to"
                                                class="form-control form-control-sm auto-filter-select"
                                                value="{{ request('created_to') }}"
                                            >
                                        </div>

                                    <a href="{{ route('inventory') }}" class="btn btn-sm btn-outline-secondary">
                                        Clear
                                    </a>
                                </div>
                            </th>
                            @endif
                            <!--- Blank actions column -->
                                <th></th>
                    </tr>
                    </thead>

                    <tbody>
                        @forelse ($inventoryItems as $item)
                            @php
                                // Normalize the state so it can be used safely as a CSS class
                                $rowState = strtolower(trim($item->state ?? ''));
                            @endphp
                            <tr class="inventory-state-row inventory-state-{{ $rowState }}">
                                <td>{{ $item->it_internal_number ?? 'N/A' }}</td>
                                <td>{{ $item->serial_number ?? 'N/A' }}</td>
                                <td>{{ $item->asset_number ?? 'N/A' }}</td>
                                <td>{{ $item->description ?? 'N/A' }}</td>
                                <td>{{ $item->model ?? 'N/A' }}</td>
                                <td>{{ $item->brand ?? 'N/A' }}</td>
                                <td>{{ $item->category ?? 'N/A' }}</td>
                                <td>{{ $item->warranty_start_date ? $item->warranty_start_date->format('Y-m-d') : 'N/A' }}</td>
                                <td>{{ $item->warranty_expiry_date ? $item->warranty_expiry_date->format('Y-m-d') : 'N/A' }}</td>
                                <td>{{ $item->purchase_origin_country ?? 'N/A' }}</td>
                                <td>{{ $item->department ?? 'N/A' }}</td>
                                <td>{{ $item->location ?? 'N/A' }}</td>
                                <td>{{ $item->business_unit ?? 'N/A' }}</td>
                                <td>{{ $item->plant ?? 'N/A' }}</td>
                                <td>{{ $item->end_user ?? 'N/A' }}</td>
                                <td>{{ $item->employee_id ?? 'N/A' }}</td>

                                <td>
                                    @if ($item->responsive)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>

                                <td>{{ $item->next_maintenance ? $item->next_maintenance->format('Y-m-d') : 'N/A' }}</td>
                                <td>{{ $item->maintenanceResponsible?->name ?? 'Not assigned' }}</td>

                                <td>
                                    @php
                                        $maintenanceStatus = $item->effective_maintenance_status;

                                        $maintenanceBadgeClass = match ($maintenanceStatus) {
                                            'pending' => 'bg-warning text-dark',
                                            'overdue' => 'bg-danger',
                                            'completed' => 'bg-success',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $maintenanceBadgeClass }}">
                                        {{ ucfirst($maintenanceStatus) }}
                                    </span>
                                </td>

                                <td>{{ $item->operating_system ?? 'N/A' }}</td>
                                <td>{{ $item->confidentiality ?? 'N/A' }}</td>
                                <td>{{ $item->integrity ?? 'N/A' }}</td>
                                <td>{{ $item->availability ?? 'N/A' }}</td>
                                <td>{{ $classificationOptions[$item->classification] ?? 'N/A' }}</td>

                                <td>
                                    @php
                                        $state = strtolower($item->state ?? '');

                                        $badgeClass = match ($state) {
                                            'active' => 'bg-success',
                                            'degraded' => 'bg-warning text-dark',
                                            'damaged' => 'bg-danger',
                                            'inactive' => 'bg-secondary',
                                            'maintenance' => 'bg-warning text-dark',
                                            'disposed' => 'bg-danger',
                                            'lost' => 'bg-dark',
                                            'to_be_deleted' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp


                                    <span class="badge {{ $badgeClass }}">
                                        {{ $state === 'to_be_deleted'
                                            ? 'To Be Deleted'
                                            : ucfirst($item->state ?? 'N/A')
                                        }}
                                    </span>
                                </td>

                                <!-- Only show Created At for Admin users -->
                                @if (Auth::user()->user_level === 'Admin')
                                <td>
                                    {{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'N/A' }}
                                </td>
                                @endif

                                <!-- Actions -->
                                <td>
                                    <!-- Hide edit button only for Read users -->
                                    @if (Auth::user()->user_level !== 'Read')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editAssetModal{{ $item->id }}"
                                        >
                                            Edit
                                        </button>
                                    @endif

                                    <!-- Show Print Data button globally -->
                                    <a
                                        href="{{ route('inventory.print-data', $item->id) }}"
                                        class="btn btn-sm btn-info text-white"
                                    >
                                        Print Data
                                    </a>

                                    <!-- Only administrators can see the Delete button -->
                                     <!-- DELETE BUTTON FORM -->
                                    @if (Auth::user()->user_level === 'Admin')
                                        <form
                                            action="{{ route('inventory.destroy', $item->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm(
                                                'Are you sure you want to permanently delete this asset? This action cannot be undone.'
                                            );"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <!-- Only users with write access can edit assets -->
                            @if (Auth::user()->user_level !== 'Read')
                                <div
                                    class="modal fade inventory-form-modal"
                                    id="editAssetModal{{ $item->id }}"
                                    tabindex="-1"
                                    aria-labelledby="editAssetModalLabel{{ $item->id }}"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <div class="modal-content">

                                            <form method="POST" action="{{ route('inventory.update', $item->id) }}">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <div>
                                                        <h5
                                                            class="modal-title"
                                                            id="editAssetModalLabel{{ $item->id }}"
                                                        >
                                                            Edit Asset
                                                        </h5>

                                                        <small class="text-muted">
                                                            {{ $item->it_internal_number ?? 'Asset without internal number' }}
                                                        </small>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        class="btn-close"
                                                        data-bs-dismiss="modal"
                                                        aria-label="Close"
                                                    ></button>
                                                </div>

                                                <div class="modal-body inventory-modal-body">

                                                    <!-- Asset identification -->
                                                    <section class="inventory-form-section">
                                                        <div class="inventory-form-section-header">
                                                            <h6>Asset Identification</h6>
                                                            <span>Main identification and equipment information.</span>
                                                        </div>

                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    IT Internal Number
                                                                </label>

                                                                <input
                                                                    type="text"
                                                                    name="it_internal_number"
                                                                    class="form-control"
                                                                    value="{{ old('it_internal_number', $item->it_internal_number) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    Serial Number
                                                                </label>

                                                                <input
                                                                    type="text"
                                                                    name="serial_number"
                                                                    class="form-control"
                                                                    value="{{ old('serial_number', $item->serial_number) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    Asset Number
                                                                </label>

                                                                <input
                                                                    type="text"
                                                                    name="asset_number"
                                                                    class="form-control"
                                                                    value="{{ old('asset_number', $item->asset_number) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Brand</label>

                                                                <input
                                                                    type="text"
                                                                    name="brand"
                                                                    class="form-control"
                                                                    value="{{ old('brand', $item->brand) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Model</label>

                                                                <input
                                                                    type="text"
                                                                    name="model"
                                                                    class="form-control"
                                                                    value="{{ old('model', $item->model) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Category</label>

                                                                <select name="category" class="form-select">
                                                                    <option value="">Select category</option>

                                                                    @foreach ($categoryOptions as $category)
                                                                        <option
                                                                            value="{{ $category }}"
                                                                            {{ old('category', $item->category) === $category ? 'selected' : '' }}
                                                                        >
                                                                            {{ $category }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    Operating System
                                                                </label>

                                                                <input
                                                                    type="text"
                                                                    name="operating_system"
                                                                    class="form-control"
                                                                    value="{{ old('operating_system', $item->operating_system) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-8">
                                                                <label class="form-label">Description</label>

                                                                <textarea
                                                                    name="description"
                                                                    class="form-control"
                                                                    rows="2"
                                                                >{{ old('description', $item->description) }}</textarea>
                                                            </div>
                                                        </div>
                                                    </section>

                                                    <!-- Assignment and location -->
                                                    <section class="inventory-form-section">
                                                        <div class="inventory-form-section-header">
                                                            <h6>Assignment and Location</h6>
                                                            <span>User, department and physical location of the asset.</span>
                                                        </div>

                                                        @php
                                                            $validPlantOptions = ['B', 'D', 'G', 'H', 'MP'];
                                                            $currentPlant = old('plant', $item->plant);
                                                        @endphp

                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">End User</label>

                                                                <input
                                                                    type="text"
                                                                    name="end_user"
                                                                    class="form-control"
                                                                    value="{{ old('end_user', $item->end_user) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Employee ID</label>

                                                                <input
                                                                    type="text"
                                                                    name="employee_id"
                                                                    class="form-control"
                                                                    value="{{ old('employee_id', $item->employee_id) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Department</label>

                                                                <input
                                                                    type="text"
                                                                    name="department"
                                                                    class="form-control"
                                                                    value="{{ old('department', $item->department) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Location</label>

                                                                <input
                                                                    type="text"
                                                                    name="location"
                                                                    class="form-control"
                                                                    value="{{ old('location', $item->location) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Business Unit</label>

                                                                <input
                                                                    type="text"
                                                                    name="business_unit"
                                                                    class="form-control"
                                                                    value="{{ old('business_unit', $item->business_unit) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Plant</label>

                                                                <select name="plant" class="form-select">
                                                                    <option value="">Select plant</option>

                                                                    {{-- Preserve legacy values so they can be corrected manually --}}
                                                                    @if ($currentPlant && !in_array($currentPlant, $validPlantOptions, true))
                                                                        <option value="{{ $currentPlant }}" selected>
                                                                            Invalid value: {{ $currentPlant }}
                                                                        </option>
                                                                    @endif

                                                                    @foreach ($validPlantOptions as $plant)
                                                                        <option
                                                                            value="{{ $plant }}"
                                                                            {{ $currentPlant === $plant ? 'selected' : '' }}
                                                                        >
                                                                            Plant {{ $plant }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </section>

                                                    <!-- Warranty and purchase -->
                                                    <section class="inventory-form-section">
                                                        <div class="inventory-form-section-header">
                                                            <h6>Warranty and Purchase</h6>
                                                            <span>Warranty period and purchasing origin.</span>
                                                        </div>

                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    Warranty Start Date
                                                                </label>

                                                                <input
                                                                    type="date"
                                                                    name="warranty_start_date"
                                                                    class="form-control"
                                                                    value="{{ old(
                                                                        'warranty_start_date',
                                                                        optional($item->warranty_start_date)->format('Y-m-d')
                                                                    ) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    Warranty Expiry Date
                                                                </label>

                                                                <input
                                                                    type="date"
                                                                    name="warranty_expiry_date"
                                                                    class="form-control"
                                                                    value="{{ old(
                                                                        'warranty_expiry_date',
                                                                        optional($item->warranty_expiry_date)->format('Y-m-d')
                                                                    ) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    Purchase Origin Country
                                                                </label>

                                                                <input
                                                                    type="text"
                                                                    name="purchase_origin_country"
                                                                    class="form-control"
                                                                    value="{{ old(
                                                                        'purchase_origin_country',
                                                                        $item->purchase_origin_country
                                                                    ) }}"
                                                                    placeholder="Example: Mexico"
                                                                >
                                                            </div>
                                                        </div>
                                                    </section>

                                                    <!-- Maintenance -->
                                                    <section class="inventory-form-section">
                                                        <div class="inventory-form-section-header">
                                                            <h6>Maintenance</h6>
                                                            <span>Schedule, assignment and current maintenance status.</span>
                                                        </div>

                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    Next Maintenance
                                                                </label>

                                                                <input
                                                                    type="date"
                                                                    name="next_maintenance"
                                                                    class="form-control"
                                                                    value="{{ old(
                                                                        'next_maintenance',
                                                                        optional($item->next_maintenance)->format('Y-m-d')
                                                                    ) }}"
                                                                >
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    Maintenance Responsible
                                                                </label>

                                                                <select
                                                                    name="maintenance_responsible_id"
                                                                    class="form-select"
                                                                >
                                                                    <option value="">
                                                                        No responsible assigned
                                                                    </option>

                                                                    @foreach ($maintenanceResponsibleOptions as $responsible)
                                                                        <option
                                                                            value="{{ $responsible->id }}"
                                                                            {{ (string) old(
                                                                                'maintenance_responsible_id',
                                                                                $item->maintenance_responsible_id
                                                                            ) === (string) $responsible->id ? 'selected' : '' }}
                                                                        >
                                                                            {{ $responsible->name }}

                                                                            @if (!empty($responsible->employee_number))
                                                                                — {{ $responsible->employee_number }}
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">
                                                                    Maintenance Status
                                                                </label>

                                                                @if (Auth::user()->user_level === 'Admin')
                                                                    <select
                                                                        name="maintenance_status"
                                                                        class="form-select"
                                                                    >
                                                                        <option
                                                                            value="pending"
                                                                            {{ old(
                                                                                'maintenance_status',
                                                                                $item->maintenance_status
                                                                            ) === 'pending' ? 'selected' : '' }}
                                                                        >
                                                                            Pending
                                                                        </option>

                                                                        <option
                                                                            value="completed"
                                                                            {{ old(
                                                                                'maintenance_status',
                                                                                $item->maintenance_status
                                                                            ) === 'completed' ? 'selected' : '' }}
                                                                        >
                                                                            Completed
                                                                        </option>
                                                                    </select>
                                                                @else
                                                                    <input
                                                                        type="text"
                                                                        class="form-control"
                                                                        value="{{ ucfirst($item->effective_maintenance_status) }}"
                                                                        disabled
                                                                    >

                                                                    <small class="form-text text-muted">
                                                                        Only administrators can change this status.
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </section>

                                                    <!-- Status and classification -->
                                                    <section class="inventory-form-section">
                                                        <div class="inventory-form-section-header">
                                                            <h6>Status and Classification</h6>
                                                            <span>Asset status, classification and CIA security values.</span>
                                                        </div>

                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Classification</label>

                                                                <input
                                                                    type="text"
                                                                    class="form-control"
                                                                    value="Automatically assigned"
                                                                    disabled
                                                                >

                                                                <small class="form-text text-muted">
                                                                    Classification is automatically assigned based on Confidentiality, Integrity and Availability.
                                                                </small>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">State</label>

                                                                <select name="state" class="form-select">
                                                                    <option
                                                                        value="active"
                                                                        {{ old('state', $item->state) === 'active' ? 'selected' : '' }}
                                                                    >
                                                                        Active
                                                                    </option>

                                                                    <option
                                                                        value="inactive"
                                                                        {{ old('state', $item->state) === 'inactive' ? 'selected' : '' }}
                                                                    >
                                                                        Inactive
                                                                    </option>

                                                                    <option
                                                                        value="maintenance"
                                                                        {{ old('state', $item->state) === 'maintenance' ? 'selected' : '' }}
                                                                    >
                                                                        Maintenance
                                                                    </option>

                                                                    <option
                                                                        value="degraded"
                                                                        {{ old('state', $item->state) === 'degraded' ? 'selected' : '' }}
                                                                    >
                                                                        Degraded
                                                                    </option>

                                                                    <option
                                                                        value="damaged"
                                                                        {{ old('state', $item->state) === 'damaged' ? 'selected' : '' }}
                                                                    >
                                                                        Damaged
                                                                    </option>

                                                                    <option
                                                                        value="disposed"
                                                                        {{ old('state', $item->state) === 'disposed' ? 'selected' : '' }}
                                                                    >
                                                                        Disposed
                                                                    </option>

                                                                    <option
                                                                        value="lost"
                                                                        {{ old('state', $item->state) === 'lost' ? 'selected' : '' }}
                                                                    >
                                                                        Lost
                                                                    </option>

                                                                    {{-- This state can only be assigned while editing --}}
                                                                    <option
                                                                        value="to_be_deleted"
                                                                        {{ old('state', $item->state) === 'to_be_deleted' ? 'selected' : '' }}
                                                                    >
                                                                        To Be Deleted
                                                                    </option>
                                                                </select>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Confidentiality</label>

                                                                <select name="confidentiality" class="form-select">
                                                                    <option value="">N/A</option>

                                                                    @for ($value = 0; $value <= 3; $value++)
                                                                        <option
                                                                            value="{{ $value }}"
                                                                            {{ old(
                                                                                'confidentiality',
                                                                                $item->confidentiality
                                                                            ) == $value ? 'selected' : '' }}
                                                                        >
                                                                            {{ $value }}
                                                                        </option>
                                                                    @endfor
                                                                </select>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Integrity</label>

                                                                <select name="integrity" class="form-select">
                                                                    <option value="">N/A</option>

                                                                    @for ($value = 0; $value <= 3; $value++)
                                                                        <option
                                                                            value="{{ $value }}"
                                                                            {{ old(
                                                                                'integrity',
                                                                                $item->integrity
                                                                            ) == $value ? 'selected' : '' }}
                                                                        >
                                                                            {{ $value }}
                                                                        </option>
                                                                    @endfor
                                                                </select>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label">Availability</label>

                                                                <select name="availability" class="form-select">
                                                                    <option value="">N/A</option>

                                                                    @for ($value = 0; $value <= 3; $value++)
                                                                        <option
                                                                            value="{{ $value }}"
                                                                            {{ old(
                                                                                'availability',
                                                                                $item->availability
                                                                            ) == $value ? 'selected' : '' }}
                                                                        >
                                                                            {{ $value }}
                                                                        </option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </section>

                                                    <!-- Additional information -->
                                                    <section class="inventory-form-section mb-0">
                                                        <div class="inventory-form-section-header">
                                                            <h6>Additional Information</h6>
                                                            <span>Comments and responsive document information.</span>
                                                        </div>

                                                        <div class="row g-3">
                                                            <div class="col-12">
                                                                <label class="form-label">Comments</label>

                                                                <textarea
                                                                    name="comments"
                                                                    class="form-control"
                                                                    rows="3"
                                                                >{{ old('comments', $item->comments) }}</textarea>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="form-check inventory-responsive-check">
                                                                    <input
                                                                        class="form-check-input"
                                                                        type="checkbox"
                                                                        name="responsive"
                                                                        id="responsive{{ $item->id }}"
                                                                        value="1"
                                                                        {{ old('responsive', $item->responsive) ? 'checked' : '' }}
                                                                    >

                                                                    <label
                                                                        class="form-check-label"
                                                                        for="responsive{{ $item->id }}"
                                                                    >
                                                                        Has responsive document
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </section>

                                                </div>

                                                <div class="modal-footer">
                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary"
                                                        data-bs-dismiss="modal"
                                                    >
                                                        Cancel
                                                    </button>

                                                    <button type="submit" class="btn btn-primary">
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            @endif

                        @empty
                            <tr>
                                <!-- Show a message when no records are found, spanning all columns, also change the colspan based on user level -->
                                <td colspan="{{ Auth::user()->user_level === 'Admin' ? 28 : 27 }}" class="text-center text-muted py-4">
                                    No inventory records found.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>

            <div class="card-footer app-card-footer">
                <div class="inventory-pagination">
                    <div class="inventory-pagination-links">
                        {{ $inventoryItems->onEachSide(1)->links() }}
                    </div>

                    @if ($inventoryItems->lastPage() > 1)
                        <form
                            class="inventory-page-jump"
                            onsubmit="
                                event.preventDefault();

                                const pageInput = this.querySelector('[name=page]');
                                const page = Math.min(
                                    Math.max(parseInt(pageInput.value, 10) || 1, 1),
                                    {{ $inventoryItems->lastPage() }}
                                );

                                const url = new URL(window.location.href);
                                url.searchParams.set('page', page);
                                window.location.href = url.toString();
                            "
                        >
                            <label for="inventoryPageJump">
                                Go to page
                            </label>

                            <input
                                id="inventoryPageJump"
                                name="page"
                                type="number"
                                class="form-control form-control-sm"
                                min="1"
                                max="{{ $inventoryItems->lastPage() }}"
                                value="{{ $inventoryItems->currentPage() }}"
                                aria-label="Page number"
                            >

                            <span>
                                of {{ $inventoryItems->lastPage() }}
                            </span>

                            <button
                                type="submit"
                                class="btn btn-sm btn-primary"
                            >
                                Go
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FOR ADDING ASSETS -->
    @if (Auth::user()->user_level !== 'Read')
        <div
            class="modal fade inventory-form-modal"
            id="addAssetModal"
            tabindex="-1"
            aria-labelledby="addAssetModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="addAssetModalLabel">
                                Add Asset
                            </h5>

                            <small class="text-muted">
                                Register a new IT inventory asset.
                            </small>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <form method="POST" action="{{ route('inventory.store') }}">
                        @csrf

                        <div class="modal-body inventory-modal-body">

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <strong>There were some errors:</strong>

                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Identification -->
                            <section class="inventory-form-section">
                                <div class="inventory-form-section-header">
                                    <h6>Asset Identification</h6>
                                    <span>Main identification and equipment information.</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">IT Internal Number</label>
                                        <input
                                            type="text"
                                            name="it_internal_number"
                                            class="form-control"
                                            value="{{ old('it_internal_number') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Serial Number</label>
                                        <input
                                            type="text"
                                            name="serial_number"
                                            class="form-control"
                                            value="{{ old('serial_number') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Asset Number</label>
                                        <input
                                            type="text"
                                            name="asset_number"
                                            class="form-control"
                                            value="{{ old('asset_number') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Brand</label>
                                        <input
                                            type="text"
                                            name="brand"
                                            class="form-control"
                                            value="{{ old('brand') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Model</label>
                                        <input
                                            type="text"
                                            name="model"
                                            class="form-control"
                                            value="{{ old('model') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Category</label>
                                        <select name="category" class="form-select">
                                            <option value="">Select category</option>

                                            @foreach ($categoryOptions as $category)
                                                <option
                                                    value="{{ $category }}"
                                                    {{ old('category') === $category ? 'selected' : '' }}
                                                >
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Operating System</label>
                                        <input
                                            type="text"
                                            name="operating_system"
                                            class="form-control"
                                            value="{{ old('operating_system') }}"
                                        >
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label">Description</label>
                                        <textarea
                                            name="description"
                                            class="form-control"
                                            rows="2"
                                        >{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </section>

                            <!-- Assignment and location -->
                            <section class="inventory-form-section">
                                <div class="inventory-form-section-header">
                                    <h6>Assignment and Location</h6>
                                    <span>User, department and physical location of the asset.</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">End User</label>
                                        <input
                                            type="text"
                                            name="end_user"
                                            class="form-control"
                                            value="{{ old('end_user') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Employee ID</label>
                                        <input
                                            type="text"
                                            name="employee_id"
                                            class="form-control"
                                            value="{{ old('employee_id') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Department</label>
                                        <input
                                            type="text"
                                            name="department"
                                            class="form-control"
                                            value="{{ old('department') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Location</label>
                                        <input
                                            type="text"
                                            name="location"
                                            class="form-control"
                                            value="{{ old('location') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Business Unit</label>
                                        <input
                                            type="text"
                                            name="business_unit"
                                            class="form-control"
                                            value="{{ old('business_unit') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Plant</label>
                                        <input
                                            type="text"
                                            name="plant"
                                            class="form-control"
                                            value="{{ old('plant') }}"
                                        >
                                    </div>
                                </div>
                            </section>

                            <!-- Warranty -->
                            <section class="inventory-form-section">
                                <div class="inventory-form-section-header">
                                    <h6>Warranty and Purchase</h6>
                                    <span>Warranty period and purchasing origin.</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Warranty Start Date</label>
                                        <input
                                            type="date"
                                            name="warranty_start_date"
                                            class="form-control"
                                            value="{{ old('warranty_start_date') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Warranty Expiry Date</label>
                                        <input
                                            type="date"
                                            name="warranty_expiry_date"
                                            class="form-control"
                                            value="{{ old('warranty_expiry_date') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Purchase Origin Country</label>
                                        <input
                                            type="text"
                                            name="purchase_origin_country"
                                            class="form-control"
                                            value="{{ old('purchase_origin_country') }}"
                                            placeholder="Example: Mexico"
                                        >
                                    </div>
                                </div>
                            </section>

                            <!-- Maintenance -->
                            <section class="inventory-form-section">
                                <div class="inventory-form-section-header">
                                    <h6>Maintenance</h6>
                                    <span>Schedule and assign the next maintenance activity.</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Next Maintenance</label>
                                        <input
                                            type="date"
                                            name="next_maintenance"
                                            class="form-control"
                                            value="{{ old('next_maintenance') }}"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Maintenance Responsible</label>

                                        <select
                                            name="maintenance_responsible_id"
                                            class="form-select"
                                        >
                                            <option value="">No responsible assigned</option>

                                            @foreach ($maintenanceResponsibleOptions as $responsible)
                                                <option
                                                    value="{{ $responsible->id }}"
                                                    {{ (string) old('maintenance_responsible_id') === (string) $responsible->id ? 'selected' : '' }}
                                                >
                                                    {{ $responsible->name }}

                                                    @if (!empty($responsible->employee_number))
                                                        — {{ $responsible->employee_number }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Maintenance Status</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            value="Pending"
                                            disabled
                                        >

                                        <small class="form-text text-muted">
                                            New assets start with pending maintenance status.
                                        </small>
                                    </div>
                                </div>
                            </section>

                            <!-- Status and classification -->
                            <section class="inventory-form-section">
                                <div class="inventory-form-section-header">
                                    <h6>Status and Classification</h6>
                                    <span>Asset status, classification and CIA security values.</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Classification</label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            value="Automatically assigned"
                                            disabled
                                        >

                                        <small class="form-text text-muted">
                                            Classification is automatically assigned based on Confidentiality, Integrity and Availability.
                                        </small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">State</label>

                                        <select name="state" class="form-select">
                                            <option value="active" {{ old('state', 'active') === 'active' ? 'selected' : '' }}>
                                                Active
                                            </option>

                                            <option value="inactive" {{ old('state') === 'inactive' ? 'selected' : '' }}>
                                                Inactive
                                            </option>

                                            <option value="maintenance" {{ old('state') === 'maintenance' ? 'selected' : '' }}>
                                                Maintenance
                                            </option>

                                            <option value="degraded" {{ old('state') === 'degraded' ? 'selected' : '' }}>
                                                Degraded
                                            </option>

                                            <option value="damaged" {{ old('state') === 'damaged' ? 'selected' : '' }}>
                                                Damaged
                                            </option>

                                            <option value="disposed" {{ old('state') === 'disposed' ? 'selected' : '' }}>
                                                Disposed
                                            </option>

                                            <option value="lost" {{ old('state') === 'lost' ? 'selected' : '' }}>
                                                Lost
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Confidentiality</label>

                                        <select name="confidentiality" class="form-select">
                                            <option value="">N/A</option>
                                            <option value="0" {{ old('confidentiality') === '0' ? 'selected' : '' }}>0</option>
                                            <option value="1" {{ old('confidentiality') === '1' ? 'selected' : '' }}>1</option>
                                            <option value="2" {{ old('confidentiality') === '2' ? 'selected' : '' }}>2</option>
                                            <option value="3" {{ old('confidentiality') === '3' ? 'selected' : '' }}>3</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Integrity</label>

                                        <select name="integrity" class="form-select">
                                            <option value="">N/A</option>
                                            <option value="0" {{ old('integrity') === '0' ? 'selected' : '' }}>0</option>
                                            <option value="1" {{ old('integrity') === '1' ? 'selected' : '' }}>1</option>
                                            <option value="2" {{ old('integrity') === '2' ? 'selected' : '' }}>2</option>
                                            <option value="3" {{ old('integrity') === '3' ? 'selected' : '' }}>3</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Availability</label>

                                        <select name="availability" class="form-select">
                                            <option value="">N/A</option>
                                            <option value="0" {{ old('availability') === '0' ? 'selected' : '' }}>0</option>
                                            <option value="1" {{ old('availability') === '1' ? 'selected' : '' }}>1</option>
                                            <option value="2" {{ old('availability') === '2' ? 'selected' : '' }}>2</option>
                                            <option value="3" {{ old('availability') === '3' ? 'selected' : '' }}>3</option>
                                        </select>
                                    </div>
                                </div>
                            </section>

                            <!-- Additional information -->
                            <section class="inventory-form-section mb-0">
                                <div class="inventory-form-section-header">
                                    <h6>Additional Information</h6>
                                    <span>Comments and responsive document information.</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Comments</label>
                                        <textarea
                                            name="comments"
                                            class="form-control"
                                            rows="3"
                                        >{{ old('comments') }}</textarea>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check inventory-responsive-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="responsive"
                                                id="addAssetResponsive"
                                                value="1"
                                                {{ old('responsive') ? 'checked' : '' }}
                                            >

                                            <label
                                                class="form-check-label"
                                                for="addAssetResponsive"
                                            >
                                                Has responsive document
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </section>

                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal"
                            >
                                Cancel
                            </button>

                            <button type="submit" class="btn btn-primary">
                                Save Asset
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <!-- End Add Asset Modal -->
    @endif


    <!-- SECURITY FORMAT INFORMATION MODAL -->
    <div
        class="modal fade"
        id="securityFormatInfoModal"
        tabindex="-1"
        aria-labelledby="securityFormatInfoModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="securityFormatInfoModalLabel">
                        Security Classification Information
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body text-center p-3 overflow-auto">
                    <img
                        src="{{ asset('images/criteriosconfidencialidad.png') }}"
                        alt="Security classification format"
                        class="d-block mx-auto"
                        style="width: 100%; max-width: none; height: auto;"
                    >
                </div>
            </div>
        </div>
    </div>
    <!-- END SECURITY FORMAT INFORMATION MODAL -->

    <!-- Auto-submit filter form on change -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.auto-filter-form');

            forms.forEach(function (form) {
                let typingTimer = null;
                const formId = form.getAttribute('id');

                const textInputs = document.querySelectorAll(`.auto-filter-input[form="${formId}"]`);
                const instantInputs = document.querySelectorAll(`.auto-filter-select[form="${formId}"]`);

                textInputs.forEach(function (input) {
                    input.addEventListener('input', function () {
                        clearTimeout(typingTimer);

                        typingTimer = setTimeout(function () {
                            form.submit();
                        }, 1200);
                    });
                });

                instantInputs.forEach(function (input) {
                    input.addEventListener('change', function () {
                        form.submit();
                    });
                });
            });
        });
    </script>
    <!-- End of Auto-submit filter form on change -->

    <!-- Error Handling for Add Asset Modal -->
     @if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addAssetModalElement = document.getElementById('addAssetModal');

            if (addAssetModalElement) {
                const addAssetModal = new bootstrap.Modal(addAssetModalElement);
                addAssetModal.show();
            }
        });
    </script>
    @endif
    <!-- End of Error Handling for Add Asset Modal -->

    <!-- Column Collapser script -->
     <script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('inventoryTable');

        if (!table) {
            return;
        }

        const storageKey = 'inventory_hidden_columns_{{ Auth::user()->user_level }}';
        const toggles = document.querySelectorAll('.inventory-column-toggle');
        const resetButton = document.getElementById('resetInventoryColumns');

        function getColumnIndex(columnName) {
            const headerCells = table.querySelectorAll('thead tr:first-child th');

            for (let index = 0; index < headerCells.length; index++) {
                if (headerCells[index].dataset.column === columnName) {
                    return index;
                }
            }

            return -1;
        }

        function setColumnVisibility(columnName, visible) {
            const columnIndex = getColumnIndex(columnName);

            if (columnIndex === -1) {
                return;
            }

            const rows = table.querySelectorAll('tr');

            rows.forEach(function (row) {
                const cell = row.children[columnIndex];

                if (cell) {
                    cell.classList.toggle('d-none', !visible);
                }
            });
        }

        function getHiddenColumns() {
            return JSON.parse(localStorage.getItem(storageKey)) || [];
        }

        function saveHiddenColumns(hiddenColumns) {
            localStorage.setItem(storageKey, JSON.stringify(hiddenColumns));
        }

        function applySavedPreferences() {
            const hiddenColumns = getHiddenColumns();

            toggles.forEach(function (toggle) {
                const columnName = toggle.value;
                const shouldBeVisible = !hiddenColumns.includes(columnName);

                toggle.checked = shouldBeVisible;
                setColumnVisibility(columnName, shouldBeVisible);
            });
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                let hiddenColumns = getHiddenColumns();
                const columnName = this.value;

                if (this.checked) {
                    hiddenColumns = hiddenColumns.filter(function (item) {
                        return item !== columnName;
                    });
                } else {
                    if (!hiddenColumns.includes(columnName)) {
                        hiddenColumns.push(columnName);
                    }
                }

                saveHiddenColumns(hiddenColumns);
                setColumnVisibility(columnName, this.checked);
            });
        });

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                localStorage.removeItem(storageKey);

                toggles.forEach(function (toggle) {
                    toggle.checked = true;
                    setColumnVisibility(toggle.value, true);
                });
            });
        }

        document.querySelectorAll('.dropdown-menu').forEach(function (dropdown) {
            dropdown.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });

        applySavedPreferences();
    });
</script>
    <!-- End of Column Collapser script -->

    <!-- Script for table header height read -->
     <script>
    document.addEventListener('DOMContentLoaded', function () {
        const inventoryTable = document.getElementById('inventoryTable');

        if (!inventoryTable) {
            return;
        }

        function updateInventoryStickyHeaderOffset() {
            const firstHeaderRow = inventoryTable.querySelector('thead tr:first-child');

            if (!firstHeaderRow) {
                return;
            }

            const firstHeaderHeight = firstHeaderRow.offsetHeight;

            inventoryTable.style.setProperty(
                '--inventory-first-header-height',
                firstHeaderHeight + 'px'
            );
        }

        updateInventoryStickyHeaderOffset();

        window.addEventListener('resize', updateInventoryStickyHeaderOffset);
    });
</script>
    <!-- End of Script for table header height read -->

    @if (Auth::user()->user_level !== 'Read')
        <div class="modal fade" id="uploadInventoryExcelModal" tabindex="-1" aria-labelledby="uploadInventoryExcelModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form method="POST" action="{{ route('inventory.import.preview') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadInventoryExcelModalLabel">
                                Upload Inventory Excel
                            </h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <label class="form-label">Excel file</label>

                            <input
                                type="file"
                                name="inventory_file"
                                class="form-control"
                                accept=".xlsx,.xls,.csv"
                                required
                            >

                            <small class="text-muted d-block mt-2">
                                The system will process the file first and show a review table before inserting records into inventory.
                            </small>

                            <div class="alert alert-info mt-3 mb-0">
                                Compatible headers include: IT Internal Number, Serial Number, Asset Number, Description, Model, Brand, Category, Warranty Start Date, Warranty Expiry Date, Purchase Origin Country, Department, Location, BU, Plant, End User, Responsive, ID Employee, Comments, Next Maintenance preventive, Operation System, Confidentiality, Integrity, Availability, Classification and State.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit" class="btn btn-success">
                                Process File
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif
    </div>
    </div>
</x-app-layout>