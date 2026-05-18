<?php
/* THIS DOCUMENT IS FOR DISPLAYING AND MANAGING THE LOGIC FOR THE USER TABLE */
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

        private function authorizeAdmin(): void /* ONLY ADMINS CAN ACCESS THE USERS PAGE */
    {
        if (auth()->user()->user_level !== 'Admin') {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    /**
     * Display the users table.
     */
    public function index()
    {

        $this->authorizeAdmin(); /* CALLS FUNCTION TO CHECK IF USER IS ADMIN */

        $users = User::orderBy('created_at', 'desc')->paginate(10);

        return view('users', compact('users'));
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {

        $this->authorizeAdmin(); /* CALLS FUNCTION TO CHECK IF USER IS ADMIN */

        $validated = $request->validate([
            'employee_number' => [
                'required',
                'string',
                'max:50',
                'unique:users,employee_number',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'department' => [
                'required',
                'string',
                Rule::in(['IT', 'HR', 'Finances']),
            ],
            'user_level' => [
                'required',
                Rule::in(['Admin', 'User', 'Read']),
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
            ],
        ]);

        $newUser = User::create([
            'employee_number' => $validated['employee_number'],
            'name' => $validated['name'],
            'department' => $validated['department'] ?? null,
            'user_level' => $validated['user_level'],
            'is_active' => $validated['is_active'],
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLogger::log(
            module: 'users',
            action: 'created',
            description: 'User ' . $newUser->employee_number . ' was created.',
            targetType: 'user',
            targetId: $newUser->id,
            oldValues: null,
            newValues: [
                'employee_number' => $newUser->employee_number,
                'name' => $newUser->name,
                'department' => $newUser->department,
                'user_level' => $newUser->user_level,
                'is_active' => $newUser->is_active,
            ]
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin(); /* CALLS FUNCTION TO CHECK IF USER IS ADMIN */

        $oldValues = [ // STORE OLD VALUES TO COMPARE CHANGES IN THE LOGS */
            'department' => $user->department,
            'user_level' => $user->user_level,
            'is_active' => $user->is_active,
        ];
        
        $validated = $request->validate([
            'department' => [
                'nullable',
                'string',
                'max:255',
                Rule::in(['IT', 'HR', 'Finances']),
            ],
            'user_level' => [
                'required',
                Rule::in(['Admin', 'User', 'Read']),
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
            'password' => [
                'nullable',
                'string',
            'min:6',
            ],
        ]);

    $user->department = $validated['department'] ?? null;
    $user->user_level = $validated['user_level'];
    $user->is_active = $validated['is_active'];

    $passwordChanged = false;

    if (!empty($validated['password'])) {
        $user->password = Hash::make($validated['password']);
        $passwordChanged = true;
    }

    $user->save();

        $newValues = [ // STORE NEW VALUES TO COMPARE CHANGES IN THE LOGS */
            'department' => $user->department,
            'user_level' => $user->user_level,
            'is_active' => $user->is_active,
        ];

        if ($passwordChanged) {
            $oldValues['password'] = 'not shown';
            $newValues['password'] = 'changed';
        }

        ActivityLogger::log(
            module: 'users',
            action: 'updated',
            description: 'User ' . $user->employee_number . ' was updated.',
            targetType: 'user',
            targetId: $user->id,
            oldValues: $oldValues,
            newValues: $newValues
        );

    return redirect()
        ->route('users.index')
        ->with('success', 'User updated successfully.');
}
}