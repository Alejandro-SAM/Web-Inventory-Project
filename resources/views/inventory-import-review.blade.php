<x-app-layout :hide-navigation="true">
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-0">Inventory Import Review</h1>
                <p class="text-muted mb-0">
                    Review valid and invalid rows before importing them into inventory.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title">Valid Rows</h5>
                        <p class="fs-3 mb-0">{{ $validCount }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="card-title">Invalid Rows</h5>
                        <p class="fs-3 mb-0">{{ $invalidCount }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Imported Rows</h5>
                        <p class="fs-3 mb-0">{{ $importedCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if ($invalidCount > 0)
            <div class="alert alert-warning">
                <h5 class="mb-2">There are {{ $invalidCount }} incompatible rows.</h5>

                <p class="mb-3">
                    Do you want to review and fix them before importing?
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('inventory.import.invalid', $batchId) }}" class="btn btn-warning">
                        Yes, review invalid rows
                    </a>

                    @if ($validCount > 0)
                        <form method="POST" action="{{ route('inventory.import.confirm', $batchId) }}">
                            @csrf

                            <button type="submit" class="btn btn-success">
                                No, import only valid rows
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('inventory.import.cancel', $batchId) }}">
                        @csrf

                        <button type="submit" class="btn btn-outline-danger">
                            Cancel whole import process
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-success">
                <h5 class="mb-2">All rows are compatible.</h5>

                <p class="mb-3">
                    You can now import all valid rows into the inventory table.
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('inventory.import.confirm', $batchId) }}">
                        @csrf

                        <button type="submit" class="btn btn-success">
                            Import valid rows
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
        @endif

        <div class="card">
            <div class="card-header">
                <strong>Processed Rows</strong>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Excel Row</th>
                            <th>Status</th>
                            <th>IT Internal Number</th>
                            <th>Serial Number</th>
                            <th>Asset Number</th>
                            <th>Description</th>
                            <th>Warranty Start Date</th>
                            <th>Warranty Expiry Date</th>
                            <th>Purchase Origin Country</th>
                            <th>Errors</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($rows as $row)
                            @php
                                $data = $row->normalized_data ?? [];
                                $errors = $row->errors ?? [];

                                $badgeClass = match ($row->status) {
                                    'valid' => 'bg-success',
                                    'invalid' => 'bg-danger',
                                    'imported' => 'bg-primary',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-secondary',
                                };
                            @endphp

                            <tr>
                                <td>{{ $row->row_number }}</td>

                                <td>
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($row->status) }}
                                    </span>
                                </td>

                                <td>{{ $data['it_internal_number'] ?? 'N/A' }}</td>
                                <td>{{ $data['serial_number'] ?? 'N/A' }}</td>
                                <td>{{ $data['asset_number'] ?? 'N/A' }}</td>
                                <td>{{ $data['description'] ?? 'N/A' }}</td>
                                <td>{{ $data['warranty_start_date'] ?? 'N/A' }}</td>
                                <td>{{ $data['warranty_expiry_date'] ?? 'N/A' }}</td>
                                <td>{{ $data['purchase_origin_country'] ?? 'N/A' }}</td>

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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    No processed rows found.
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