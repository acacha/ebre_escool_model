<?php

namespace Scool\EbreEscoolModel;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Scool\EbreEscoolModel\Scopes\ActivePeriodScope;

/**
 * Class Study
 * @package Scool\EbreEscoolModel
 */
class Study extends EloquentModel
{
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
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

    }

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
     * Only studies active for current period.
     *
     * @param $query
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
     * @param $query
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
