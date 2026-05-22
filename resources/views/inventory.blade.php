<x-app-layout>

    <div class="container mt-4">

        <!-- PAGE TITLE -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-0">
                    Inventory
                </h1>

                <p class="text-muted mb-0">
                    Review, filter and manage IT inventory assets.
                </p>
            </div>

            <!-- Show Add Asset button only for users with Create or Admin level -->
            <div>
                @if (Auth::user()->user_level !== 'Read')
                    <button 
                        type="button" 
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#addAssetModal"
                    >
                    Add Asset
                    </button>
                    @endif
            </div>
        </div>
        <div class="card">

            <!-- Hidden form for automatic filters -->
            <form id="inventoryFiltersForm" method="GET" action="{{ route('inventory') }}" class="auto-filter-form"></form>
            <!-- Hidden form for automatic filters end -->

            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Inventory Assets</strong>

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
                <input class="form-check-input inventory-column-toggle" type="checkbox" value="operating_system" id="toggle_operating_system" checked>
                <label class="form-check-label" for="toggle_operating_system">OS</label>
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

            <div class="card-body p-0">
                <div class="table-responsive inventory-table-responsive">

                <!-- Inventory table -->
                <table id="inventoryTable" class="table table-bordered table-hover align-middle wide-table mb-0">

                    <thead class="table-light">
                    <tr>
                        <th class="col-md-custom" data-column="it_internal_number">IT Internal Number</th>
                        <th class="col-md-custom" data-column="serial_number">Serial Number</th>
                        <th class="col-md-custom" data-column="asset_number">Asset Number</th>
                        <th class="col-lg-custom" data-column="description">Description</th>
                        <th class="col-md-custom" data-column="model">Model</th>
                        <th class="col-md-custom" data-column="brand">Brand</th>
                        <th class="col-md-custom" data-column="category">Category</th>
                        <th class="col-md-custom" data-column="department">Department</th>
                        <th class="col-md-custom" data-column="location">Location</th>
                        <th class="col-md-custom" data-column="business_unit">BU</th>
                        <th class="col-md-custom" data-column="plant">Plant</th>
                        <th class="col-md-custom" data-column="end_user">End User</th>
                        <th class="col-md-custom" data-column="employee_id">Employee ID</th>
                        <th class="col-md-custom" data-column="responsive">Responsive</th>
                        <th class="col-md-custom" data-column="next_maintenance">Next Maintenance</th>
                        <th class="col-md-custom" data-column="operating_system">OS</th>
                        <th class="col-md-custom" data-column="confidentiality">C</th>
                        <th class="col-md-custom" data-column="integrity">I</th>
                        <th class="col-md-custom" data-column="availability">A</th>
                        <th class="col-md-custom" data-column="classification">Classification</th>
                        <th class="col-md-custom" data-column="state">State</th>

                        @if (Auth::user()->user_level === 'Admin')
                        <th class="col-date-custom" data-column="created_at">Created At</th>
                        @endif

                        @if (Auth::user()->user_level !== 'Read')
                        <th class="col-actions-custom" data-column="actions">Actions</th>
                        @endif
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
                            <select
                                form="inventoryFiltersForm"
                                name="category"
                                class="form-select form-select-sm auto-filter-select"
                            >
                            <option value="">All</option>

                            @foreach ($categoryOptions as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                            {{ $category }}
                            </option>
                            @endforeach
                            </select>
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
                                <input
                                    form="inventoryFiltersForm"
                                    type="text"
                                    name="plant"
                                    class="form-control form-control-sm auto-filter-input"
                                    placeholder="Plant..."
                                    value="{{ request('plant') }}"
                                >
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
                                <select
                                    form="inventoryFiltersForm"
                                    name="confidentiality"
                                    class="form-select form-select-sm auto-filter-select"
                                >
                                    <option value="">All</option>
                                    <option value="0" {{ request('confidentiality') === '0' ? 'selected' : '' }}>0</option>
                                    <option value="1" {{ request('confidentiality') === '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ request('confidentiality') === '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ request('confidentiality') === '3' ? 'selected' : '' }}>3</option>
                                </select>
                            </th>

                            <th class="col-md-custom">
                                <select
                                    form="inventoryFiltersForm"
                                    name="integrity"
                                    class="form-select form-select-sm auto-filter-select"
                                >
                                    <option value="">All</option>
                                    <option value="0" {{ request('integrity') === '0' ? 'selected' : '' }}>0</option>
                                    <option value="1" {{ request('integrity') === '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ request('integrity') === '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ request('integrity') === '3' ? 'selected' : '' }}>3</option>
                                </select>
                            </th>

                            <th class="col-md-custom">
                                <select
                                    form="inventoryFiltersForm"
                                    name="availability"
                                    class="form-select form-select-sm auto-filter-select"
                                >
                                    <option value="">All</option>
                                    <option value="0" {{ request('availability') === '0' ? 'selected' : '' }}>0</option>
                                    <option value="1" {{ request('availability') === '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ request('availability') === '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ request('availability') === '3' ? 'selected' : '' }}>3</option>
                                </select>
                            </th>

                            <th class="col-md-custom">
                            <select
                                form="inventoryFiltersForm"
                                name="classification"
                                class="form-select form-select-sm auto-filter-select"
                            >
                            <option value="">All</option>

                            @foreach ($classificationOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('classification') == $value ? 'selected' : '' }}>
                            {{ $label }}
                            </option>
                            @endforeach
                            </select>
                            </th>

                            <th class="col-md-custom">
                                <select
                                    form="inventoryFiltersForm"
                                    name="state"
                                    class="form-select form-select-sm auto-filter-select"
                                >
                                    <option value="">All</option>
                                    <option value="active" {{ request('state') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('state') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="maintenance" {{ request('state') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="disposed" {{ request('state') === 'disposed' ? 'selected' : '' }}>Disposed</option>
                                    <option value="lost" {{ request('state') === 'lost' ? 'selected' : '' }}>Lost</option>
                                </select>
                            </th>

                            <!--- Only show Created At filters for Admin users -->
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
                            <!--- Only show blank (Actions) column for Read/Write users -->
                            @if (Auth::user()->user_level !== 'Read')
                                <th></th>
                            @endif
                    </tr>
                    </thead>

                    <tbody>
                        @forelse ($inventoryItems as $item)
                            <tr>
                                <td>{{ $item->it_internal_number ?? 'N/A' }}</td>
                                <td>{{ $item->serial_number ?? 'N/A' }}</td>
                                <td>{{ $item->asset_number ?? 'N/A' }}</td>
                                <td>{{ $item->description ?? 'N/A' }}</td>
                                <td>{{ $item->model ?? 'N/A' }}</td>
                                <td>{{ $item->brand ?? 'N/A' }}</td>
                                <td>{{ $item->category ?? 'N/A' }}</td>
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

                                <td>
                                    {{ $item->next_maintenance ? $item->next_maintenance->format('Y-m-d') : 'N/A' }}
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
                                            'inactive' => 'bg-secondary',
                                            'maintenance' => 'bg-warning text-dark',
                                            'disposed' => 'bg-danger',
                                            'lost' => 'bg-dark',
                                            default => 'bg-secondary',
                                        };
                                    @endphp

                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($item->state ?? 'N/A') }}
                                    </span>
                                </td>

                                <!-- Only show Created At for Admin users -->
                                @if (Auth::user()->user_level === 'Admin')
                                <td>
                                    {{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'N/A' }}
                                </td>
                                @endif

                                <!-- Only show Edit button for Read/Write users -->
                                @if (Auth::user()->user_level !== 'Read')
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editAssetModal{{ $item->id }}"
                                        >
                                            Edit
                                        </button>
                                    </td>
                                @endif
                            </tr>

                            <!-- Only show Edit modal button for Read/Write users -->
                            @if (Auth::user()->user_level !== 'Read')
                                <div class="modal fade" id="editAssetModal{{ $item->id }}" tabindex="-1" aria-labelledby="editAssetModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <div class="modal-content">

                                            <form method="POST" action="{{ route('inventory.update', $item->id) }}">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editAssetModalLabel{{ $item->id }}">
                                                        Edit Asset - {{ $item->it_internal_number ?? 'N/A' }}
                                                    </h5>

                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="row g-3">

                                                        <div class="col-md-4">
                                                            <label class="form-label">IT Internal Number</label>
                                                            <input type="text" name="it_internal_number" class="form-control" value="{{ old('it_internal_number', $item->it_internal_number) }}">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Serial Number</label>
                                                            <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $item->serial_number) }}">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Asset Number</label>
                                                            <input type="text" name="asset_number" class="form-control" value="{{ old('asset_number', $item->asset_number) }}">
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="description" class="form-control" rows="2">{{ old('description', $item->description) }}</textarea>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Model</label>
                                                            <input type="text" name="model" class="form-control" value="{{ old('model', $item->model) }}">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Brand</label>
                                                            <input type="text" name="brand" class="form-control" value="{{ old('brand', $item->brand) }}">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Category</label>
                                                            <input type="text" name="category" class="form-control" value="{{ old('category', $item->category) }}">
                                                        </div>

                                                        {{-- Remove this block if these columns are not in your inventory table --}}
                                                        <div class="col-md-3">
                                                            <label class="form-label">Department</label>
                                                            <input type="text" name="department" class="form-control" value="{{ old('department', $item->department) }}">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label class="form-label">Location</label>
                                                            <input type="text" name="location" class="form-control" value="{{ old('location', $item->location) }}">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label class="form-label">BU</label>
                                                            <input type="text" name="business_unit" class="form-control" value="{{ old('business_unit', $item->business_unit) }}">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label class="form-label">Plant</label>
                                                            <input type="text" name="plant" class="form-control" value="{{ old('plant', $item->plant) }}">
                                                        </div>
                                                        {{-- End optional block --}}

                                                        <div class="col-md-4">
                                                            <label class="form-label">End User</label>
                                                            <input type="text" name="end_user" class="form-control" value="{{ old('end_user', $item->end_user) }}">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Employee ID</label>
                                                            <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id', $item->employee_id) }}">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Operating System</label>
                                                            <input type="text" name="operating_system" class="form-control" value="{{ old('operating_system', $item->operating_system) }}">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Next Maintenance</label>
                                                            <input
                                                                type="date"
                                                                name="next_maintenance"
                                                                class="form-control"
                                                                value="{{ old('next_maintenance', optional($item->next_maintenance)->format('Y-m-d')) }}"
                                                            >
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Classification</label>
                                                            <input type="text" name="classification" class="form-control" value="{{ old('classification', $item->classification) }}">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">State</label>
                                                            <select name="state" class="form-select">
                                                                <option value="active" {{ old('state', $item->state) === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ old('state', $item->state) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                <option value="maintenance" {{ old('state', $item->state) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                                <option value="disposed" {{ old('state', $item->state) === 'disposed' ? 'selected' : '' }}>Disposed</option>
                                                                <option value="lost" {{ old('state', $item->state) === 'lost' ? 'selected' : '' }}>Lost</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Confidentiality</label>
                                                            <select name="confidentiality" class="form-select">
                                                                <option value="">N/A</option>
                                                                <option value="0" {{ old('confidentiality', $item->confidentiality) == '0' ? 'selected' : '' }}>0</option>
                                                                <option value="1" {{ old('confidentiality', $item->confidentiality) == '1' ? 'selected' : '' }}>1</option>
                                                                <option value="2" {{ old('confidentiality', $item->confidentiality) == '2' ? 'selected' : '' }}>2</option>
                                                                <option value="3" {{ old('confidentiality', $item->confidentiality) == '3' ? 'selected' : '' }}>3</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Integrity</label>
                                                            <select name="integrity" class="form-select">
                                                                <option value="">N/A</option>
                                                                <option value="0" {{ old('integrity', $item->integrity) == '0' ? 'selected' : '' }}>0</option>
                                                                <option value="1" {{ old('integrity', $item->integrity) == '1' ? 'selected' : '' }}>1</option>
                                                                <option value="2" {{ old('integrity', $item->integrity) == '2' ? 'selected' : '' }}>2</option>
                                                                <option value="3" {{ old('integrity', $item->integrity) == '3' ? 'selected' : '' }}>3</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Availability</label>
                                                            <select name="availability" class="form-select">
                                                                <option value="">N/A</option>
                                                                <option value="0" {{ old('availability', $item->availability) == '0' ? 'selected' : '' }}>0</option>
                                                                <option value="1" {{ old('availability', $item->availability) == '1' ? 'selected' : '' }}>1</option>
                                                                <option value="2" {{ old('availability', $item->availability) == '2' ? 'selected' : '' }}>2</option>
                                                                <option value="3" {{ old('availability', $item->availability) == '3' ? 'selected' : '' }}>3</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label class="form-label">Comments</label>
                                                            <textarea name="comments" class="form-control" rows="3">{{ old('comments', $item->comments) }}</textarea>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <div class="form-check">
                                                                <input
                                                                    class="form-check-input"
                                                                    type="checkbox"
                                                                    name="responsive"
                                                                    id="responsive{{ $item->id }}"
                                                                    value="1"
                                                                    {{ old('responsive', $item->responsive) ? 'checked' : '' }}
                                                                >

                                                                <label class="form-check-label" for="responsive{{ $item->id }}">
                                                                    Has responsive document
                                                                </label>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
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
        <td colspan="{{ Auth::user()->user_level === 'Admin' ? 23 : (Auth::user()->user_level === 'Read' ? 21 : 22) }}" class="text-center text-muted py-4">
            No inventory records found.
        </td>
    </tr>
@endforelse

                    </tbody>

                </table>
            </div>
        </div>

            <div class="card-footer">
                {{ $inventoryItems->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL FOR ADDING ASSETS -->
    @if (Auth::user()->user_level !== 'Read')
    <div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="addAssetModalLabel">Add Asset</h5>
                        <small class="text-muted">Register a new IT inventory asset.</small>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label">IT Internal Number</label>
                                <input type="text" name="it_internal_number" class="form-control" value="{{ old('it_internal_number') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Asset Number</label>
                                <input type="text" name="asset_number" class="form-control" value="{{ old('asset_number') }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control" value="{{ old('model') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" class="form-control" value="{{ old('brand') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">Select category</option>

                                    @foreach ($categoryOptions as $category)
                                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">End User</label>
                                <input type="text" name="end_user" class="form-control" value="{{ old('end_user') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Employee ID</label>
                                <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Operating System</label>
                                <input type="text" name="operating_system" class="form-control" value="{{ old('operating_system') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Next Maintenance</label>
                                <input type="date" name="next_maintenance" class="form-control" value="{{ old('next_maintenance') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Classification</label>
                                <select name="classification" class="form-select">
                                    <option value="">Select classification</option>

                                    @foreach ($classificationOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('classification') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <select name="state" class="form-select">
                                    <option value="active" {{ old('state') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('state') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="maintenance" {{ old('state') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="disposed" {{ old('state') === 'disposed' ? 'selected' : '' }}>Disposed</option>
                                    <option value="lost" {{ old('state') === 'lost' ? 'selected' : '' }}>Lost</option>
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

                            <div class="col-md-12">
                                <label class="form-label">Comments</label>
                                <textarea name="comments" class="form-control" rows="3">{{ old('comments') }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        name="responsive" 
                                        id="responsive" 
                                        value="1" 
                                        {{ old('responsive') ? 'checked' : '' }}
                                    >

                                    <label class="form-check-label" for="responsive">
                                        Has responsive document
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
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
                        }, 600);
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

</x-app-layout>