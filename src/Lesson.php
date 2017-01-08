<?php

namespace Scool\EbreEscoolModel;

/**
 * Class Lesson.
 *
 * @package Scool\EbreEscoolModel
 */
class Lesson extends Model
{
    /**
     * Database Connection.
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * The periods associated to model.
     *
     * @return mixed
     */
    public function period()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    /**
     * Only enrollments active for current period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $this->scopeActiveOn($query, AcademicPeriod::current()->first()->id);
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
        return $query->where('lesson_academic_period_id', $period);
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
            str_repeat("_",$level) . 'Migrating ' . class_basename($this) . ' ('. $this->id . ' | ' . $this->academic_period_id .
            ') | Group ' . $this->group->name . ' ('. $this->group->id .
            ') | Teacher ' . $this->teacher->name . ' ('. $this->teacher->id .
            ') | Module ' . $this->study_module->name . ' ('. $this->study_module->id .
            ') | Location ' . $this->location->name . ' ('. $this->location->id .
            ') | Day ' . $this->day .
            '  | Timeslot ' . $this->time_slot_id .
            '...'
        );
    }

    /**
     * Get the user that owns the lesson.
     */
    public function user()
    {
        //TODO
    }

    /**
     * Get the study module that owns the lesson.
     */
    public function study_module()
    {
        return $this->belongsTo(StudyModule::class);
    }

    /**
     * Get the teacher that owns the lesson.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the location that owns the lesson.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the group that owns the enrollment.
     */
    public function group()
    {
        return $this->belongsTo(ClassroomGroup::class,'lesson_classroom_group_id');
    }

    /**
     * Get the new Lesson id migration.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function newLesson()
    {
        return $this->hasOne(LessonMigration::class);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('lesson_' .$key) ;
    }

}
