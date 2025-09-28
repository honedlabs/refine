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
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'refine');

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
     */
    protected function offerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs'),
        ], 'refine-stubs');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/refine'),
        ], 'refine-lang');
    }
}
