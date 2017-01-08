<?php

namespace Scool\EbreEscoolModel;

/**
 * Class LessonMigration.
 *
 * @package Scool\EbreEscoolModel
 */
class LessonMigration extends Model
{
    /**
     * Database Connection.
     *
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * Get the new Lesson id migration.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

}
