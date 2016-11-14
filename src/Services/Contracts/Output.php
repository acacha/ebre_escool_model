<?php

namespace Scool\EbreEscoolModel\Services\Contracts;

/**
 * Interface Output
 * @package Scool\EbreEscoolModel\Services\Contracts
 */
interface Output
{
    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function info($string, $verbosity = null);

    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @param  string  $style
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function line($string, $style = null, $verbosity = null);
}