<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Asset;
use App\Models\Report;
use App\Models\User;
use App\UserRole;
use App\UserStatus;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        Relation::morphMap([
            'activity' => Activity::class,
            'report' => Report::class,
        ]);

        Gate::define('access-admin', fn (User $user): bool => $user->status === UserStatus::Active
            && $user->role === UserRole::Admin);

        Gate::define('access-super-admin', fn (User $user): bool => $user->status === UserStatus::Active
            && $user->role === UserRole::SuperAdmin);

        View::composer(['layouts.admin', 'layouts.super_admin'], function ($view): void {
            $user = request()->user();
            $databaseNotifications = $user?->notifications()->latest()->limit(10)->get() ?? collect();

            if ($view->getName() === 'layouts.super_admin') {
                $assetAlerts = Asset::vehicleAlerts();

                $view->with([
                    'assetAlerts' => $assetAlerts,
                    'layoutNotifications' => $databaseNotifications,
                    'layoutUnreadNotificationCount' => $assetAlerts->count(),
                ]);

                return;
            }

            $view->with([
                'layoutNotifications' => $databaseNotifications,
                'layoutUnreadNotificationCount' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        });
    }
}
