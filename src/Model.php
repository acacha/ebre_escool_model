<?php

namespace Scool\EbreEscoolModel;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;

class Model extends EloquentModel
{
    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute($this->model(). '_' .$key) ;
    }

    protected function model()
    {
        return Str::snake(class_basename($this));
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
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
