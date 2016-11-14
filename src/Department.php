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
     * Get all studies related to deparment for all academic periods.
     */
    public function all_studies()
    {
        return $this->belongsToMany(Study::class, 'study_department', 'department_id', 'study_id');
    }

    /**
     * Get only active studies related to department.
     */
    public function studies()
    {
        return $this->all_studies()->active();
    }

    /**
     * Get only active studies related to department.
     */
    public function studiesActiveOn($period)
    {
        return $this->all_studies()->activeOn($period);
    }

}
