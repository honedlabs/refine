<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Refine\Console\Commands\FilterMakeCommand;
use Honed\Refine\Console\Commands\SearchMakeCommand;
use Honed\Refine\Console\Commands\SortMakeCommand;
use Illuminate\Support\ServiceProvider;

class RefineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/refine.php', 'refine');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FilterMakeCommand::class,
                SortMakeCommand::class,
                SearchMakeCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/refine.php' => config_path('refine.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs'),
            ], 'stubs');

        }
    }
}
