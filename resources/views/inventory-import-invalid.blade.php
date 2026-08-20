<x-app-layout :hide-navigation="true">

    <style>
        /*
         * The form wraps the complete Bootstrap modal content. It therefore
         * needs to reproduce Bootstrap's flex/overflow behavior so the body
         * scrolls while the header and footer remain visible.
         */
        .modal-dialog-scrollable .modal-content > .import-row-edit-form {
            display: flex;
            flex-direction: column;
            max-height: 100%;
            min-height: 0;
            overflow: hidden;
        }

        .import-row-edit-form .modal-header,
        .import-row-edit-form .modal-footer {
            flex-shrink: 0;
        }

        .import-row-edit-form .modal-body {
            min-height: 0;
            overflow-y: auto;
        }
    </style>

    <div class="app-page">
        <div class="app-page-container">
        
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-0">Invalid Import Rows</h1>
                <p class="text-muted mb-0">
                    Review and fix incompatible rows before importing them into inventory.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('inventory.import.review', $batchId) }}" class="btn btn-secondary">
                    Back to Import Review
                </a>

                <form method="POST" action="{{ route('inventory.import.confirm', $batchId) }}">
                    @csrf

                    <button type="submit" class="btn btn-success">
                        Import only valid rows
                    </button>
                </form>

                <form method="POST" action="{{ route('inventory.import.cancel', $batchId) }}">
                    @csrf

                    <button type="submit" class="btn btn-outline-danger">
                        Cancel whole import process
                    </button>
                </form>
            </div>
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

                            <tr data-import-row-id="{{ $row->id }}">
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
                                    <div class="d-flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editImportRowModal{{ $row->id }}"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger discard-import-row"
                                            data-row-id="{{ $row->id }}"
                                            data-url="{{ route('inventory.import.row.destroy', ['row' => $row->id] + request()->query()) }}"
                                        >
                                            Drop
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            @push('inventory-import-row-modals')
                            <div class="modal fade" id="editImportRowModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                    <div class="modal-content">

                                        <form
                                            method="POST"
                                            action="{{ route('inventory.import.row.update', ['row' => $row->id] + request()->query()) }}"
                                            class="import-row-edit-form"
                                            data-row-id="{{ $row->id }}"
                                            novalidate
                                        >
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

                                                <button
                                                    type="button"
                                                    class="btn btn-primary save-import-row-button"
                                                >
                                                    Save and Revalidate
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                            @endpush
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

    </div>
    </div>

    {{-- Render modals outside the table and page containers. --}}
    @stack('inventory-import-row-modals')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]'
            )?.content || document.querySelector(
                'input[name="_token"]'
            )?.value;

            const previousPageUrl = @json($rows->previousPageUrl());
            const reviewUrl = @json(route('inventory.import.review', $batchId));

            function visibleImportRows() {
                return document.querySelectorAll('[data-import-row-id]');
            }

            function continueWhenPageIsEmpty() {
                if (visibleImportRows().length > 0) {
                    return;
                }

                window.location.href = previousPageUrl || reviewUrl;
            }

            function showMessage(message, type = 'success') {
                const previousAlert = document.getElementById(
                    'importAjaxAlert'
                );

                if (previousAlert) {
                    previousAlert.remove();
                }

                const alert = document.createElement('div');
                alert.id = 'importAjaxAlert';
                alert.className = `alert alert-${type}`;
                alert.textContent = message;

                const card = document.querySelector('.card');

                if (card) {
                    card.before(alert);
                }

                window.setTimeout(function () {
                    alert.remove();
                }, 4000);
            }

            function removeImportRow(rowId) {
                document.querySelector(
                    `[data-import-row-id="${rowId}"]`
                )?.remove();

                document.getElementById(
                    `editImportRowModal${rowId}`
                )?.remove();

                continueWhenPageIsEmpty();
            }

            document.querySelectorAll('.import-row-edit-form').forEach(
                function (form) {
                    form.addEventListener('submit', async function (event) {
                        event.preventDefault();

                        if (form.dataset.submitting === 'true') {
                            return;
                        }

                        const rowId = String(form.dataset.rowId);
                        const submitButton = form.querySelector(
                            '.save-import-row-button'
                        );
                        const originalText = submitButton.textContent;

                        form.dataset.submitting = 'true';
                        submitButton.disabled = true;
                        submitButton.textContent = 'Saving...';

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const result = await response.json();

                            if (!response.ok) {
                                throw new Error(
                                    result.message || 'The row could not be updated.'
                                );
                            }

                            form.closest('.modal')
                                ?.querySelector('[data-bs-dismiss="modal"]')
                                ?.click();

                            if (result.row.status === 'valid') {
                                removeImportRow(rowId);
                            } else {
                                const tableRow = document.querySelector(
                                    `[data-import-row-id="${rowId}"]`
                                );
                                const cells = tableRow?.children;
                                const data = result.row.data || {};
                                const errors = result.row.errors || [];

                                if (cells) {
                                    cells[1].textContent =
                                        data.it_internal_number || 'N/A';
                                    cells[2].textContent =
                                        data.serial_number || 'N/A';
                                    cells[3].textContent =
                                        data.asset_number || 'N/A';
                                    cells[4].textContent =
                                        data.description || 'N/A';

                                    cells[5].innerHTML = '';
                                    const errorList = document.createElement('ul');
                                    errorList.className = 'mb-0';

                                    errors.forEach(function (error) {
                                        const item = document.createElement('li');
                                        item.textContent = error;
                                        errorList.appendChild(item);
                                    });

                                    cells[5].appendChild(errorList);
                                }
                            }

                            showMessage(
                                result.message,
                                result.row.status === 'valid'
                                    ? 'success'
                                    : 'warning'
                            );
                        } catch (error) {
                            window.alert(error.message);
                        } finally {
                            form.dataset.submitting = 'false';
                            submitButton.disabled = false;
                            submitButton.textContent = originalText;
                        }
                    });

                    const saveButton = form.querySelector(
                        '.save-import-row-button'
                    );

                    if (saveButton) {
                        saveButton.addEventListener('click', function () {
                            form.dispatchEvent(
                                new Event('submit', {
                                    bubbles: true,
                                    cancelable: true
                                })
                            );
                        });
                    }
                }
            );

            document.querySelectorAll('.discard-import-row').forEach(
                function (button) {
                    button.addEventListener('click', async function () {
                        const confirmed = window.confirm(
                            'Discard this row from the import batch? This action cannot be undone.'
                        );

                        if (!confirmed) {
                            return;
                        }

                        button.disabled = true;

                        try {
                            const response = await fetch(button.dataset.url, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });

                            const result = await response.json();

                            if (!response.ok) {
                                throw new Error(
                                    result.message || 'The row could not be discarded.'
                                );
                            }

                            removeImportRow(String(button.dataset.rowId));
                            showMessage(result.message);
                        } catch (error) {
                            button.disabled = false;
                            window.alert(error.message);
                        }
                    });
                }
            );
        });
    </script>
    
</x-app-layout>
