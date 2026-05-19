<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogsController extends Controller
{
    /**
     * Display logs dashboard.
     */
public function index(Request $request)
{
    $user = Auth::user();

    $tab = $request->query('tab', 'activity');

    /*
        Read users cannot view the logs dashboard,
        but their logins are still registered.
    */
    if ($user->user_level === 'Read') {
        abort(403, 'You do not have permission to view this page.');
    }

    $activityLogsQuery = ActivityLog::query()
        ->orderBy('created_at', 'desc');

    $loginLogsQuery = LoginLog::query()
        ->orderBy('login_at', 'desc');

    /*
        User level can only view its own records.
        Also, User cannot see logs from the users module.
    */
    if ($user->user_level === 'User') {
        $activityLogsQuery
            ->where('user_id', $user->id)
            ->where('module', '!=', 'users');

        $loginLogsQuery->where('user_id', $user->id);
    }

    /*
        Activity Logs filters
    */
if ($request->filled('activity_employee')) {
    $activityLogsQuery->where('employee_number', 'like', '%' . $request->activity_employee . '%');
}

if ($request->filled('activity_user')) {
    $activityLogsQuery->where('username', 'like', '%' . $request->activity_user . '%');
}

if ($request->filled('activity_module')) {
    $activityLogsQuery->where('module', $request->activity_module);
}

if ($request->filled('activity_action')) {
    $activityLogsQuery->where('action', $request->activity_action);
}

if ($request->filled('activity_description')) {
    $activityLogsQuery->where('description', 'like', '%' . $request->activity_description . '%');
}

if ($request->filled('activity_date_from')) {
    $activityLogsQuery->whereDate('created_at', '>=', $request->activity_date_from);
}

if ($request->filled('activity_date_to')) {
    $activityLogsQuery->whereDate('created_at', '<=', $request->activity_date_to);
}

/*
    Login Logs filters
*/

if ($request->filled('login_employee')) {
    $loginLogsQuery->where('employee_number', 'like', '%' . $request->login_employee . '%');
}

if ($request->filled('login_user')) {
    $loginLogsQuery->where('username', 'like', '%' . $request->login_user . '%');
}

if ($request->filled('login_role')) {
    $loginLogsQuery->where('role', $request->login_role);
}

if ($request->filled('login_ip')) {
    $loginLogsQuery->where('ip_address', 'like', '%' . $request->login_ip . '%');
}

if ($request->filled('login_date_from')) {
    $loginLogsQuery->whereDate('login_at', '>=', $request->login_date_from);
}

if ($request->filled('login_date_to')) {
    $loginLogsQuery->whereDate('login_at', '<=', $request->login_date_to);
}

    return view('logs', [
        'tab' => $tab,

        'activityLogs' => $activityLogsQuery
            ->paginate(10, ['*'], 'activity_page')
            ->appends($request->query()),

        'loginLogs' => $loginLogsQuery
            ->paginate(10, ['*'], 'login_page')
            ->appends($request->query()),
    ]);
}
}