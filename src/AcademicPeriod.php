<?php

namespace Scool\EbreEscoolModel;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Scool\EbreEscoolModel\Traits\Ebrescoolable;

/**
 * Class AcademicPeriod
 * @package Scool\EbreEscoolModel
 */
class AcademicPeriod extends EloquentModel
{
    protected $connection = 'ebre_escool';

    use Ebrescoolable;

    /**
     * @var string
     */
    protected $primaryKey = 'academic_periods_id';

    /**
     * @return string
     */
    protected function model()
    {
        return 'academic_periods';
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeCurrent($query)
    {
        return $query->where('academic_periods_current', 1);
    }

    /**
     * Get the classrooms that are active for the period.
     */
    public function classrooms()
    {
        return $this->belongsToMany(ClassroomGroup::class,'classroom_group_academic_periods','classroom_group_academic_periods_academic_period_id','classroom_group_academic_periods_classroom_group_id');
    }

    /**
     * Get the teachers that are active for the period.
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class,'classroom_group_academic_periods','classroom_group_academic_periods_academic_period_id','classroom_group_academic_periods_classroom_group_id');
    }

}
