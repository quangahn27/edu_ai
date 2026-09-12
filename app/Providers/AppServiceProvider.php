<?php

namespace App\Providers;

use App\Services\AI\AIProvider;
use App\Services\AI\GeminiService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIProvider::class, GeminiService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Configure rate limits for AI endpoints.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('ai-tutor', fn ($request) => Limit::perMinute(20)->by($request->user()?->id));
        RateLimiter::for('ai-quiz-generator', fn ($request) => Limit::perMinute(5)->by($request->user()?->id));
        RateLimiter::for('ai-essay-grading', fn ($request) => Limit::perMinute(10)->by($request->user()?->id));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
