<x-app-layout>

    <div class="app-page">
        <div class="app-page-container">

    <div class="container mt-4">

        <!-- PAGE TITLE -->
        <div class="d-flex justify-content-between align-items-center app-page-title">
            <div>
                <h1 class="mb-0">
                    {{ $tab === 'logins' ? 'Login Logs' : 'Activity Logs' }}
                </h1>

                <p class="text-muted mb-0">
                    {{ $tab === 'logins'
                        ? 'Review user login records and access evidence.'
                        : 'Review system activity records and user changes.' }}
                </p>
            </div>

            <!-- Switch table button -->
            <div>
                @if ($tab === 'logins')
                    <a href="{{ route('logs') }}" class="btn btn-primary">
                        View Activity Logs
                    </a>
                @else
                    <a href="{{ route('logs', ['tab' => 'logins']) }}" class="btn btn-primary">
                        View Login Logs
                    </a>
                @endif
            </div>
        </div>

        <!-- ACTIVITY LOGS TABLE -->
        @if ($tab !== 'logins')
            <div class="card app-card">

                <!-- Hidden form for automatic filters -->
                    <form id="activityFiltersForm" method="GET" action="{{ route('logs') }}" class="auto-filter-form">
                        <input type="hidden" name="tab" value="activity">
                    </form>
                <!-- Hidden form for automatic filters end -->

                <div class="card-header app-card-header">
                    <strong>Activity Logs</strong>
                </div>

                <div class="card-body app-card-body table-responsive app-table-wrapper p-0">
                    <table class="table table-hover align-middle app-table mb-0">
                <!-- Table Headers with Filters -->

<thead class="table-light">
    <tr>
        <th>Date</th>
        <th>Employee Number</th>
        <th>User</th>
        <th>Module</th>
        <th>Action</th>
        <th>Description</th>
    </tr>

    <tr>
            <!-- INPUTS -->
            <th style="min-width: 220px;">
                <div class="d-flex gap-1">
                    <input
                        form="activityFiltersForm"
                        type="date"
                        name="activity_date_from"
                        class="form-control form-control-sm auto-filter-select"
                        value="{{ request('activity_date_from') }}"
                    >

                    <input
                        form="activityFiltersForm"
                        type="date"
                        name="activity_date_to"
                        class="form-control form-control-sm auto-filter-select"
                        value="{{ request('activity_date_to') }}"
                    >
                </div>
            </th>

            <th>
                <input
                    form="activityFiltersForm"
                    type="text"
                    name="activity_employee"
                    class="form-control form-control-sm auto-filter-input"
                    placeholder="Employee..."
                    value="{{ request('activity_employee') }}"
                >
            </th>

            <th>
                <input
                    form="activityFiltersForm"
                    type="text"
                    name="activity_user"
                    class="form-control form-control-sm auto-filter-input"
                    placeholder="User..."
                    value="{{ request('activity_user') }}"
                >
            </th>

            <th>
                <select name="activity_module" class="form-select form-select-sm auto-filter-select">
                    <option value="">All</option>
                    <option value="users" {{ request('activity_module') === 'users' ? 'selected' : '' }}>Users</option>
                    <option value="inventory" {{ request('activity_module') === 'inventory' ? 'selected' : '' }}>Inventory</option>
                </select>
            </th>

            <th>
                <select name="activity_action" class="form-select form-select-sm auto-filter-select" form="activityFiltersForm">
                    <option value="">All</option>
                    <option value="created" {{ request('activity_action') === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('activity_action') === 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deactivated" {{ request('activity_action') === 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                    <option value="deleted" {{ request('activity_action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="uploaded" {{ request('activity_action') === 'uploaded' ? 'selected' : '' }}>Uploaded</option>
                </select>
            </th>

            <th>
                <div class="d-flex gap-2">
                    <input
                        form="activityFiltersForm"
                        type="text"
                        name="activity_description"
                        class="form-control form-control-sm auto-filter-input"
                        placeholder="Description..."
                        value="{{ request('activity_description') }}"
                    >

                    <a href="{{ route('logs') }}" class="btn btn-sm btn-outline-secondary">
                        Clear
                    </a>
                </div>
            </th>
    </tr>
</thead>

                        <!-- table headers with filters end -->

                        <tbody>
                            @forelse ($activityLogs as $log)
                                <tr>
                                    <td>
                                        {{ $log->created_at ? $log->created_at->format('Y-m-d H:i') : 'N/A' }}
                                    </td>
                                    <td>{{ $log->employee_number ?? 'N/A' }}</td>
                                    <td>{{ $log->username ?? 'N/A' }}</td>
                                    <td>{{ $log->module ?? 'N/A' }}</td>

                                    <td>
                                    @php
                                        $action = strtolower($log->action ?? '');

                                        $badgeClass = match (true) {
                                        in_array($action, ['inserted', 'uploaded', 'created']) => 'bg-success',
                                        in_array($action, ['edited', 'altered', 'changed', 'updated']) => 'bg-warning text-dark',
                                        in_array($action, ['disabled', 'deleted', 'deactivated']) => 'bg-danger',
                                        default => 'bg-secondary',
                                        };
                                    @endphp 

                                    <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($log->action ?? 'N/A') }}
                                    </span>
                                    </td>

                                    <td>{{ $log->description ?? 'N/A' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                        <td colspan="6" class="text-center text-muted">
                                        No activity records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $activityLogs->links() }}
                </div>
            </div>
        @endif

        <!-- LOGIN LOGS TABLE -->
        @if ($tab === 'logins')
            <div class="card">

            <!-- auto filter hidden form -->
                <form id="loginFiltersForm" method="GET" action="{{ route('logs') }}" class="auto-filter-form">
                    <input type="hidden" name="tab" value="logins">
                </form>
            <!-- auto filter hidden form end -->

                <div class="card-header">
                    <strong>Login Logs</strong>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                    <!-- Table Headers with filters -->

