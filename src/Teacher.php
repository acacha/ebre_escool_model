<?php

namespace Scool\EbreEscoolModel;
use Scool\EbreEscoolModel\Traits\Periodable;

/**
 * Class Teacher.
 *
 * @package Scool\EbreEscoolModel
 */
class Teacher extends Model
{
    use Periodable;

    /**
     * Database connection name.
     *
     * @var string
     */
    protected $connection = 'ebre_escool';

    /**
     * @var string
     */
    protected $table = 'teacher';

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key) ?: $this->getAttribute('teacher_' .$key) ;
    }

    /**
     * Get the teacher periods.
     */
    public function periods()
    {
        return $this->belongsToMany(AcademicPeriod::class, 'teacher_academic_periods',
            'teacher_academic_periods_teacher_id', 'teacher_academic_periods_academic_period_id');
    }

    /**
     * Get teacher name.
     *
     * @param $value
     * @return mixed
     */
    public function getNameAttribute($value)
    {
        return $this->person->name;
    }

    /**
     * Get teacher name.
     *
     * @param $value
     * @return mixed
     */
    public function getEmailAttribute($value)
    {
        return $this->person->email;
    }

    /**
     * Is teacher active.
     *
     * @param $value
     * @return mixed
     */
    public function getActiveAttribute($value)
    {
        if ($this->details()->active()->first() != null) return true;
        return false;
    }

    /**
     * Get teacher code.
     *
     * @param $value
     * @return mixed
     */
    public function getCodeAttribute($value)
    {
        if ($this->active) {
            return $this->details()->active()->first()->code;
        }
        return null;
    }

    /**
     * Get teacher department.
     *
     * @param $value
     * @return mixed
     */
    public function getDepartmentAttribute($value)
    {
        if ($this->active) {
            return Department::findOrFail(($this->details()->active()->first()->department_id));
        }
        return null;
    }

    /**
     * Get the oerson associated to this teacher.
     */
    public function person()
    {
        return $this->belongsTo(Person::class,'teacher_person_id','person_id');
    }

    /**
     * Get the user associated to this teacher.
     */
    public function user()
    {
        return $this->belongsTo(User::class,'teacher_user_id','id');
    }

    /**
     * Get the teacher details for multiple academic periods
     */
    public function details()
    {
        return $this->hasMany(TeacherAcademicPeriod::class,
            'teacher_academic_periods_teacher_id');
    }
}
