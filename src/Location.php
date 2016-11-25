<?php

namespace Scool\EbreEscoolModel;
use Scool\EbreEscoolModel\Traits\Periodable;

/**
 * Class Location.
 *
 * @package Scool\EbreEscoolModel
 */
class Location extends Model
{
    use Periodable;

    /**
     * Database connection name.
     *
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * @var string
     */
    protected $table = 'location';

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('location_' .$key) ;
    }

}
