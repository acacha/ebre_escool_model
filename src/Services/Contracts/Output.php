<?php


namespace Scool\EbreEscoolModel\Services\Contracts;


interface Output
{
    public function info($string, $verbosity = null);
}