<?php

namespace Scool\EbreEscoolModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Scool\EbreEscoolModel\Traits\Periodable;

/**
 * Class StudyModuleType.
 *
 * @package Scool\EbreEscoolModel
 */
class StudyModuleType extends EloquentModel
{
    use Periodable;

    protected $connection = 'ebre_escool';

    /**
     * @var string
     */
    protected $table = 'study_module_type';

    /**
     * @var string
     */
    protected $primaryKey = 'study_module_type_id';

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        $attribute= null;
        if ($this->getAttribute($key)) {
            $attribute = $this->getAttribute($key);
        } else {
            if ($this->getAttribute('study_module_type_' .$key)) {
                $attribute = $this->getAttribute('study_module_type_' .$key);
            }
        }
        return $attribute;
    }

    /**
     * Get the study modules of this type.
     */
    public function modules()
    {
        return $this->hasMany(StudyModule::class,'study_module_type', 'study_module_id');
    }
}
