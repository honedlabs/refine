<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Refine\Commands\FilterMakeCommand;
use Honed\Refine\Commands\RefineMakeCommand;
use Honed\Refine\Commands\SearchMakeCommand;
use Honed\Refine\Commands\SortMakeCommand;
use Illuminate\Support\ServiceProvider;

class RefineServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/refine.php', 'refine');
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->offerPublishing();

            $this->commands([
                FilterMakeCommand::class,
                RefineMakeCommand::class,
                SearchMakeCommand::class,
                SortMakeCommand::class,
            ]);
        }
    }

    /**
     * Register the publishing for the package.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/refine.php' => config_path('refine.php'),
        ], 'refine-config');

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs'),
        ], 'refine-stubs');
    }
}
