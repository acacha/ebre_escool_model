<?php

namespace Scool\EbreEscoolModel\Services;

use Scool\EbreEscoolModel\AcademicPeriod;
use Scool\EbreEscoolModel\Course;
use Scool\EbreEscoolModel\Department;
use Scool\EbreEscoolModel\Services\Contracts\Migrator;
use Scool\EbreEscoolModel\Services\Contracts\Output;

/**
 * Class EbreEscoolMigrator
 * @package Scool\EbreEscoolModel\Services
 */
class EbreEscoolMigrator implements Migrator
{
    /**
     * @var Output
     */
    protected $output;

    /**
     * Current period.
     *
     * @var
     */
    protected $period;

    /**
     * @param Output $output
     */
    public function setOutput(Output $output)
    {
        $this->output = $output;
    }

    /**
     * EbreEscoolMigrator constructor.
     * @param $output
     */
    public function __construct(Output $output = null)
    {
        $this->output = $output;
    }

    /**
     * Migrate old database to new database.
     */
    public function migrate()
    {
        $this->period = 5;
        $this->migrateCurriculum();
    }

    /**
     * Migrate curriculum.
     */
    private function migrateCurriculum()
    {
        foreach ($this->departments() as $department) {
            $this->output->info('Migrating department: ' . $department->name . '('. $department->id . ')...');
            foreach ($department->studiesActiveOn($this->period)->get() as $study) {
                $suffix = $study->multiple() ? ". <bg=yellow;options=bold>Study is multiple!</>" : "";
                $this->output->info('  Migrating study: ' . $study->name . '('. $study->id . ') ' . $suffix . ' ...');
                foreach ($study->allCourses()->get() as $course) {
                    $this->output->info('   Migrating course: ' . $course->name . '('. $course->id . ')...');

                }
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function academicPeriods()
    {
        return AcademicPeriod::all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function departments()
    {
        //Avoid using FOL because is a transversal department
        return Department::whereNotIn('department_id', [3])->get();
    }

    /**
     * @param $collection
     * @param $field
     * @param string $separator
     * @return mixed
     */
    private function printCollection($collection, $field, $separator = ' | ')
    {
        return $collection->implode($field, $separator);
    }
}