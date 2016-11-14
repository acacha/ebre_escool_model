<?php

namespace Scool\EbreEscoolModel\Services\Contracts;

/**
 * Interface Migrator
 * @package Scool\EbreEscoolModel\Services\Contracts
 */
interface Migrator
{
    /**
     * Migrates
     */
    public function migrate();

    /**
     * Set output.
     *
     * @param Output $output
     * @return mixed
     */
    public function setOutput(Output $output);
}