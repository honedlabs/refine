<?php

declare(strict_types=1);

namespace Honed\Refine;

use Illuminate\Support\ServiceProvider;

final class RefineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/refine.php', 'refine');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/refine.php' => config_path('refine.php'),
        ], 'config');
    }
}
