<?php

namespace Scool\EbreEscoolModel\Exceptions;

/**
 * Class InvalidNumberOfItemsException.
 *
 * @package Scool\EbreEscoolModel\Exceptions
 */
class InvalidNumberOfItemsException extends \Exception
{
    /**
     * @var string
     */
    private $collection;

    /**
     * InvalidStateException constructor.
     *
     * @param string $collection
     */
    public function __construct($collection)
    {

        $this->collection = $collection;
    }
}