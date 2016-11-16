<?php

namespace Scool\EbreEscoolModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Scool\EbreEscoolModel\Traits\Periodable;

/**
 * Class StudyModuleAcademicPeriod.
 *
 * @package Scool\EbreEscoolModel
 */
class StudyModuleAcademicPeriod extends EloquentModel
{

    /**
     * @var string
     */
    protected $table = 'study_module_academic_periods';

    /**
     * @var string
     */
    protected $primaryKey = 'study_module_academic_periods_id';

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('study_module_academic_periods_' .$key) ;
    }

    /**
     * Get the study module related to this model.
     */
    public function module()
    {
        return $this->belongsTo(StudyModule::class,
            'study_module_academic_periods_study_module_id', 'study_module_id');
    }

    /**
     * Only study modules active for current period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $this->scopeActiveOn($query,AcademicPeriod::current());
    }

    /**
     * Only studies modules active for given period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $period
     * @return mixed
     */
    public function scopeActiveOn($query, $period)
    {
        return $query->where(
            'study_module_academic_periods_academic_period_id',
            $period
        );
    }
}
