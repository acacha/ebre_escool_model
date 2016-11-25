<?php

namespace Scool\EbreEscoolModel\Services;

use Illuminate\Database\QueryException;
use Scool\Curriculum\Models\Module;
use Scool\Curriculum\Models\Submodule;
use Scool\EbreEscoolModel\AcademicPeriod;
use Scool\EbreEscoolModel\Course;
use Scool\EbreEscoolModel\Department;
use Scool\Curriculum\Models\Department as ScoolDepartment;
use Scool\EbreEscoolModel\Exceptions\InvalidNumberOfItemsException;
use Scool\EbreEscoolModel\Location;
use Scool\Foundation\Location as ScoolLocation;
use Scool\EbreEscoolModel\Services\Contracts\Migrator;
use Scool\EbreEscoolModel\Services\Contracts\Output;
use Scool\EbreEscoolModel\Study;
use Scool\EbreEscoolModel\StudyModule;
use Scool\EbreEscoolModel\StudyModuleAcademicPeriod;
use Scool\EbreEscoolModel\StudySubModule;
use Scool\EbreEscoolModel\Teacher;
use Scool\Foundation\User;

/**
 * Class EbreEscoolMigrator.
 *
 * @package Scool\EbreEscoolModel\Services
 */
class EbreEscoolMigrator implements Migrator
{
    /**
     * @var Output
     */
    protected $output;

    /**
     * Filters to apply.
     *
     * @var array
     */
    protected $filters;

    /**
     * Current period in migration process.
     *
     * @var
     */
    protected $period;

    /**
     * Destination database connection.
     *
     * @var
     */
    protected $destinationConnection;

    /**
     * Env variable with destination connection.
     */
    const DESTINATION_CONNECTION_ENV_VAR = 'DB_SCOOL_CONNECTION';

    /**
     * Destination connection prefix
     */
    const DESTINATION_CONNECTION_PREFIX = 'scool';

    /**
     * Verbose output is active?
     *
     * @var
     */
    protected $verbose = true;

    /**
     * Department is used at current migration process.
     *
     * @var Department
     */
    protected $department;

    /**
     * @return ScoolDepartment
     */
    public function getScoolDepartment()
    {
        return $this->scoolDepartment;
    }

    /**
     * @param ScoolDepartment $scoolDepartment
     */
    public function setScoolDepartment($scoolDepartment)
    {
        $this->scoolDepartment = $scoolDepartment;
    }

    /**
     * Scool department is used at current migration process.
     *
     * @var ScoolDepartment
     */
    protected $scoolDepartment;

    /**
     * Study is used at current migration process.
     *
     * @var Study
     */
    protected $study;

    /**
     * Course is used at current migration process.
     *
     * @var Course
     */
    protected $course;

    /**
     * Study module is used at current migration process.
     *
     * @var StudyModule
     */
    protected $module;

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param Study $study
     */
    public function setStudy($study)
    {
        $this->study = $study;
    }

    /**
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param Course $course
     */
    public function setCourse($course)
    {
        $this->course = $course;
    }

    /**
     * @return StudyModule
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param StudyModule $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * EbreEscoolMigrator constructor.
     *
     * @param Output $output
     */
    public function __construct(Output $output = null)
    {
        $this->output = $output;
    }

    /**
     * Set output.
     *
     * @param Output $output
     * @return void
     */
    public function setOutput(Output $output)
    {
        $this->output = $output;
    }

    /**
     * @param mixed $verbose
     * @return mixed|void
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }

    /**
     * Migrate old database to new database.
     *
     * @param array $filters
     * @return void
     */
    public function migrate(array $filters)
    {
        $this->filters =$filters;
        foreach ($this->academicPeriods() as $period) {
            $this->period = $period->id;
            $this->setDestinationConnectionByPeriod($this->period);
            $this->switchToDestinationConnection();
            $this->output->info('Migrating period: ' . $period->name . '(' .  $period->id . ')');
            $this->truncate();
//            $this->migrateTeachers();
//            $this->migrateLocations();
            $this->migrateCurriculum();
       }
    }

