<?php

namespace Scool\EbreEscoolModel\Services;

use Scool\EbreEscoolModel\AcademicPeriod;
use Scool\EbreEscoolModel\Course;
use Scool\EbreEscoolModel\Department;
use Scool\EbreEscoolModel\Services\Contracts\Migrator;
use Scool\EbreEscoolModel\Services\Contracts\Output;
use Scool\EbreEscoolModel\StudySubModule;

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
        $this->period = 7;
        $this->output->info('Migrating period: ' . $this->period);

        $this->migrateCurriculum();
    }

    /**
     * Migrate curriculum.
     */
    private function migrateCurriculum()
    {
        ! count($this->departments()) ? dd('Error 0 departments') : null;
        foreach ($this->departments() as $department) {
            $this->output->info('Migrating department: ' . $department->name . '('. $department->id . ')...');

            ! count($department->studiesActiveOn($this->period)->get()) ? dd('Error 0 studies') : null;
            foreach ($department->studiesActiveOn($this->period)->get() as $study) {
                //echo $study->periods->pluck('academic_periods_id');
                $suffix = $study->multiple() ? ". <bg=yellow;options=bold>Study is multiple!</>" : "";
                $this->output->info('  Migrating study: ' . $study->name . '('. $study->id . ') ' . $suffix . ' ...');
                ! count($study->allCourses()->active()->get() ) ? dd('Error 0 courses') : null;
                foreach ($study->allCourses()->active()->get() as $course) {
                    //echo $course->periods->pluck('academic_periods_id');
                    $this->output->info('   Migrating course: ' . $course->name . '('. $course->id . ')...');
                    $modules = $course->modules()->active()->get();
                    ! count($modules) ? dd('Error 0 modules') : null;
                    $sortedModules = $modules->sortBy(
                        function ($module, $key) {
                            return $module->order;
                        }
                    );

                    foreach ($sortedModules as $module) {
                        //dd($module->module()->first());
                        //echo $module->periods->pluck('academic_periods_id');
                        $this->output->info('    Migrating module: ' . $module->order . ' ' .  $module->name . ' | ' . $module->shortname . ' ('. $module->id . ')...');
                        ! count($module->module()->first()->submodules()->active()->get()) ? dd('Error 0 submodules') : null;
                        foreach ($module->module()->first()->submodules()->active()->get() as $submodule) {
                            echo $submodule->periods->pluck('academic_periods_id');
                            $this->output->info('     Migrating submodule: ' . $submodule->order . ' ' .  $submodule->name . ' | ' . $submodule->shortname . ' ('. $submodule->id . ')...');

                            $this->migrateSubmodule($submodule);
                        }
                    }
                }
            }
        }
    }

    public function migrateSubmodule(StudySubModule $submodule)
    {
        
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
        //return Department::whereIn('department_id', [2])->get();
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