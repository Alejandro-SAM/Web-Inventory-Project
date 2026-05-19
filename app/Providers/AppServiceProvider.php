<?php

namespace App\Providers;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;

            LoginLog::create([
                'user_id' => $user->id,

                // AJUSTAR SEGUN TABLA DE USUARIOS
                'employee_number' => $user->employee_number ?? null,
                'username' => $user->name ?? null,
                'role' => $user->user_level ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at' => now(),
            ]);
        });
    }
}