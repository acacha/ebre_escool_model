<?php

namespace Scool\EbreEscoolModel;
use Scool\EbreEscoolModel\Contracts\HasPeriods;
use Scool\EbreEscoolModel\Traits\Periodable;

/**
 * Class Course
 * @package Scool\EbreEscoolModel
 */
class Course extends Model implements HasPeriods
{
    protected $connection = 'ebre_escool';

    use Periodable;

    /**
     * Get the collection of studies associated to this course.
     */
    public function studies()
    {
        return $this->studiesMultiple()->get()->union($this->studySimple()->get());
    }

    /**
     * Get the studies associated to this course.
     */
    public function studiesMultiple()
    {
        return $this->belongsToMany(Study::class, 'course_studies',
            'course_studies_course_id', 'course_studies_study_id');
    }

    /**
     * Get the study that owns the course.
     */
    public function studySimple()
    {
        return $this->belongsTo(Study::class, 'course_study_id', 'studies_id');
    }

    /**
     * Get the study that owns the course.
     */
    public function study()
    {
        return $this->studySimple();
    }

    /**
     * The periods associated to model.
     *
     * @return mixed
     */
    public function periods()
    {
        return $this->belongsToMany(AcademicPeriod::class, 'courses_academic_periods',
            'courses_academic_periods_course_id', 'courses_academic_periods_academic_period_id');
    }

    /**
     * Get the study modules associated to this course.
     */
    public function modules()
    {
        return $this->belongsToMany(StudyModuleAcademicPeriod::class, 'study_module_ap_courses',
            'study_module_ap_courses_course_id','study_module_ap_courses_study_module_ap_id');
    }

}
