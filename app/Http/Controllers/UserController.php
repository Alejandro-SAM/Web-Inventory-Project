<?php
/* THIS DOCUMENT IS FOR DISPLAYING AND MANAGING THE LOGIC FOR THE USER TABLE */
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
/*Badges imports */
use App\Models\Badge;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Inventory;

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
        $this->authorizeAdmin();

        $users = User::with('badges')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $badges = Badge::orderBy('name')->get();

        return view('users', compact('users', 'badges'));
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

            'badges' => [
                'nullable',
                'array',
            ],

            'badges.*' => [
                'integer',
                'exists:badges,id',
            ],

            'it_room_responsible_plant' => [
                'nullable',
                Rule::in(['B', 'D', 'G', 'H', 'MP']),
            ],
        ]);

    $passwordChanged = false;
    
    DB::transaction(function () use (
        $user,
        $validated,
        &$passwordChanged
    ) {

        /*
        |--------------------------------------------------------------------------
        | Current IT Room assignment
        |--------------------------------------------------------------------------
        |
        | Save the previous plant before changing any badge assignment.
        |
        */

        $itRoomBadge = Badge::where(
            'slug',
            'it_room_responsible'
        )->first();

        $previousItRoomPlant = null;

        if ($itRoomBadge) {
            $previousItRoomPlant = DB::table('user_badges')
                ->where('user_id', $user->id)
                ->where('badge_id', $itRoomBadge->id)
                ->where('is_active', true)
                ->value('plant');
        }

        /*
        |--------------------------------------------------------------------------
        | Update main user data
        |--------------------------------------------------------------------------
        */

        $user->department = $validated['department'] ?? null;
        $user->user_level = $validated['user_level'];
        $user->is_active = $validated['is_active'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $passwordChanged = true;
        }

        $user->save();


        /*
        |--------------------------------------------------------------------------
        | Update badges
        |--------------------------------------------------------------------------
        */

        if ($user->user_level !== 'User') {

            DB::table('user_badges')
                ->where('user_id', $user->id)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Remove previous IT Room responsibility
            |--------------------------------------------------------------------------
            */
            if ($previousItRoomPlant) {

                Inventory::query()
                    ->where('plant', $previousItRoomPlant)
                    ->whereRaw('UPPER(TRIM(location)) = ?', ['IT ROOM'])
                    ->update([
                        'end_user' => null,
                        'employee_id' => null,
                    ]);
            }

            return;
        }


        $selectedBadges = $validated['badges'] ?? [];

        $selectedPlant =
            $validated['it_room_responsible_plant'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Validate IT Room Responsible BEFORE changing anything
        |--------------------------------------------------------------------------
        */

        if ($itRoomBadge && $selectedPlant) {

            $existingResponsible = DB::table('user_badges')
                ->where('badge_id', $itRoomBadge->id)
                ->where('plant', $selectedPlant)
                ->where('is_active', true)
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($existingResponsible) {

                throw ValidationException::withMessages([
                    'it_room_responsible_plant' =>
                        "Plant {$selectedPlant} already has an active IT Room Responsible.",
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Deactivate normal badges
        |--------------------------------------------------------------------------
        */

        $normalBadgeQuery = DB::table('user_badges')
            ->where('user_id', $user->id);

        if ($itRoomBadge) {
            $normalBadgeQuery->where(
                'badge_id',
                '!=',
                $itRoomBadge->id
            );
        }

        $normalBadgeQuery->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Reactivate or create normal checkbox badges
        |--------------------------------------------------------------------------
        */

        foreach ($selectedBadges as $badgeId) {

            $badge = Badge::find($badgeId);

            if (!$badge) {
                continue;
            }

            if ($badge->slug === 'it_room_responsible') {
                continue;
            }

            $existingBadge = DB::table('user_badges')
                ->where('user_id', $user->id)
                ->where('badge_id', $badgeId)
                ->whereNull('plant')
                ->first();

            if ($existingBadge) {

                DB::table('user_badges')
                    ->where('id', $existingBadge->id)
                    ->update([
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);

            } else {

                DB::table('user_badges')->insert([
                    'user_id' => $user->id,
                    'badge_id' => $badgeId,
                    'plant' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | IT Room Responsible
        |--------------------------------------------------------------------------
        */

        if ($itRoomBadge) {

            /*
            |--------------------------------------------------------------------------
            | Deactivate previous IT Room assignments for this user
            |--------------------------------------------------------------------------
            */
            DB::table('user_badges')
                ->where('user_id', $user->id)
                ->where('badge_id', $itRoomBadge->id)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);


            /*
            |--------------------------------------------------------------------------
            | No plant selected
            |--------------------------------------------------------------------------
            |
            | The user is no longer responsible for an IT Room.
            | Clear End User and Employee ID from the previous plant's IT Room assets.
            |
            */
            if (!$selectedPlant) {

                if ($previousItRoomPlant) {

                    Inventory::query()
                        ->where('plant', $previousItRoomPlant)
                        ->whereRaw(
                            'UPPER(TRIM(location)) = ?',
                            ['IT ROOM']
                        )
                        ->update([
                            'end_user' => null,
                            'employee_id' => null,
                        ]);
                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Reactivate existing assignment or create it
            |--------------------------------------------------------------------------
            */

            $existingUserAssignment = DB::table('user_badges')
                ->where('user_id', $user->id)
                ->where('badge_id', $itRoomBadge->id)
                ->where('plant', $selectedPlant)
                ->first();

            if ($existingUserAssignment) {

                DB::table('user_badges')
                    ->where('id', $existingUserAssignment->id)
                    ->update([
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);

            } else {

                DB::table('user_badges')->insert([
                    'user_id' => $user->id,
                    'badge_id' => $itRoomBadge->id,
                    'plant' => $selectedPlant,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Clear assets from previous plant
            |--------------------------------------------------------------------------
            |
            | Only necessary when the responsible plant actually changed.
            |
            */

            if (
                $previousItRoomPlant &&
                $previousItRoomPlant !== $selectedPlant
            ) {

                Inventory::query()
                    ->where('plant', $previousItRoomPlant)
                    ->whereRaw(
                        'UPPER(TRIM(location)) = ?',
                        ['IT ROOM']
                    )
                    ->update([
                        'end_user' => null,
                        'employee_id' => null,
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Synchronize assets in new/current plant
            |--------------------------------------------------------------------------
            */

            Inventory::query()
                ->where('plant', $selectedPlant)
                ->whereRaw(
                    'UPPER(TRIM(location)) = ?',
                    ['IT ROOM']
                )
                ->update([
                    'end_user' => $user->name,
                    'employee_id' => $user->employee_number,
                ]);
        }
    });

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