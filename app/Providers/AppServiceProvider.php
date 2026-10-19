<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());
        Gate::define('manage-accounts', fn (User $user, string $group): bool => $user->hasRole('owner') || ($group === 'customers' && $user->isAdmin()));
        Gate::define('view-reports', fn (User $user): bool => $user->hasRole('owner', 'cashier'));
        Gate::define('owner', fn (User $user): bool => $user->hasRole('owner'));
        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('public-forms', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));
    }
}
