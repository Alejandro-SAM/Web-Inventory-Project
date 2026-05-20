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

            <div>
                @if (Auth::user()->user_level !== 'Read')
                    <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                        Add Asset
                    </a>
                @endif
            </div>
        </div>

        <div class="card">

            <!-- Hidden form for automatic filters -->
            <form id="inventoryFiltersForm" method="GET" action="{{ route('inventory') }}" class="auto-filter-form"></form>
            <!-- Hidden form for automatic filters end -->

            <div class="card-header">
                <strong>Inventory Assets</strong>
            </div>

            <div class="card-body table-responsive">

                <!-- Inventory table -->
                <table class="table table-bordered table-hover align-middle wide-table">

                    <thead class="table-light">
                        <tr>
                            <th class="col-md-custom">IT Internal Number</th>
                            <th class="col-md-custom">Serial Number</th>
                            <th class="col-md-custom">Asset Number</th>
                            <th class="col-lg-custom">Description</th>
                            <th class="col-md-custom">Model</th>
                            <th class="col-md-custom">Brand</th>
                            <th class="col-md-custom">Category</th>
                            <th class="col-md-custom">Department</th>
                            <th class="col-md-custom">Location</th>
                            <th class="col-md-custom">BU</th>
                            <th class="col-md-custom">Plant</th>
                            <th class="col-md-custom">End User</th>
                            <th class="col-md-custom">Employee ID</th>
                            <th class="col-md-custom">Responsive</th>
                            <th class="col-md-custom">Next Maintenance</th>
                            <th class="col-md-custom">OS</th>
                            <th class="col-md-custom">C</th>
                            <th class="col-md-custom">I</th>
                            <th class="col-md-custom">A</th>
                            <th class="col-md-custom">Classification</th>
                            <th class="col-md-custom">State</th>

                            <!-- Only show Created At column for Admin users -->
                            @if (Auth::user()->user_level === 'Admin')
                            <th class="col-md-custom">Created At</th>
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

                            <th class="col-md-custom" style="min-width: 220px;">
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
                        </tr>
                    </thead>
                            @endif

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
                            </tr>
                        @empty
                            <tr>
                                <!-- Show a message when no records are found, spanning all columns, also change the colspan based on user level -->
                                <td colspan="{{ Auth::user()->user_level === 'Admin' ? 22 : 21 }}" class="text-center text-muted">
                                    No inventory records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="card-footer">
                {{ $inventoryItems->links() }}
            </div>
        </div>
    </div>

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

</x-app-layout>