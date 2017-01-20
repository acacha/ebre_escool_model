<?php

namespace Scool\EbreEscoolModel;
use Scool\EbreEscoolModel\Traits\Periodable;

/**
 * Class TeacherAcademicPeriod.
 *
 * @package Scool\EbreEscoolModel
 */
class TeacherAcademicPeriod extends Model
{

    /**
     * Database connection name.
     *
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * @var string
     */
    protected $table = 'teacher_academic_periods';

    /**
     * Get the teacher.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('teacher_academic_periods_' .$key) ;
    }

    /**
     * Only teachers active for current period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $this->scopeActiveOn($query,
                AcademicPeriod::current()->get()->first()->id);
    }

    /**
     * Only teachers active for given period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $period
     * @return mixed
     */
    public function scopeActiveOn($query, $period)
    {
        return $query->where('teacher_academic_periods_academic_period_id',$period);
    }
}
