<?php

namespace Scool\EbreEscoolModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Scool\EbreEscoolModel\Traits\Periodable;

/**
 * Class StudySubModule.
 *
 * @package Scool\EbreEscoolModel
 */
class StudySubModule extends EloquentModel
{
    use Periodable;

    /**
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * @var string
     */
    protected $table = 'study_submodules';

    /**
     * @var string
     */
    protected $primaryKey = 'study_submodules_id';

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->getAttribute($key)) {
            $attribute = $this->getAttribute($key);
        } else {
            if ($this->getAttribute('study_submodules_' .$key)) {
                $attribute = $this->getAttribute('study_submodules_' .$key);
            } else {
                $attribute = $this->getAttributeInPeriod($key);
            }
        }

        return $attribute;
    }

    /**
     *
     * Search for attribute in current period.
     *
     * @param $key
     * @return string
     */
    public function getAttributeInPeriod($key)
    {
        if ($moduleByPeriod = $this->modulesByPeriod()->active()->first()) {
            if ($attribute = $moduleByPeriod->getAttribute($key)) {
                return $attribute;
            }
            return $moduleByPeriod->getAttribute('study_module_academic_periods_' .$key);
        }
        return '';
    }

    /**
     * Get modules info by periods.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modulesByPeriod()
    {
        return $this->hasMany(StudyModuleAcademicPeriod::class,
            'study_module_academic_periods_study_module_id', 'study_module_id'
            );
    }

    /**
     * Get the study periods.
     */
    public function periods()
    {
        return $this->belongsToMany(AcademicPeriod::class, 'study_submodules_academic_periods',
            'study_submodules_academic_periods_study_submodules_id', 'study_submodules_academic_periods_academic_period_id');
    }

    /**
     * Get the study module that owns the study submodule.
     */
    public function module()
    {
        return $this->belongsTo(StudyModule::class, 'study_submodules_study_module_id', 'study_module_id');
    }

    /**
     * Get the type of submodule.
     *
     * @param  string  $value
     * @return StudyModuleType
     */
    public function getTypeAttribute($value)
    {
        return $this->module->type;
    }
}
