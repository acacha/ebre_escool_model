<?php

namespace Scool\EbreEscoolModel;

/**
 * Class Department.
 *
 * @package Scool\EbreEscoolModel
 */
class Department extends Model
{

    /**
     * Database connection name.
     *
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * Get all studies related to department for all academic periods.
     */
    public function allStudies()
    {
        return $this->belongsToMany(Study::class, 'study_department', 'department_id', 'study_id');
    }

    /**
     * Get only active studies related to department.
     */
    public function studies()
    {
        return $this->allStudies()->active();
    }

    /**
     * Get only active studies related to department.
     *
     * @param $period
     * @return mixed
     */
    public function studiesActiveOn($period)
    {
        return $this->allStudies()->activeOn($period);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('department_' .$key) ;
    }

}
