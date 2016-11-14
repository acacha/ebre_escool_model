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
    use Ebrescoolable;

    protected $primaryKey = 'academic_periods_id';

    /**
     * @return string
     */
    protected function model()
    {
        return 'academic_periods';
    }

    public function scopeCurrent($query)
    {
        return $query->where('academic_periods_current', 1);
    }
}
