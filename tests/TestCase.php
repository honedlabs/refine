<?php

declare(strict_types=1);

namespace Honed\Refine\Tests;

use Honed\Refine\RefineServiceProvider;
use Honed\Refine\Tests\Stubs\Status;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Get the package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int,class-string>
     */
    protected function getPackageProviders($app)
    {
        return [
            RefineServiceProvider::class,
        ];
    }

    /**
     * Define the database migrations.
     *
     * @return void
     */
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

    /**
     * Define the environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function getEnvironmentSetUp($app)
    {
        config()->set('refine', require __DIR__.'/../config/refine.php');
        config()->set('database.default', 'testing');
        config()->set('scout.driver', 'array');
    }
}
