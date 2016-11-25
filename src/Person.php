<?php

namespace Scool\EbreEscoolModel;
use Scool\EbreEscoolModel\Traits\Periodable;

/**
 * Class Person.
 *
 * @package Scool\EbreEscoolModel
 */
class Person extends Model
{
    /**
     * Database connection name.
     *
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * @var string
     */
    protected $table = 'person';

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('person_' .$key) ;
    }

    /**
     * Accessor for name attribute.
     *
     * @param $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        return $this->person_givenName . ' ' . $this->person_sn1 . ' ' . $this->person_sn2;
    }

    /**
     * Accessor for email attribute.
     *
     * @param $value
     * @return string
     */
    public function getEmailAttribute($value)
    {
        return $this->person_email;
    }
}
