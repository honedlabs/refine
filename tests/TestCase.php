<?php

declare(strict_types=1);

namespace Honed\Refine\Tests;

use Honed\Core\CoreServiceProvider;
use Honed\Refine\RefineServiceProvider;
use Honed\Refine\Tests\Stubs\Status;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__.'/Stubs');
        Inertia::setRootView('app');
        config()->set('inertia.testing.ensure_pages_exist', false);
        config()->set('inertia.testing.page_paths', [realpath(__DIR__)]);

        config()->set('refine', require __DIR__.'/../config/refine.php');
    }

    protected function getPackageProviders($app)
    {
        return [
            InertiaServiceProvider::class,
            RefineServiceProvider::class,
            CoreServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default(Status::Available->value);
            $table->unsignedInteger('price')->default(0);
            $table->boolean('best_seller')->default(false);
            $table->timestamps();
        });

        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->unsignedBigInteger('quantity')->default(0);
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('refine', require __DIR__.'/../config/refine.php');
        config()->set('database.default', 'testing');
        config()->set('scout.driver', 'array');
    }
}
