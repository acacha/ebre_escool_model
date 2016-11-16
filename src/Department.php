<?php

namespace Scool\EbreEscoolModel;

/**
 * Class Department
 * @package Scool\EbreEscoolModel
 */
class Department extends Model
{
    /**
     * Get all studies related to deparment for all academic periods.
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

}
