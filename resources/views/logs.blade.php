<x-app-layout>
    <div class="container mt-4">

        <!-- PAGE TITLE -->
        <div class="d-flex justify-content-between align-items-center mb-4">
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
            <div class="card">
                <div class="card-header">
                    <strong>Activity Records</strong>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee Number</th>
                                <th>User</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Description</th>
                            </tr>
                        </thead>

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
                <div class="card-header">
                    <strong>Login Records</strong>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Employee Number</th>
                                <th>User</th>
                                <th>User Level</th>
                                <th>Login Date</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>

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
                <div class="card-footer">
                    {{ $loginLogs->appends(['tab' => 'logins'])->links() }}
                </div>
            </div>
        @endif

    </div>
</x-app-layout>