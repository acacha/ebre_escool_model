<?php

namespace Scool\EbreEscoolModel;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Scool\EbreEscoolModel\Contracts\HasPeriods;
use Scool\EbreEscoolModel\Traits\Periodable;

/**
 * Class Study
 * @package Scool\EbreEscoolModel
 */
class Study extends EloquentModel implements HasPeriods
{
    use Periodable;

    protected $connection = 'ebre_escool';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'studies';

    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = 'studies_id';

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('studies_' .$key) ;
    }

    /**
     * Get the study periods.
     */
    public function periods()
    {
        return $this->belongsToMany(AcademicPeriod::class, 'studies_academic_periods',
            'studies_academic_periods_study_id', 'studies_academic_periods_academic_period_id');
    }

    /**
     * Get the study courses.
     */
    public function allCourses()
    {
        return $this->multiple() ? $this->allCoursesForMultiple() : $this->allCoursesForSingle();
    }

    /**
     * Get all courses for studies of type multiple.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    protected function allCoursesForMultiple() {
        return $this->belongsToMany(Course::class, 'course_studies',
            'course_studies_study_id','course_studies_course_id');
    }

    /**
     * Get all courses for studies of type single.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    protected function allCoursesForSingle() {
        return $this->hasMany(Course::class,'course_study_id','studies_id');
    }

    /**
     * Get only active studies related to department.
     */
    public function courses()
    {
        return $this->allCourses()->active();
    }


    /**
     * Get only active studies related to department.
     *
     * @param $period
     * @return mixed
     */
    public function coursesActiveOn($period)
    {
        return $this->allCourses()->activeOn($period);
    }

    /**
     * Get the active study modules for this study.
     */
    public function modules()
    {
        $courses = $this->courses->pluck('course_id');
        return StudyModule::whereHas('modulesByPeriod.courses', function ($query) use ($courses)  {
            $query->whereIn('course_id',$courses);
        });
    }

    /**
     * Get the active study modules for this study on period id.
     *
     */
    public function modulesActiveOn($periodId)
    {
        $courses = $this->coursesActiveOn($periodId)->pluck('course_id');
        return StudyModule::whereHas('modulesByPeriod.courses', function ($query) use ($courses)  {
            $query->whereIn('course_id',$courses);
        });
    }

    /**
     * Get modules attribute.
     *
     * @return mixed
     */
    public function getModulesAttribute()
    {
        return $this->modules()->get();
    }

    /**
     * Check if study is multiple (like ASIX-DAM)
     * @return boolean
     */
    public function multiple() {
        return (boolean) $this->studies_multiple;
    }
}
