<?php

namespace Scool\EbreEscoolModel\Traits;

/**
 * Class Periodable.
 *
 * @package Scool\EbreEscoolModel\Traits
 */
trait Periodable
{
    /**
     * Only studies active for current period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->whereHas('periods', function($query){
            $query->where('academic_periods_current', 1);
        });
    }

    /**
     * Only studies active for given period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $period
     * @return mixed
     */
    public function scopeActiveOn($query, $period)
    {
        return $query->whereHas('periods', function($query) use ($period){
            $query->where('academic_periods_id', $period);
        });
    }
}