<?php

namespace Scool\EbreEscoolModel\Console\Commands;

use Illuminate\Console\Command;
use Scool\EbreEscoolModel\Services\Contracts\Migrator;
use Scool\EbreEscoolModel\Services\Contracts\Output;

/**
 * Class MigrateEbreEscool.
 *
 * @package Scool\EbreEscoolModel\Console\Commands
 */
class MigrateEbreEscool extends Command implements Output
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "scool:migrate {filters* : filters to apply} {--debug}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates old EbreEscool database';

    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * MigrateEbreEscool constructor.
     *
     * @param Migrator $migrator
     */
    public function __construct(Migrator $migrator)
    {
        parent::__construct();
        $this->migrator = $migrator;
        $this->migrator->setOutput($this);
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->migrator->setVerbose($this->option('debug'));
        $this->migrator->migrate($this->argument('filters'));
    }
}