    /**
     * Migrate teachers.
     */
    protected function migrateTeachers()
    {
        $this->output->info('### Migrating teachers ###');
        foreach ($this->teachers() as $teacher) {
            $this->showMigratingInfo($teacher, 1);
            $this->output->info('  email: ' . $teacher->email);
            $this->migrateTeacher($teacher);
        }
        $this->output->info(
            '### END Migrating teachers. Migrated ' . count($this->teachers()) .  ' teachers   ###');
    }

    /**
     * Migrate teacher.
     *
     * @param Teacher $teacher
     */
    protected function migrateTeacher(Teacher $teacher) {
        $user = User::firstOrNew([
            'email' => $teacher->email,
        ]);
        $user->name = $teacher->name;
        $user->password = bcrypt('secret');
        $user->remember_token = str_random(10);
        $user->save();
    }

    /**
     *
     */
    protected function migrateLocations()
    {
        $this->output->info('### Migrating locations ###');
        foreach ($this->locations() as $location) {
            $this->showMigratingInfo($location, 1);
            $this->migrateLocation($location);
        }
        $this->output->info(
            '### END Migrating locations. Migrated ' . count($this->locations()) .  ' locations   ###');
    }

    /**
     * @param $location
     */
    protected function migrateLocation($location) {
        $user = ScoolLocation::firstOrNew([
            'name' => $location->name,
        ]);
        $user->save();
        $user->shortname = $location->shortname;
        $user->description = $location->description;
        $user->code = $location->external_code;
    }

