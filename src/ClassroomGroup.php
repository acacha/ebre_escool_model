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
}
