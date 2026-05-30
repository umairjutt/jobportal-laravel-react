<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Broadcast::routes(['middleware' => ['auth:sanctum']]);
        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(60)->by($r->user()?->id ?? $r->ip()));
    }
}
