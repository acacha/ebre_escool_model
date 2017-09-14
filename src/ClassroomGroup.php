<?php

namespace Scool\EbreEscoolModel;

/**
 * Class ClassroomGroup.
 *
 * @package Scool\EbreEscoolModel
 */
class ClassroomGroup extends Model
{
    protected $connection = 'ebre_escool';

    /**
     * Get the collection of studies associated to this course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'classroom_group_course_id', 'course_id');
    }

    /**
     * Get the periods that this classroom has been active.
     */
    public function periods()
    {
        return $this->belongsToMany(AcademicPeriod::class,'classroom_group_academic_periods','classroom_group_academic_periods_academic_period_id','classroom_group_academic_periods_classroom_group_id');
    }


}