<thead class="table-light">
    <tr>
        <th>Employee Number</th>
        <th>User</th>
        <th>User Level</th>
        <th>Login Date</th>
        <th>IP Address</th>
    </tr>

    <tr>
            <!-- INPUTS -->
            <th>
                <input
                    form="loginFiltersForm"
                    type="text"
                    name="login_employee"
                    class="form-control form-control-sm auto-filter-input"
                    placeholder="Employee..."
                    value="{{ request('login_employee') }}"
                >
            </th>

            <th>
                <input
                    form="loginFiltersForm"
                    type="text"
                    name="login_user"
                    class="form-control form-control-sm auto-filter-input"
                    placeholder="User..."
                    value="{{ request('login_user') }}"
                >
            </th>

            <th>
                <select name="login_role" class="form-select form-select-sm auto-filter-select" form="loginFiltersForm">
                    <option value="">All</option>
                    <option value="Admin" {{ request('login_role') === 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="User" {{ request('login_role') === 'User' ? 'selected' : '' }}>User</option>
                    <option value="Read" {{ request('login_role') === 'Read' ? 'selected' : '' }}>Read</option>
                </select>
            </th>

            <th style="min-width: 220px;">
                <div class="d-flex gap-1">
                    <input
                        form="loginFiltersForm"
                        type="date"
                        name="login_date_from"
                        class="form-control form-control-sm auto-filter-select"
                        value="{{ request('login_date_from') }}"
                    >

                    <input
                        form="loginFiltersForm"
                        type="date"
                        name="login_date_to"
                        class="form-control form-control-sm auto-filter-select"
                        value="{{ request('login_date_to') }}"
                    >
                </div>
            </th>

            <th>
                <div class="d-flex gap-2">
                    <input
                        form="loginFiltersForm"
                        type="text"
                        name="login_ip"
                        class="form-control form-control-sm auto-filter-input"
                        placeholder="IP..."
                        value="{{ request('login_ip') }}"
                    >

                    <a href="{{ route('logs', ['tab' => 'logins']) }}" class="btn btn-sm btn-outline-secondary">
                        Clear
                    </a>
                </div>
            </th>
    </tr>
</thead>

                    <!-- Table Headers with filters end -->

                        <tbody>
                            @forelse ($loginLogs as $log)
                                <tr>
                                    <td>{{ $log->employee_number ?? 'N/A' }}</td>
                                    <td>{{ $log->username ?? 'N/A' }}</td>
                                    <td>{{ $log->role ?? 'N/A' }}</td>
                                    <td>
                                        {{ $log->login_at ? \Carbon\Carbon::parse($log->login_at)->format('Y-m-d H:i') : 'N/A' }}
                                    </td>
                                    <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No login records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer app-card-footer">
                    {{ $loginLogs->links() }}
                </div>
            </div>
        @endif

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
</div>
</div>

</x-app-layout>