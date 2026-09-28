<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Report;
use App\Models\User;
use App\Services\WhatsAppService;
use App\UserRole;
use App\UserStatus;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('guest-reports', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('guest-feedback', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('login-ip', fn (Request $request): Limit => Limit::perMinute(20)->by($request->ip()));
        RateLimiter::for('otp-request', function (Request $request): array {
            $phone = (string) $request->input('telepon', '');
            $limits = [
                Limit::perMinute(8)->by('ip:'.$request->ip()),
                Limit::perHour(15)->by('ip-hour:'.$request->ip()),
            ];

            if ($phone !== '') {
                $limits[] = Limit::perHour(8)->by('phone:'.app(WhatsAppService::class)->normalizePhone($phone));
            }

            foreach ($limits as $limit) {
                $limit->response(fn (Request $request, array $headers): JsonResponse => response()->json([
                    'message' => 'Terlalu banyak permintaan kode OTP. Coba lagi nanti.',
                ], 429, $headers));
            }

            return $limits;
        });
        RateLimiter::for('ai-assistant', function (Request $request): array {
            $limits = [
                Limit::perMinute(10)->by('minute:'.$request->ip()),
                Limit::perMinute(30)->by('global-minute'),
                Limit::perHour(max(1, (int) config('services.gemini.hourly_limit', 60)))->by('hour:'.$request->ip()),
                Limit::perDay(max(1, (int) config('services.gemini.daily_limit', 1000)))->by('global'),
            ];
            foreach ($limits as $limit) {
                $limit->response(fn (Request $request, array $headers): JsonResponse => response()->json([
                    'message' => 'Batas penggunaan Asisten AI tercapai. Coba lagi nanti; Anda tetap dapat mengirim laporan melalui formulir.',
                ], 429, $headers));
            }

            return $limits;
        });

        Relation::morphMap([
            'activity' => Activity::class,
            'report' => Report::class,
        ]);

        Gate::define('access-admin', fn (User $user): bool => $user->status === UserStatus::Active
            && $user->role === UserRole::Admin);

        Gate::define('access-super-admin', fn (User $user): bool => $user->status === UserStatus::Active
            && $user->role === UserRole::SuperAdmin);

        Gate::define('access-internal', fn (User $user): bool => $user->can('access-admin')
            || $user->can('access-super-admin'));

        View::composer(['layouts.admin', 'layouts.super_admin'], function ($view): void {
            $user = request()->user();
            $databaseNotifications = $user?->notifications()->latest()->limit(10)->get() ?? collect();

            $view->with([
                'layoutNotifications' => $databaseNotifications,
                'layoutUnreadNotificationCount' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        });
    }
}
