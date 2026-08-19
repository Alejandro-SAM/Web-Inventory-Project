<x-app-layout>
    <div class="app-page">
        <div class="app-page-container">
                <div class="app-card-header">
                    <strong>Completed Maintenance</strong>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">
                            {{ $maintenanceRecords->total() }} records
                        </span>

                        <a
                            href="{{ route('maintenance.index') }}"
                            class="btn btn-outline-primary btn-sm"
                        >
                            Back to Maintenance
                        </a>
                    </div>
                </div>

                <div class="app-card-body">
                    <div class="app-table-wrapper">
                        <table class="table app-table mb-0">
                            <thead>
                                <tr>
                                    <th>Completed Date</th>
                                    <th>IT Number</th>
                                    <th>Serial</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Maintenance Date</th>
                                    <th>Responsible</th>
                                    <th>Requested By</th>
                                    <th>Approved By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($maintenanceRecords as $record)
                                    <tr>
                                        <td>
                                            {{ $record->completed_at?->format('Y-m-d H:i') ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $record->inventory?->it_internal_number ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $record->inventory?->serial_number ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $record->inventory?->description ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $record->inventory?->category ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $record->maintenance_date?->format('Y-m-d') ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $record->responsible?->name ?? 'Unknown user' }}
                                        </td>

                                        <td>
                                            {{ $record->completionRequestedBy?->name ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $record->reviewer?->name ?? '—' }}
                                        </td>

                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#historyModal{{ $record->id }}"
                                            >
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="10"
                                            class="text-center py-4 text-muted"
                                        >
                                            No completed maintenance records were found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        @foreach ($maintenanceRecords as $record)
                            @php
                                $item = $record->inventory;
                            @endphp

                            <div
                                class="modal fade"
                                id="historyModal{{ $record->id }}"
                                tabindex="-1"
                                aria-labelledby="historyModalLabel{{ $record->id }}"
                                aria-hidden="true"
                            >
                                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5
                                                class="modal-title"
                                                id="historyModalLabel{{ $record->id }}"
                                            >
                                                Maintenance Record Details
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

                                                <div class="col-12">
                                                    <h6 class="fw-bold border-bottom pb-2">
                                                        Maintenance Information
                                                    </h6>
                                                </div>

                                                <div class="col-md-4">
                                                    <strong>Maintenance Date</strong>
                                                    <div>
                                                        {{ $record->maintenance_date?->format('Y-m-d') ?? '—' }}
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <strong>Completion Requested At</strong>
                                                    <div>
                                                        {{ $record->completion_requested_at?->format('Y-m-d H:i') ?? '—' }}
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <strong>Completed At</strong>
                                                    <div>
                                                        {{ $record->completed_at?->format('Y-m-d H:i') ?? '—' }}
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <strong>Status</strong>
                                                    <div>
                                                        {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <strong>Responsible</strong>
                                                    <div>
                                                        {{ $record->responsible?->name ?? 'Unknown user' }}
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <strong>Requested By</strong>
                                                    <div>
                                                        {{ $record->completionRequestedBy?->name ?? '—' }}
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <strong>Approved By</strong>
                                                    <div>
                                                        {{ $record->reviewer?->name ?? '—' }}
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <strong>Reviewed At</strong>
                                                    <div>
                                                        {{ $record->reviewed_at?->format('Y-m-d H:i') ?? '—' }}
                                                    </div>
                                                </div>

                                                <div class="col-12 mt-4">
                                                    <h6 class="fw-bold border-bottom pb-2">
                                                        Asset Information
                                                    </h6>
                                                </div>

                                                @if ($item)
                                                    <div class="col-md-4">
                                                        <strong>IT Internal Number</strong>
                                                        <div>
                                                            {{ $item->it_internal_number ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Serial Number</strong>
                                                        <div>
                                                            {{ $item->serial_number ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Asset Number</strong>
                                                        <div>
                                                            {{ $item->asset_number ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Description</strong>
                                                        <div>
                                                            {{ $item->description ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Category</strong>
                                                        <div>
                                                            {{ $item->category ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Brand</strong>
                                                        <div>
                                                            {{ $item->brand ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Model</strong>
                                                        <div>
                                                            {{ $item->model ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Plant</strong>
                                                        <div>
                                                            {{ $item->plant ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Business Unit</strong>
                                                        <div>
                                                            {{ $item->business_unit ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Department</strong>
                                                        <div>
                                                            {{ $item->department ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Location</strong>
                                                        <div>
                                                            {{ $item->location ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>End User</strong>
                                                        <div>
                                                            {{ $item->end_user ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Employee ID</strong>
                                                        <div>
                                                            {{ $item->employee_id ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Operating System</strong>
                                                        <div>
                                                            {{ $item->operating_system ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Asset State</strong>
                                                        <div>
                                                            {{ ucfirst(str_replace('_', ' ', $item->state ?? '')) ?: '—' }}
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
                                                        <div>
                                                            {{ $item->purchase_origin_country ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Responsive</strong>
                                                        <div>
                                                            {{ $item->responsive ? 'Yes' : 'No' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <strong>Classification</strong>
                                                        <div>
                                                            {{ $item->classification ?? '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <strong>Comments</strong>
                                                        <div class="mt-1">
                                                            {{ $item->comments ?: '—' }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="col-12">
                                                        <div class="alert alert-warning mb-0">
                                                            The inventory item related to this maintenance record no longer exists.
                                                        </div>
                                                    </div>
                                                @endif

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
                        @endforeach
                    </div>
                </div>

                    </div>
                </div>

                @if ($maintenanceRecords->hasPages())
                    <div class="app-card-footer">
                        {{ $maintenanceRecords->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>