    /**
     * Migrate curriculum.
     */
    private function migrateCurriculum()
    {
        $level= 0;
        foreach ($this->departments() as $department) {
            $this->setDepartment($department);
            $this->showMigratingInfo($department,++$level);
            $this->migrateDepartment($department);
            foreach ($this->studies($department) as $study) {
                $this->setStudy($study);
                $this->showMigratingInfo($study,++$level);
                foreach ($this->courses($study) as $course) {
                    $this->setCourse($course);
                    $this->showMigratingInfo($course,++$level);
                    foreach ($this->modules($course) as $module) {
                        $this->setModule($module);
                        $this->showMigratingInfo($module, ++$level);
                        foreach ($this->submodules($module) as $submodule) {
                            $this->migrateSubmodule($submodule);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Department $department
     */
    protected function migrateDepartment(Department $department) {

        $this->setScoolDepartment(
            $this->createDepartment(Department::findOrFail($department->parent_department_id))
        );
    }

    /**
     * Create scool department using ebre-escool department.
     * 
     * @param Department $srcDepartment
     * @return mixed
     */
    protected function createDepartment(Department $srcDepartment)
    {
         $department = ScoolDepartment::firstOrNew([
            'name'         => $srcDepartment->name,
        ]);
        $department->location_id = $this->getLocation($srcDepartment->location_id)->id;
        $department->save();
        $department->shortname = $srcDepartment->shortname;
        $department->head = $this->getTeacher($srcDepartment->head);

        return $department;

    }

    /**
     * get Scool location by ebre_escool location id.
     *
     * @param $id
     * @return mixed
     */
    protected function getLocation($id)
    {
        return ScoolLocation::where('name', Location::findOrFail($id)->name)->firstOrFail();
    }

    /**
     * get Scool Teacher by ebre_escool teacher id.
     *
     * @param $id
     * @return mixed
     */
    protected function getTeacher($id)
    {
        return User::where('email', Teacher::findOrFail($id)->email)->firstOrFail();
    }

    /**
     * Show migrating info.
     *
     * @param $model
     * @param int $level
     */
    protected function showMigratingInfo($model, $level = 0) {
        $suffix = '';
        if ($model instanceof Study) {
            $suffix = $model->multiple() ? ". <bg=yellow;options=bold>Study is multiple!</>" : "";
        }
        if ($this->verbose && $model->periods != null) $suffix .= ' ' . $model->periods->pluck('academic_periods_id');

        $this->output->info(
            str_repeat("_",$level) . 'Migrating ' . class_basename($model) . ': ' .
            $model->name . '('. $model->id . ')...' . $suffix
        );
    }

    /**
     * @param StudySubModule $srcSubmodule
     */
    public function migrateSubmodule($srcSubmodule)
    {
        $submodule = new Submodule();
        $submodule->name = $srcSubmodule->name;
        $submodule->order = $srcSubmodule->order;
        $submodule->type = $srcSubmodule->type;
        $submodule->save();
        $submodule->altnames = [
            'shortname' => $srcSubmodule->shortname,
            'description' => $srcSubmodule->description
        ];

        $module = StudyModule::findOrFail($srcSubmodule->module_id);
        Module::firstOrCreate([
            'name' => $module->name,
            'order' => $module->order,
            'study_id' => $module->study->id,
        ]);
        $submodule->addModule($module);
    }

    /**
     * Get academic periods.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function academicPeriods()
    {
        if ( ! $this->filtersAppliedToPeriods()) return AcademicPeriod::all();
        if (str_contains($this->filters[0], '-')) {
            try {
                return collect([AcademicPeriod::where(['academic_periods_name' => $this->filters[0]])->firstOrFail()]);
            } catch (\Exception $e) {
                return collect([AcademicPeriod::where(['academic_periods_shortname' => $this->filters[0]])->firstOrFail()]);
            }
        }
        if ( ! is_numeric($this->filters[0])) throw new \InvalidArgumentException();
        return collect([AcademicPeriod::findOrFail(intval($this->filters[0]))]);
    }

    /**
     * Get the teachers to migrate.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function teachers()
    {
        return $this->validateCollection(Teacher::activeOn($this->period)->get());
    }

    /**
     * Get locations to migrate.
     *
     * @return mixed
     */
    protected function locations()
    {
        return $this->validateCollection(Location::all());
    }

    /**
     * Get the departments to migrate.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function departments()
    {
        //Avoid using FOL because is a transversal department
        if ( ! $this->filtersAppliedToDepartments()) return $this->validateCollection(Department::whereNotIn('department_id', [3])->get());

        if ( ! is_numeric($this->filters[1]) )  throw new \InvalidArgumentException();
        return collect(Department::findOrFail(intval($this->filters[1])));
    }

    /**
     * Get the studies to migrate.
     *
     * @param Department $department
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function studies(Department $department) {
        return $this->validateCollection($department->studiesActiveOn($this->period)->get());
    }

    /**
     * Get the courses to migrate.
     *
     * @param Study $study
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function courses(Study $study) {
        return $this->validateCollection($study->allCourses()->active()->get());
    }

    /**
     * Get the modules to migrate.
     *
     * @param Course $course
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function modules(Course $course) {
        $modules = $course->modules()->active()->get();
        $sortedModules = $modules->sortBy(
            function ($module, $key) {
                return $module->order;
            }
        );
        return $this->validateCollection($sortedModules);
    }

    /**
     * Get the submodules to migrate.
     *
     * @param StudyModuleAcademicPeriod $module
     * @return mixed
     */
    protected function submodules(StudyModuleAcademicPeriod $module) {
        return $this->validateCollection($module->module()->first()->submodules()->active()->get());
    }

    /**
     * @param $collection
     * @return mixed
     * @throws InvalidNumberOfItemsException
     */
    protected function validateCollection($collection) {
        if (! count($collection)) {
            throw new InvalidNumberOfItemsException($collection);
        } else {
            return $collection;
        }
    }

    /**
     * @return bool
     */
    protected function filtersAppliedToDepartments()
    {
        return $this->isFilterApplied(1);
    }

    /**
     * @param $filters
     * @param $filter
     * @return bool
     */
    protected function isFilterApplied($filter)
    {
        $filters = is_array($this->filters) ? $this->filters : [$this->filters] ;
        if (!array_key_exists($filter,$filters)) return false ;
        if (strcasecmp($filters[$filter], 'all') == 0) return false;
        return true;
    }


    /**
     * Check if filters are applied to periods.
     *
     * @return bool
     */
    private function filtersAppliedToPeriods()
    {
        return $this->isFilterApplied(0);
    }

    /**
     * Print a collection.
     *
     * @param $collection
     * @param $field
     * @param string $separator
     * @return mixed
     */
    private function printCollection($collection, $field, $separator = ' | ')
    {
        return $collection->implode($field, $separator);
    }

    /**
     * Truncate scool database.
     */
    private function truncate()
    {
        try {
            Submodule::truncate();
        } catch (QueryException $qe) {}
    }

    /**
     * @return mixed
     */
    public function getDestinationConnection()
    {
        return $this->destinationConnection;
    }

    /**
     * @param mixed $destinationConnection
     */
    public function setDestinationConnection($destinationConnection)
    {
        $this->destinationConnection = $destinationConnection;
    }

    /**
     * Is period current?
     *
     * @param $period
     * @return mixed
     */
    public function isPeriodCurrent($period)
    {
        return AcademicPeriod::findOrFail($period)->current;
    }

    /**
     * Is period in process current?
     *
     * @return mixed
     */
    public function isPeriodInProcessCurrent()
    {
        return $this->isPeriodCurrent($this->period);
    }

    /**
     * Set destination connection by period id
     */
    private function setDestinationConnectionByPeriod($period)
    {
        $this->setDestinationConnection($this->composeDestinationConnectionNameByCurrentPeriodInProcess());
    }

    /**
     * Compose destination connection environment variable using current period in process.
     *
     * @return string
     */
    protected function composeDestinationConnectionEnvVarByCurrentPeriodInProcess()
    {
        return EbreEscoolMigrator::DESTINATION_CONNECTION_ENV_VAR . $this->composePeriodInProcessSuffix();
    }

    /**
     * Compose destination connection name using current period in process.
     *
     * @return string
     */
    protected function composeDestinationConnectionNameByCurrentPeriodInProcess()
    {
        return EbreEscoolMigrator::DESTINATION_CONNECTION_PREFIX . $this->composePeriodInProcessSuffix();
    }

    /**
     * Compose period suffix by period.
     *
     * @return string
     */
    protected function composePeriodSuffix($period) {
        return $this->isPeriodInProcessCurrent() ? '' : '_' . $this->getPeriodShortNameById($period);
    }

    /**
     * Compose period suffix.
     *
     * @return string
     */
    protected function composePeriodInProcessSuffix() {
        return $this->isPeriodInProcessCurrent() ? '' : '_' . $this->getCurrentPeriodInProcesShortname();
    }

    /**
     * Get current period in process shortname.
     *
     * @return mixed
     */
    private function getCurrentPeriodInProcesShortname()
    {
        return $this->getPeriodShortname($this->period);
    }

    /**
     * Get current period in process shortname.
     *
     * @return mixed
     */
    private function getPeriodShortname($period)
    {
        return $this->getPeriodShortNameById($period);
    }

    /**
     * Get period shortname by period id.
     *
     * @param $period
     * @return mixed
     */
    public function getPeriodShortNameById($period)
    {
        return AcademicPeriod::findOrFail($period)->shortname;
    }

    /**
     * Switch default connection.
     *
     * @param $connection
     * @param $env
     */
    protected function switchConnection($env,$connection)
    {
        config(['database.default' => env($env, $connection)]);
    }

    /**
     * Switch to current destination connection in process.
     *
     */
    protected function switchToDestinationConnection()
    {   $env = env(
            $this->composeDestinationConnectionEnvVarByCurrentPeriodInProcess(),
            $this->composeDestinationConnectionNameByCurrentPeriodInProcess()
        );
        $this->switchConnection($env,$this->getDestinationConnection());
    }
}