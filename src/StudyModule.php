<?php

namespace Scool\EbreEscoolModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Class StudyModule.
 *
 * @package Scool\EbreEscoolModel
 */
class StudyModule extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'study_module';

    /**
     * @var string
     */
    protected $primaryKey = 'study_module_id';

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
            if ($this->getAttribute($this->model(). '_' .$key)) {
                $attribute = $this->getAttribute('study_module_' .$key);
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
     */
    public function getAttributeInPeriod($key)
    {
        if ($attribute = $this->modulesByPeriod()->active()->first()->getAttribute($key)) {
            return $attribute;
        }
        return $this->modulesByPeriod()->active()->first()->getAttribute('study_module_' .$key);
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

}
