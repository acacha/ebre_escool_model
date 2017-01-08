<?php

namespace Scool\EbreEscoolModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Class Timeslot.
 *
 * @package Scool\EbreEscoolModel
 */
class Timeslot extends EloquentModel
{
    /**
     * Database connection name.
     *
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'time_slot';

    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = "time_slot_id";

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('time_slot_' .$key) ;
    }

    /**
     * Show migration info.
     *
     * @param $output
     * @param int $level
     */
    public function showMigratingInfo($output, $level = 0)
    {
        $output->info(
            str_repeat("_",$level) . 'Migrating ' . class_basename($this) . ' ('. $this->id . ')' .
            ' | Start time ' . $this->start_time .
            ' | End time '   . $this->end_time .
            '...'
        );
    }

}
