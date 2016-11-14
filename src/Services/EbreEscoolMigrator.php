<?php

namespace Scool\EbreEscoolModel\Services;

use Scool\EbreEscoolModel\Department;
use Scool\EbreEscoolModel\Services\Contracts\Migrator;
use Scool\EbreEscoolModel\Services\Contracts\Output;

class EbreEscoolMigrator implements Migrator
{
    protected $output;

    /**
     * @param Output $output
     */
    public function setOutput(Output $output)
    {
        $this->output = $output;
    }

    /**
     * EbreEscoolMigrator constructor.
     * @param $output
     */
    public function __construct(Output $output = null)
    {
        $this->output = $output;
    }

    public function migrate()
    {
        $this->migrateCurriculum();
    }

    private function migrateCurriculum()
    {
        foreach ($this->departments() as $department) {
            $this->output->info($department->name);
        }
    }

    protected function departments()
    {
        return Department::all();
    }
}