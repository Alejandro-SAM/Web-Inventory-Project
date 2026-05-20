<x-app-layout>
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-0">Add Asset</h1>
                <p class="text-muted mb-0">
                    Register a new IT inventory asset.
                </p>
            </div>

            <a href="{{ route('inventory') }}" class="btn btn-secondary">
                Back to Inventory
            </a>
        </div>

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

        <div class="card">
            <div class="card-header">
                <strong>Asset Information</strong>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('inventory.store') }}">
                    @csrf

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
                                <input class="form-check-input" type="checkbox" name="responsive" id="responsive" value="1" {{ old('responsive') ? 'checked' : '' }}>
                                <label class="form-check-label" for="responsive">
                                    Has responsive document
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('inventory') }}" class="btn btn-secondary">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Save Asset
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</x-app-layout>