<?php

namespace Scool\EbreEscoolModel\Services\Contracts;

/**
 * Interface Migrator.
 *
 * @package Scool\EbreEscoolModel\Services\Contracts
 */
interface Migrator
{

    /**
     * Migrates old database to new database.
     *
     * @param array $filters
     * @return mixed
     */
    public function migrate(array $filters);

    /**
     * Set output.
     *
     * @param Output $output
     * @return mixed
     */
    public function setOutput(Output $output);

    /**
     * Set verbose.
     *
     * @param boolean $verbose
     * @return mixed
     */
    public function setVerbose($verbose);


}