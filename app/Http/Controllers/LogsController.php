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

        /*
            Default table:
            - activity = system activity logs
            - logins = login records
        */
        $tab = $request->query('tab', 'activity');

        /*
            READ USERS CANNOT VIEW LOGS
            THEIR LOGS ARE REGISTERED NONETHELESS
        */
        if ($user->user_level === 'Read') {
            abort(403, 'You do not have permission to view this page.');
        }

        $activityLogsQuery = ActivityLog::query()
            ->orderBy('created_at', 'desc');

        $loginLogsQuery = LoginLog::query()
            ->orderBy('login_at', 'desc');

        /*
            USER LEVEL ONLY VIEWS THEIR OWN LOGS
            ADMIN VIEWS ALL LOGS
        */
        if ($user->user_level === 'User') {
            $activityLogsQuery->where('user_id', $user->id);
            $loginLogsQuery->where('user_id', $user->id);
        }

        return view('logs', [
            'tab' => $tab,
            'activityLogs' => $activityLogsQuery->paginate(10, ['*'], 'activity_page'),
            'loginLogs' => $loginLogsQuery->paginate(10, ['*'], 'login_page'),
        ]);
    }
}