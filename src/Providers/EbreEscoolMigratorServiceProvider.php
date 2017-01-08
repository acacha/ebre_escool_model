<?php

namespace Scool\EbreEscoolModel\Providers;

use Illuminate\Support\ServiceProvider;
use Scool\EbreEscoolModel\Services\Contracts\Migrator;
use Scool\EbreEscoolModel\Services\EbreEscoolMigrator;

/**
 * Class EbreEscoolMigratorServiceProvider.
 *
 * @package Scool\EbreEscoolModel\Providers
 */
class EbreEscoolMigratorServiceProvider extends ServiceProvider
{
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
        if (!defined('EBRE_ESCOOL_MODEL_PATH')) {
            define('EBRE_ESCOOL_MODEL_PATH', realpath(__DIR__.'/../../'));
        }
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrations();
    }

    /**
     * Load migrations.
     */
    private function loadMigrations()
    {
        $this->loadMigrationsFrom(EBRE_ESCOOL_MODEL_PATH . '/database/migrations');
    }
}
