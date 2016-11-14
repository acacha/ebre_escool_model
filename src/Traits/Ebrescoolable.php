<?php

namespace Scool\EbreEscoolModel\Traits;
use Illuminate\Support\Str;

/**
 * Class Ebrescoolable.
 *
 * @package Scool\EbreEscoolModel
 */
trait Ebrescoolable
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

    /**
     * Get model.
     *
     * @return string
     */
    protected function model()
    {
        return Str::snake(class_basename($this));
    }
}