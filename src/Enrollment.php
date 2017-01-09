<?php

namespace Scool\EbreEscoolModel;

/**
 * Class Enrollment.
 *
 * @package Scool\EbreEscoolModel
 */
class Enrollment extends Model
{
    /**
     * Database Connection.
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * Only enrollments active for current period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        $this->scopeActiveOn($query,AcademicPeriod::current()->shortname);
    }

    /**
     * Only enrollments active for given period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $period
     * @return mixed
     */
    public function scopeActiveOn($query, $period)
    {
        return $query->where('enrollment_periodid', $period);
    }

    /**
     * Show migration info.
     *
     * @param $output
     * @param int $level
     */
    public function showMigratingInfo($output, $level = 0)
    {
        $output->info(
            str_repeat("_",$level) . 'Migrating ' . class_basename($this) . ' ('. $this->id . ' | ' . $this->periodid
            . ') | Study ' . $this->study->name . ' ('. $this->study->id .
            ') | Course ' . $this->course->name . ' ('. $this->course->id .
            ') | Group ' . $this->group->name . ' ('. $this->group->id .
            ') | Person ' . $this->person->name . ' ('. $this->person->id .
            ') ...'
        );
    }

    /**
     * Get the study that owns the enrollment.
     */
    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    /**
     * Get the course that owns the enrollment.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the group that owns the enrollment.
     */
    public function group()
    {
        return $this->belongsTo(ClassroomGroup::class);
    }

    /**
     * Get the person that owns the enrollment.
     */
    public function person()
    {
        return $this->belongsTo(Person::class, 'enrollment_personid','person_id');
    }

    /**
     * Get the enrollment details (modules and submodules).
     */
    public function details()
    {
        return $this->hasMany(EnrollmentSubmodule::class, 'enrollment_submodules_enrollment_id');
    }

}
