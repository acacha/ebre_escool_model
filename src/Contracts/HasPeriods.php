<?php

namespace Scool\EbreEscoolModel\Contracts;

/**
 * Interface HasPeriods.
 */
interface HasPeriods
{
    /**
     * Ther periods associated to model.
     *
     * @return mixed
     */
    public function periods();
}