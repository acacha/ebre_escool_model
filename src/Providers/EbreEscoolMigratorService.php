<?php

namespace Scool\EbreEscoolModel\Providers;

use Illuminate\Support\ServiceProvider;
use Scool\EbreEscoolModel\Services\Contracts\Migrator;
use Scool\EbreEscoolModel\Services\EbreEscoolMigrator;

class EbreEscoolMigratorService extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            Migrator::class,
            EbreEscoolMigrator::class);

    }
}
