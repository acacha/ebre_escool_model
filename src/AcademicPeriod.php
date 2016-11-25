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

}
