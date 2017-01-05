<?php

namespace Scool\EbreEscoolModel;

/**
 * Class EnrollmentSubmodule.
 *
 * @package Scool\EbreEscoolModel
 */
class EnrollmentSubmodule extends Model
{
    /**
     * Database connection.
     *
     * @var string
     */
    protected $connection = 'ebre_escool';

    protected $table = 'enrollment_submodules';

    /**
     * Only enrollment details active for current period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        //TODO
    }

    /**
     * Only enrollments details active for given period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $period
     * @return mixed
     */
    public function scopeActiveOn($query, $period)
    {
        //TODO
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
            str_repeat("_",$level) . 'Migrating ' . class_basename($this) . ' ('. $this->id
            . ') | Module ' . $this->module->name . ' ('. $this->module->id .
            ') | Submodule ' . $this->submodule->name . ' ('. $this->submodule->id .
            ') | Enrollment ' . ' ('. $this->enrollment->id .
            ') ...'
        );
    }

    /**
     * Get the study module that owns the enrollment details.
     */
    public function module()
    {
        return $this->belongsTo(StudyModule::class,'enrollment_submodules_moduleid');
    }

    /**
     * Get the study submodule that owns the enrollment details.
     */
    public function submodule()
    {
        return $this->belongsTo(StudySubModule::class,'enrollment_submodules_submoduleid');
    }

    /**
     * Get the group that owns the enrollment details.
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('enrollment_submodules_' .$key) ;
    }

}
