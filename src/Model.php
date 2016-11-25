<?php

namespace Scool\EbreEscoolModel;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;
use Scool\EbreEscoolModel\Traits\Ebrescoolable;

/**
 * Class Model
 * @package Scool\EbreEscoolModel
 */
class Model extends EloquentModel
{
    use Ebrescoolable;

    /**
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * Get the primary key for the model.
     *
     *
     */
    public function getKeyName()
    {
        return Str::snake(class_basename($this)) . '_' . $this->primaryKey;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) {
            return $this->table;
        }

        return str_replace('\\', '', Str::snake(class_basename($this)));
    }
}
