<x-app-layout>
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-0">Invalid Import Rows</h1>
                <p class="text-muted mb-0">
                    Review and fix incompatible rows before importing them into inventory.
                </p>
            </div>

            <a href="{{ route('inventory.import.review', $batchId) }}" class="btn btn-secondary">
                Back to Import Review
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <strong>Rows with Errors</strong>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Excel Row</th>
                            <th>IT Internal Number</th>
                            <th>Serial Number</th>
                            <th>Asset Number</th>
                            <th>Description</th>
                            <th>Errors</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($rows as $row)
                            @php
                                $data = $row->normalized_data ?? [];
                                $errors = $row->errors ?? [];
                            @endphp

                            <tr>
                                <td>{{ $row->row_number }}</td>
                                <td>{{ $data['it_internal_number'] ?? 'N/A' }}</td>
                                <td>{{ $data['serial_number'] ?? 'N/A' }}</td>
                                <td>{{ $data['asset_number'] ?? 'N/A' }}</td>
                                <td>{{ $data['description'] ?? 'N/A' }}</td>

                                <td>
                                    @if (!empty($errors))
                                        <ul class="mb-0">
                                            @foreach ($errors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">No errors</span>
                                    @endif
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editImportRowModal{{ $row->id }}"
                                    >
                                        Edit
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="editImportRowModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                    <div class="modal-content">

                                        <form method="POST" action="{{ route('inventory.import.row.update', $row->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    Edit Import Row #{{ $row->row_number }}
                                                </h5>

                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="row g-3">

                                                    @foreach ([
                                                        'it_internal_number' => 'IT Internal Number',
                                                        'serial_number' => 'Serial Number',
                                                        'asset_number' => 'Asset Number',
                                                        'description' => 'Description',
                                                        'model' => 'Model',
                                                        'brand' => 'Brand',
                                                        'category' => 'Category',
                                                        'purchase_origin_country' => 'Purchase Origin Country',
                                                        'department' => 'Department',
                                                        'location' => 'Location',
                                                        'business_unit' => 'BU',
                                                        'plant' => 'Plant',
                                                        'end_user' => 'End User',
                                                        'employee_id' => 'Employee ID',
                                                        'comments' => 'Comments',
                                                        'operating_system' => 'Operating System',
                                                        'classification' => 'Classification',
                                                    ] as $field => $label)
                                                        <div class="col-md-4">
                                                            <label class="form-label">{{ $label }}</label>
                                                            <input
                                                                type="text"
                                                                name="{{ $field }}"
                                                                class="form-control"
                                                                value="{{ $data[$field] ?? '' }}"
                                                            >
                                                        </div>
                                                    @endforeach

                                                    <div class="col-md-4">
                                                        <label class="form-label">Responsive</label>
                                                        <select name="responsive" class="form-select">
                                                            <option value="N" {{ empty($data['responsive']) ? 'selected' : '' }}>No</option>
                                                            <option value="Y" {{ !empty($data['responsive']) ? 'selected' : '' }}>Yes</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Next Maintenance</label>
                                                        <input
                                                            type="date"
                                                            name="next_maintenance"
                                                            class="form-control"
                                                            value="{{ $data['next_maintenance'] ?? '' }}"
                                                        >
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Warranty Start Date</label>
                                                        <input
                                                            type="date"
                                                            name="warranty_start_date"
                                                            class="form-control"
                                                            value="{{ $data['warranty_start_date'] ?? '' }}"
                                                        >
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Warranty Expiry Date</label>
                                                        <input
                                                            type="date"
                                                            name="warranty_expiry_date"
                                                            class="form-control"
                                                            value="{{ $data['warranty_expiry_date'] ?? '' }}"
                                                        >
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Confidentiality</label>
                                                        <select name="confidentiality" class="form-select">
                                                            <option value="">N/A</option>
                                                            @foreach ([0, 1, 2, 3] as $value)
                                                                <option value="{{ $value }}" {{ isset($data['confidentiality']) && (string) $data['confidentiality'] === (string) $value ? 'selected' : '' }}>
                                                                    {{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Integrity</label>
                                                        <select name="integrity" class="form-select">
                                                            <option value="">N/A</option>
                                                            @foreach ([0, 1, 2, 3] as $value)
                                                                <option value="{{ $value }}" {{ isset($data['integrity']) && (string) $data['integrity'] === (string) $value ? 'selected' : '' }}>
                                                                    {{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Availability</label>
                                                        <select name="availability" class="form-select">
                                                            <option value="">N/A</option>
                                                            @foreach ([0, 1, 2, 3] as $value)
                                                                <option value="{{ $value }}" {{ isset($data['availability']) && (string) $data['availability'] === (string) $value ? 'selected' : '' }}>
                                                                    {{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">State</label>
                                                        <select name="state" class="form-select">
                                                            @foreach (['active', 'inactive', 'maintenance', 'disposed', 'lost'] as $state)
                                                                <option value="{{ $state }}" {{ ($data['state'] ?? 'active') === $state ? 'selected' : '' }}>
                                                                    {{ ucfirst($state) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Cancel
                                                </button>

                                                <button type="submit" class="btn btn-primary">
                                                    Save and Revalidate
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No invalid rows found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $rows->links() }}
            </div>
        </div>

    </div>
</x-app-layout>