<x-app-layout>

    <div class="app-page">
        <div class="app-page-container">

    <div class="container mt-4">

        <!-- PAGE TITLE -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-0">User Table</h1>
                <p class="text-muted mb-0">Manage system users and access levels.</p>
            </div>

            <!-- Create user button -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                Create User
            </button>
        </div>

        <!-- Success message -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- USER TABLE -->
        <div class="card">
            <div class="card-header">
                <strong>Users List</strong>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Number</th>
                            <th>Name</th>
                            <th>Department/Area</th>
                            <th>User Level</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->employee_number }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->department ?? 'N/A' }}</td>
                                <td>{{ $user->user_level }}</td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $user->created_at ? $user->created_at->format('Y-m-d') : 'N/A' }}
                                </td>
                                <td class="text-center">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal{{ $user->id }}"
                                    >
                                        Edit
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit user modal -->
                            <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="editUserModalLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">

                                        <form method="POST" action="{{ route('users.update', $user->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editUserModalLabel{{ $user->id }}">
                                                    Edit User
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="row">

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Employee Number</label>
                                                        <input 
                                                            type="text" 
                                                            name="employee_number" 
                                                            class="form-control" 
                                                            value="{{ $user->employee_number }}" 
                                                            disabled
                                                        >
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Employee Name</label>
                                                        <input 
                                                            type="text" 
                                                            name="name" 
                                                            class="form-control" 
                                                            value="{{ $user->name }}" 
                                                            disabled
                                                        >
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Department/Area</label>
                                                        <select name="department" class="form-select" required>
                                                        <option value="IT" {{ $user->department === 'IT' ? 'selected' : '' }}>IT</option>
                                                        <option value="HR" {{ $user->department === 'HR' ? 'selected' : '' }}>HR</option>
                                                        <option value="Finances" {{ $user->department === 'Finances' ? 'selected' : '' }}>Finances</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">User Level</label>
                                                        <select name="user_level" class="form-select" required>
                                                            <option value="Admin" {{ $user->user_level === 'Admin' ? 'selected' : '' }}>Admin</option>
                                                            <option value="User" {{ $user->user_level === 'User' ? 'selected' : '' }}>User</option>
                                                            <option value="Read" {{ $user->user_level === 'Read' ? 'selected' : '' }}>Read</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Account Status</label>
                                                        <select name="is_active" class="form-select" required>
                                                            <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                                                            <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">New Password</label>

                                                        <div class="input-group">
                                                            <input 
                                                                type="password" 
                                                                name="password" 
                                                                id="editPassword{{ $user->id }}"
                                                                class="form-control"
                                                                placeholder="Leave blank to keep current password"
                                                            >

                                                            <button 
                                                                type="button" 
                                                                class="btn btn-outline-secondary toggle-password"
                                                                data-target="editPassword{{ $user->id }}"
                                                            >
                                                            Show
                                                            </button>
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
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No users registered.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Create user modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="createUserModalLabel">
                            Create User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Employee Number</label>
                                <input 
                                    type="text" 
                                    name="employee_number" 
                                    class="form-control" 
                                    required
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Employee Name</label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    class="form-control" 
                                    required
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department/Area</label>
                                <select name="department" class="form-select" required>
                                    <option value="" selected disabled>Select department/area</option>
                                    <option value="IT">IT</option>
                                    <option value="HR">HR</option>
                                    <option value="Finances">Finances</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">User Level</label>
                                <select name="user_level" class="form-select" required>
                                    <option value="Read" selected>Read</option>
                                    <option value="User">User</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Status</label>
                                <select name="is_active" class="form-select" required>
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>

                                    <div class="input-group">
                                    <input 
                                        type="password" 
                                        name="password" 
                                        id="createPassword"
                                        class="form-control" 
                                        required
                                    >

                                    <button 
                                        type="button" 
                                        class="btn btn-outline-secondary toggle-password"
                                        data-target="createPassword"
                                    >
                                    Show
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Create User
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.toggle-password').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);

                if (!passwordInput) {
                    return;
                }

                const isPassword = passwordInput.type === 'password';

                passwordInput.type = isPassword ? 'text' : 'password';
                this.textContent = isPassword ? 'Hide' : 'Show';
            });
        });
    </script>
    
    </div>
    </div>
    
</x-app-layout>