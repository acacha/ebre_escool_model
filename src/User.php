<?php

namespace Scool\EbreEscoolModel;

/**
 * Class User.
 *
 * @package Scool\EbreEscoolModel
 */
class User extends Model
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
    protected $table = 'users';

    /**
     * Accessor for name attribute.
     *
     * @param $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        if ($this->person) return $this->person->givenName . ' ' . $this->person->sn1 . ' ' . $this->person->sn2;
        return $value;
    }

    /**
     * Get the person that owns the comment.
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Accessor for email attribute.
     *
     * @param $value
     * @return string
     */
    public function getEmailAttribute($value)
    {
        if ($this->person) return $this->person->email;
        return $value;
    }

    /**
     * Accessor for secondary email attribute.
     *
     * @param $value
     * @return string
     */
    public function getSecondaryEmailAttribute($value)
    {
        if ($this->person) return $this->person->secondary_email;
        return $value;
    }
}
