<?php

namespace Scool\EbreEscoolModel\Services;

use DB;
use Illuminate\Database\QueryException;
use Monolog\Handler\SocketHandler;
use Schema;
use Scool\Curriculum\Models\Classroom;
use Scool\Curriculum\Models\Module;
use Scool\Curriculum\Models\Submodule;
use Scool\EbreEscoolModel\AcademicPeriod;
use Scool\EbreEscoolModel\ClassroomGroup;
use Scool\EbreEscoolModel\Course;
use Scool\EbreEscoolModel\Department;
use Scool\Curriculum\Models\Department as ScoolDepartment;
use Scool\Curriculum\Models\Study as ScoolStudy;
use Scool\Curriculum\Models\Course as ScoolCourse;
use Scool\Curriculum\Models\Module as ScoolModule;
use Scool\Curriculum\Models\Submodule as ScoolSubmodule;
use Scool\EbreEscoolModel\Enrollment;
use Scool\EbreEscoolModel\Exceptions\ClassroomNotFoundByNameException;
use Scool\EbreEscoolModel\Exceptions\CourseNotFoundByNameException;
use Scool\EbreEscoolModel\Exceptions\LocationNotFoundByNameException;
use Scool\EbreEscoolModel\Exceptions\ModuleNotFoundByNameException;
use Scool\EbreEscoolModel\Exceptions\StudyNotFoundByNameException;
use Scool\EbreEscoolModel\Exceptions\SubmoduleNotFoundByNameException;
use Scool\EbreEscoolModel\Exceptions\TimeslotNotFoundByNameException;
use Scool\EbreEscoolModel\Lesson;
use Scool\EbreEscoolModel\LessonMigration;
use Scool\EbreEscoolModel\Timeslot;
use Scool\Enrollment\Models\Enrollment as ScoolEnrollment;
use Scool\Enrollment\Models\EnrollmentSubmodule as ScoolEnrollmentSubmodule;
use Scool\Timetables\Models\Day;
use Scool\Timetables\Models\Lesson as ScoolLesson;
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
use Scool\Timetables\Models\Shift;
use Scool\Timetables\Models\Timeslot as ScoolTimeSlot;

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
     * Study module is used at current migration process.
     *
     * @var StudySubModule
     */
    protected $submodule;

    /**
     * Scool department is used at current migration process.
     *
     * @var ScoolDepartment
     */
    protected $scoolDepartment;

    /**
     * Scool study is used at current migration process.
     *
     * @var ScoolStudy
     */
    protected $scoolStudy;

    /**
     * Scool course is used at current migration process.
     *
     * @var ScoolCourse
     */
    protected $scoolCourse;

    /**
     * Scool study module is used at current migration process.
     *
     * @var ScoolModule
     */
    protected $scoolModule;

    /**
     * Scool study module is used at current migration process.
     *
     * @var ScoolSubmodule
     */
    protected $scoolSubmodule;

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
     * @return ScoolStudy
     */
    public function getScoolStudy()
    {
        return $this->scoolStudy;
    }

    /**
     * @param ScoolStudy $scoolStudy
     */
    public function setScoolStudy($scoolStudy)
    {
        $this->scoolStudy = $scoolStudy;
    }

    /**
     * @return ScoolCourse
     */
    public function getScoolCourse()
    {
        return $this->scoolCourse;
    }

    /**
     * @param ScoolCourse $scoolCourse
     */
    public function setScoolCourse($scoolCourse)
    {
        $this->scoolCourse = $scoolCourse;
    }

    /**
     * @return Module
     */
    public function getScoolModule()
    {
        return $this->scoolModule;
    }

    /**
     * @param Module $scoolModule
     */
    public function setScoolModule($scoolModule)
    {
        $this->scoolModule = $scoolModule;
    }

    /**
     * @return ScoolSubmodule
     */
    public function getScoolSubmodule()
    {
        return $this->scoolSubmodule;
    }

    /**
     * @param ScoolSubmodule $scoolSubmodule
     */
    public function setScoolSubmodule($scoolSubmodule)
    {
        $this->scoolSubmodule = $scoolSubmodule;
    }

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
     * @return StudySubModule
     */
    public function getSubmodule()
    {
        return $this->submodule;
    }

    /**
     * @param StudySubModule $submodule
     */
    public function setSubmodule($submodule)
    {
        $this->submodule = $submodule;
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
     * Set verbose.
     *
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
            $this->migrateTeachers();
            $this->migrateLocations();
            $this->migrateCurriculum();
            $this->migrateClassrooms();
            $this->migrateEnrollments();
            $this->seedDays();
            $this->seedShifts();
            $this->migrateTimeslots();
            $this->migrateLessons();
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
     * Migrate ebre-escool enrollment to scool enrollment.
     *
     * @param $enrollment
     * @return null
     */
    protected function migrateEnrollment($enrollment)
    {
        $user = $this->migratePerson($enrollment->person);
        try {
            $enrollment = ScoolEnrollment::firstOrNew([
                'user_id'      => $user->id,
                'study_id'     => $this->translateStudyId($enrollment->study_id),
                'course_id'    => $this->translateCourseId($enrollment->course_id),
                'classroom_id' => $this->translateClassroomId($enrollment->group_id)
            ]);
            $enrollment->state='Validated';
            $enrollment->save();
            return $enrollment;
        } catch (\Exception $e) {
            $this->output->error(
                'Error migrating enrollment. ' . class_basename($e) . ' ' .  $e->getMessage());
            return null;
        }
    }

    /**
     * Migrate lesson.
     *
     */
    protected function migrateLesson($oldLesson)
    {
            if ($this->lessonNotExists($oldLesson)) {
                DB::beginTransaction();
              try {
                    $lesson = new ScoolLesson;
                    $lesson->location_id = $this->translateLocationId($oldLesson->location_id);
                    $lesson->day_id = $this->translateDayId($oldLesson->day);
                    $lesson->timeslot_id = $this->translateTimeslotId($oldLesson->time_slot_id);
                    $lesson->state='Validated';
                    $lesson->save();
                    $lesson->addTeacher($this->translateTeacher($oldLesson->teacher_id));
                    $module = Module::findOrFail($this->translateModuleId($oldLesson->study_module_id));
                    foreach ($module->submodules as $submodule) {
                        $lesson->addSubmodule($submodule);
                    }

                    $classroom = Classroom::findOrFail($this->translateClassroomId($oldLesson->classroom_group_id));
                    $lesson->addClassroom($classroom);

                    $lesson_migration = new LessonMigration();
                    $lesson_migration->newlesson_id = $lesson->id;
                    $lesson_migration->lesson()->associate($oldLesson);
                    $lesson_migration->save();
                    DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->output->error(
                    'Error migrating lesson. ' . class_basename($e) . ' ' .  $e->getMessage());
                return null;
            }

            }
    }

    /**
     * Check if lesson is new or already exists.
     *
     * @param $oldLesson
     * @return bool
     */
    protected function lessonNotExists($oldLesson)
    {
        if (LessonMigration::where('lesson_id',$oldLesson->id)->count() == 0) return true;
        return false;
    }

    /**
     * Migrate timeslot.
     *
     * @param $oldTimeslot
     */
    protected function migrateTimeslot($oldTimeslot)
    {
        $timeslot = ScoolTimeSlot::firstOrNew([
            'init'    => $oldTimeslot->start_time,
            'end'     => $oldTimeslot->end_time,
            'lective' => $oldTimeslot->lective,

            'order'   => $oldTimeslot->order,
        ]);
        if ( $oldTimeslot->order < 8 ) {
            $timeslot->addShiftOnlyIfNotExists(Shift::where('name', 'Matí')->first());
        }
        if ( $oldTimeslot->order > 8 )
        {
            $timeslot->addShiftOnlyIfNotExists(Shift::where('name','Tarda')->first());
        }

        $timeslot->save();
    }

    /**
     * Migrate ebre-escool enrollment detail to scool enrollment.
     *
     * @param $enrollmentDetail
     * @param $enrollment_id
     */
    protected function migrateEnrollmentDetail($enrollmentDetail,$enrollment_id)
    {
        try {
            $enrollment = ScoolEnrollmentSubmodule::firstOrNew([
                'enrollment_id' => $enrollment_id,
                'module_id'     => $this->translateModuleId($enrollmentDetail->moduleid),
                'submodule_id'  => $this->translateSubmoduleId($enrollmentDetail->submoduleid),
            ]);
            $enrollment->state='Validated';
            $enrollment->save();
        } catch (\Exception $e) {
            $this->output->error(
                'Error migrating enrollment detail. ' . class_basename($e) . ' ' .  $e->getMessage());
        }
    }

    /**
     * Translate from ebre_escool teacher id to scool user id.
     *
     * @param $id
     * @return mixed
     */
    protected function translateTeacher($id)
    {
        return User::where('email', Teacher::findOrFail($id)->email)->firstOrFail();
    }

    /**
     * Translate location id.
     *
     * @param $oldLocationId
     * @return mixed
     * @throws LocationNotFoundByNameException
     */
    protected function translateLocationId($oldLocationId)
    {
        $location = ScoolLocation::where('name',Location::findOrFail($oldLocationId)->name)->first();
        if ( $location != null ) {
            return $location->id;
        }
        throw new LocationNotFoundByNameException();
    }

    /**
     * Translate day id.
     *
     * @param $oldDayId
     * @return mixed
     */
    protected function translateDayId($oldDayId)
    {
        switch ($oldDayId) {
            case 0:
                return 0;
            case 1:
                return 1;
            case 2:
                return 2;
            case 3:
                return 3;
            case 4:
                return 4;
            case 5:
                return 5;
            case 6:
                return 6;
            case 7:
                return 7;
        }
    }

    /**
     * Translate timeslot id.
     *
     * @param $oldTimeslotId
     * @return mixed
     * @throws TimeslotNotFoundByNameException
     */
    protected function translateTimeslotId($oldTimeslotId)
    {
        $timeslot = ScoolTimeslot::where('order',Timeslot::findOrFail($oldTimeslotId)->order)->first();
        if ( $timeslot != null ) {
            return $timeslot->id;
        }
        throw new TimeslotNotFoundByNameException();
    }

    /**
     * Translate module id.
     *
     * @param $oldModuleId
     * @return
     * @throws ModuleNotFoundByNameException
     */
    protected function translateModuleId($oldModuleId)
    {
        $module = Module::where('name',StudyModule::findOrFail($oldModuleId)->name)->first();
        if ( $module != null ) {
            return $module->id;
        }
        throw new ModuleNotFoundByNameException();
    }

    /**
     * Translate submodule id.
     *
     * @param $oldSubModuleId
     * @return
     * @throws SubmoduleNotFoundByNameException
     */
    protected function translateSubmoduleId($oldSubModuleId)
    {
        $submodule = Submodule::where('name',StudySubModule::findOrFail($oldSubModuleId)->name)->first();
        if ( $submodule != null ) {
            return $submodule->id;
        }
        throw new SubmoduleNotFoundByNameException();
    }


    /**
     * Translate old ebre-escool study id to scool id.
     *
     * @param $oldStudyId
     * @return mixed
     * @throws StudyNotFoundByNameException
     */
    protected function translateStudyId($oldStudyId)
    {
        $study = ScoolStudy::where('name',Study::findOrFail($oldStudyId)->name)->first();
        if ( $study != null ) {
            return $study->id;
        }
        throw new StudyNotFoundByNameException();
    }

    /**
     * Translate old ebre-escool course id to scool id.
     *
     * @param $oldCourseId
     * @return mixed
     * @throws CourseNotFoundByNameException
     */
    protected function translateCourseId($oldCourseId)
    {
        $course = ScoolCourse::where('name',Course::findOrFail($oldCourseId)->name)->first();
        if ( $course != null ) {
            return $course->id;
        }
        throw new CourseNotFoundByNameException();
    }

    /**
     * Translate old ebre-escool classroom id to scool id.
     *
     * @param $oldClassroomId
     * @return integer
     * @throws ClassroomNotFoundByNameException
     */
    protected function translateClassroomId($oldClassroomId)
    {
        $classroom = Classroom::where('name',ClassroomGroup::findOrFail($oldClassroomId)->name)->first();
        if ( $classroom != null ) {
            return $classroom->id;
        }
        throw new ClassroomNotFoundByNameException();
    }

    /**
     * Migrate ebre-escool person to scool person.
     *
     * @param $person
     */
    protected function migratePerson($person)
    {
        //TODO create person in personal data table
        $user = User::firstOrNew([
            'email' => $person->email,
        ]);
        $user->name = $person->name;
        $user->password = bcrypt('secret');
        $user->remember_token = str_random(10);
        $user->save();
        return $user;
    }

    /**
     * Migrate classrooms.
     */
    protected function migrateClassrooms()
    {
        $this->output->info('### Migrating classrooms ###');
        foreach ($classrooms = $this->classrooms() as $classroom) {
            $this->showMigratingInfo($classroom, 1);
            $this->migrateClassroom($classroom);
        }
        $this->output->info(
            '### END Migrating classrooms. Migrated ' . count($classrooms) .  ' locations   ###');
    }

    /**
     * Migrate locations.
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
     * Migrate location.
     *
     * @param $srcLocation
     */
    protected function migrateLocation($srcLocation) {
        $location = ScoolLocation::firstOrNew([
            'name' => $srcLocation->name,
        ]);
        $location->save();
        $location->shortname = $srcLocation->shortName;
        $location->description = $srcLocation->description;
        $location->code = $srcLocation->external_code;
    }

    /**
     * Migrate classroom.
     *
     * @param $srcClassroom
     */
    protected function migrateClassroom($srcClassroom) {
        $classroom = Classroom::firstOrNew([
            'name' => $srcClassroom->name,
        ]);
        $classroom->save();
        $this->addCourseToClassroom($classroom, $srcClassroom->course_id);
        $classroom->shortname = $srcClassroom->shortName;
        $classroom->description = $srcClassroom->description;
        $classroom->code = $srcClassroom->external_code;
    }



    /**
     * Migrate curriculum.
     */
    private function migrateCurriculum()
    {
        foreach ($this->departments() as $department) {
            $this->setDepartment($department);
            $this->showMigratingInfo($department,1);
            $this->migrateDepartment($department);
            foreach ($this->studies($department) as $study) {
                $this->setStudy($study);
                $this->showMigratingInfo($study,2);
                $this->migrateStudy($study);
                $this->addStudyToDeparment();
                foreach ($this->courses($study) as $course) {
                    $this->setCourse($course);
                    $this->showMigratingInfo($course,3);
                    $this->migrateCourse($course);
                    $this->addCourseToStudy();
                    foreach ($this->modules($course) as $module) {
                        $this->setModule($module);
                        $this->showMigratingInfo($module, 4);
                        $this->migrateModule($module);
                        $this->addModuleToCourse();
                        foreach ($this->submodules($module) as $submodule) {
                            $this->setSubmodule($submodule);
                            $this->showMigratingInfo($submodule, 5);
                            $this->migrateSubmodule($submodule);
                            $this->addSubModuleToModule();
                        }
                    }
                }
            }
        }
    }

    /**
     * Migrate enrollment.
     */
    private function migrateEnrollments()
    {
        $this->output->info('### Migrating enrollments ###');
        foreach ($this->enrollments() as $enrollment) {
            $enrollment->showMigratingInfo($this->output,1);
            $newEnrollment = $this->migrateEnrollment($enrollment);
            if ($newEnrollment) $this->migrateEnrollmentDetails($enrollment, $newEnrollment);
        }
        $this->output->info('### END Migrating enrollments ###');
    }

    /**
     * Seed days of the week with ISO-8601 numeric code.
     */
    protected function seedDays()
    {
        $this->output->info('### Seeding days###');
        //%u	ISO-8601 numeric representation of the day of the week	1 (for Monday) through 7 (for Sunday)
        $timestamp = strtotime('next Monday');
        $days = array();
        for ($i = 1; $i < 8; $i++) {
            $days[$i] = strftime('%A', $timestamp);
            $timestamp = strtotime('+1 day', $timestamp);
        }

        foreach ($days as $dayNumber => $day) {
            $this->output->info('### Seeding day: ' . $day . ' ###');
            $dayModel = Day::firstOrNew([
                'code' => $dayNumber,
                ]
            );
            $dayModel->name = $day;
            $dayModel->code = $dayNumber;
            $dayModel->lective = true;
            if ($dayNumber == 6 || $dayNumber == 7) {
                $dayModel->lective = false;
            }
            $dayModel->save();
        }
        $this->output->info('### END Seeding days###');
    }

    /**
     * Seed shifts.
     */
    protected function seedShifts()
    {
        $this->output->info('### Seeding shifts###');
        $this->output->info('Adding Matí shift...');
        $shift = Shift::firstOrNew([
                'name' => 'Matí'
            ]
        );
        $shift->save();
        $this->output->info('Adding Tarda shift...');
        $shift = Shift::firstOrNew([
                'name' => 'Tarda'
            ]
        );
        $shift->save();
        $this->output->info('### END Seeding shifts###');
    }

    /**
     * Migrate timeslots.
     */
    protected function migrateTimeslots()
    {
        $this->output->info('### Migrating timeslots ###');
        foreach ($this->timeslots() as $timeslot) {
            $timeslot->showMigratingInfo($this->output,1);
            $this->migrateTimeslot($timeslot);
        }
        $this->output->info('### END OF Migrating timeslots ###');
    }

    /**
     * Migrate lessons.
     */
    protected function migrateLessons()
    {
        $this->output->info('### Migrating lessons ###');
        if ($this->checkLessonsMigrationState()) {
            foreach ($this->lessons() as $lesson) {
                $lesson->showMigratingInfo($this->output,1);
                $this->migrateLesson($lesson);
            }
        }
        $this->output->info('### END OF Migrating lessons ###');
    }

    /**
     * Check state of lessons migration.
     *
     * @return bool
     */
    protected function checkLessonsMigrationState()
    {
        $this->output->info('# Checkin lessons migration state... #');

        switch ($this->checkLessonMigrationStats()) {
            case 0:
                return true;
            case 1:
                $this->output->error(' Migration stats does not match!');
                if ($this->output->confirm('Do you wish to continue (lesson_migration and scool lesson tables will be truncated)?')) {
                    DB::connection('ebre_escool')->statement('DELETE FROM lesson_migration');
                    DB::connection('ebre_escool')->statement('ALTER TABLE lesson_migration AUTO_INCREMENT = 1');
                    DB::statement('DELETE FROM lessons');
                    DB::statement('ALTER TABLE lessons AUTO_INCREMENT = 1');
                    DB::statement('DELETE FROM lesson_user');
                    DB::statement('ALTER TABLE lesson_user AUTO_INCREMENT = 1');
                    DB::statement('DELETE FROM lesson_submodule');
                    DB::statement('ALTER TABLE lesson_submodule AUTO_INCREMENT = 1');
                    DB::statement('DELETE FROM classroom_submodule');
                    DB::statement('ALTER TABLE classroom_submodule AUTO_INCREMENT = 1');
                } else {
                    die();
                }
                return true;
            case 2:
                $this->output->info(' Lesson data seems already migrated. Skipping lessons migration...');
                return false;
        }
    }

    /**
     * Check lesson migrations stats.
     *
     * @return bool
     */
    protected function checkLessonMigrationStats()
    {
        $numberOfOriginalLessons = Lesson::activeOn($this->period)->count();
        $numberOfTrackedLessons = LessonMigration::all()->count();
        $numberOfMigratedLessons = ScoolLesson::all()->count();
        $this->output->info(' Original lessons: ' . $numberOfOriginalLessons);
        $this->output->info(' Tracked migrated lessons (table lesson_migration): ' . $numberOfTrackedLessons );
        $this->output->info(' Already migrated lessons: ' . $numberOfMigratedLessons);
        if ($numberOfTrackedLessons == 0 && $numberOfMigratedLessons == 0) return 0;
        if ( $numberOfOriginalLessons != $numberOfTrackedLessons ||
             $numberOfTrackedLessons != $numberOfMigratedLessons) return 1;
        if ( $numberOfOriginalLessons == $numberOfTrackedLessons ||
            $numberOfTrackedLessons == $numberOfMigratedLessons) return 2;
    }

    /**
     * Migrate enrollment details (enrollment modules/submodules).
     *
     * @param $oldEnrollment
     * @param $newEnrollment
     * @internal param $enrollment
     */
    protected function migrateEnrollmentDetails($oldEnrollment,$newEnrollment)
    {
        foreach ($this->enrollmentDetails($oldEnrollment) as $enrollmentDetail) {
            $enrollmentDetail->showMigratingInfo($this->output,2);
            $this->migrateEnrollmentDetail($enrollmentDetail,$newEnrollment->id);
        }
    }

    /**
     * Obtain all ebre_escool_enrollments
     */
    private function enrollments()
    {
        return $this->validateCollection(
            Enrollment::activeOn(
                AcademicPeriod::findOrFail($this->period)->shortname)
            )->orderBy(
                'enrollment_study_id',
                'enrollment_course_id',
                'enrollment_group_id')->get();
    }

    /**
     * Obtain all ebre_escool lessons to migrate.
     */
    protected function lessons()
    {
        return $this->validateCollection(
            Lesson::activeOn($this->period)
        )->orderBy(
            'lesson_classroom_group_id',
            'lesson_teacher_id',
            'lesson_study_module_id'
            )->get();
    }

    /**
     * Obtain all timeslots to migrate.
     *
     * @return mixed
     */
    protected function timeslots()
    {
        return $this->validateCollection(
            Timeslot::all()
        );
    }

    /**
     * Get enrollment details.
     *
     * @param $enrollment
     * @return mixed
     */
    protected function enrollmentDetails($enrollment)
    {
        return $this->validateCollection(
            $enrollment->details
        );
    }


    /**
     * Migrate department.
     *
     * @param Department $department
     */
    protected function migrateDepartment(Department $department) {

        $this->setScoolDepartment(
            $this->createDepartment($department)
        );
    }

    /**
     * Migrate study.
     *
     * @param Study $study
     */
    protected function migrateStudy(Study $study)
    {
        $this->setScoolStudy(
            $this->createStudy($study)
        );
    }

    /**
     * Migrate course.
     *
     * @param Course $course
     */
    protected function migrateCourse(Course $course)
    {
        $this->setScoolCourse(
            $this->createCourse($course)
        );
    }

    /**
     * Migrate module.
     *
     * @param StudyModuleAcademicPeriod $module
     */
    public function migrateModule(StudyModuleAcademicPeriod $module)
    {
        try {
            $this->setScoolModule(
                $this->createModule($module)
            );
        } catch (\LogicException $le) {
            $this->output->error($le->getMessage());
        }

    }

    /**
     * Migrate submodule.
     *
     * @param $srcSubmodule
     */
    public function migrateSubmodule($srcSubmodule)
    {
        $this->setScoolSubmodule(
            $this->createSubModule($srcSubmodule)
        );
    }

    /**
     * Create scool study using ebre-escool study.
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
        $department->head = $this->translateTeacher($srcDepartment->head);

        return $department;

    }

    /**
     * Create scool study using ebre-escool study.
     * 
     * @param Study $srcStudy
     * @return mixed
     */
    protected function createStudy(Study $srcStudy)
    {
         $study = ScoolStudy::firstOrNew([
            'name'         => $srcStudy->name,
        ]);
        $study->law_id = $this->mapLaws($srcStudy->studies_law_id);
        $study->save();
        $study->shortname = $srcStudy->shortname;
        $study->description = $srcStudy->description;

        return $study;
    }

    /**
     * Create scool course using ebre-escool course.
     *
     * @param Course $srcCourse
     * @return mixed
     */
    protected function createCourse(Course $srcCourse)
    {
        $course = ScoolCourse::firstOrNew([
            'name'         => $srcCourse->name,
        ]);
        $course->save();
        $course->shortname = $srcCourse->shortname;
        $course->description = $srcCourse->description;

        return $course;
    }

    /**
     * Create scool study module using ebre-escool study module.
     *
     * @param StudyModuleAcademicPeriod $studyModule
     * @return mixed
     * @throws \Exception
     */
    protected function createModule(StudyModuleAcademicPeriod $studyModule)
    {
        $module = ScoolModule::firstOrNew([
            'name'         => $studyModule->name,
            'study_id'     => $this->getScoolStudy()->id
        ]);
        if($studyModule->study_shortname != $this->getScoolStudy()->shortname) {
            throw new \LogicException(
                'study_shortname in Ebre-escool study module (' . $studyModule->study_shortname
                . ") doesn't match study shortname (" . $this->getScoolStudy()->shortname . ')');
        }
        $module->save();
        $module->order = $studyModule->order;
        $module->shortname = $studyModule->shortname;
        $module->description = $studyModule->description;
        return $module;
    }

    /**
     * Create scool study submodule using ebre-escool study submodule.
     *
     * @param StudySubModule $srcSubmodule
     * @return ScoolSubmodule
     */
    protected function createSubmodule(StudySubModule $srcSubmodule)
    {
        //First Or new
        $id = $this->SubModuleAlreadyExists($srcSubmodule);
        if ($id != null) {
            $submodule = Submodule::findOrFail($id);
        } else {
            $submodule = new Submodule();
        }

        $submodule->name = $srcSubmodule->name;
        $submodule->order = $srcSubmodule->order;
        $submodule->type = $this->mapTypes($srcSubmodule->type->id);
        $submodule->save();
        $submodule->altnames = [
            'shortname'   => $srcSubmodule->shortname,
            'description' => $srcSubmodule->description
        ];

        $submodule->addModule($this->getScoolModule());
        return $submodule;
    }

    /**
     * Check if submodule already exists and return id if exists (or null if not).
     * @param $srcSubmodule
     * @return null
     */
    protected function SubModuleAlreadyExists($srcSubmodule)
    {
        $module = $this->getScoolModule();
        foreach ($module->submodules as $submodule) {
            if ($submodule->name == $srcSubmodule->name ) return $submodule->id;
        }
        return null;
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
     * Map ebre_escool law to scool law
     *
     * @param $law
     * @return int
     */
    protected function mapLaws($law)
    {
        switch ($law) {
            case 1:
                return 1;
            case 2:
                return 2;
        }
        throw new \InvalidArgumentException();
    }

    /**
     * Map ebre_escool study module types to scool types
     *
     * @param $type
     * @return int
     */
    protected function mapTypes($type)
    {
        switch ($type) {
            case 1:
                return 1;
            case 2:
                return 2;
            case 3:
                return 3;
            case 4:
                return 4;
        }
        throw new \InvalidArgumentException();
    }

    /**
     * Get academic periods.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
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
     * @return mixed
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
     * Get classrooms to migrate.
     *
     * @return mixed
     */
    protected function classrooms()
    {
        return $this->validateCollection(ClassroomGroup::all());
    }

    /**
     * Get the departments to migrate.
     *
     * @return \Illuminate\Support\Collection|mixed
     */
    protected function departments()
    {
        //Avoid using FOL because is a transversal department
        if ( ! $this->filtersAppliedToDepartments()) return $this->validateCollection(Department::whereNotIn('department_id', [3])->get());
        if ( ! is_numeric($this->filters[1]) )  throw new \InvalidArgumentException();
        return collect([Department::findOrFail(intval($this->filters[1]))]);
    }

    /**
     * Get the studies to migrate.
     *
     * @param Department $department
     * @return mixed
     */
    protected function studies(Department $department) {
        return $this->validateCollection($department->studiesActiveOn($this->period)->get());
    }

    /**
     * Get the courses to migrate.
     *
     * @param Study $study
     * @return mixed
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

    /**
     * Add current study to current department.
     */
    protected function addStudyToDeparment()
    {
        $this->getScoolDepartment()->studies()
             ->syncWithoutDetaching([$this->getScoolStudy()->id]);
    }

    /**
     * Add current course to current study.
     */
    protected function addCourseToStudy()
    {
        $this->getScoolStudy()->courses()
             ->syncWithoutDetaching([$this->getScoolCourse()->id]);
    }

    /**
     * Add current module to current course.
     */
    protected function addModuleToCourse()
    {
        $this->getScoolCourse()->modules()
            ->syncWithoutDetaching([$this->getScoolModule()->id]);
    }

    /**
     * Add current submodule to current module.
     */
    protected function addSubModuleToModule()
    {
        $this->getScoolModule()->submodules()
             ->syncWithoutDetaching([$this->getScoolSubmodule()->id]);
    }

    /**
     * Add course to classroom.
     *
     * @param $classroom
     * @param $course_id
     */
    protected function addCourseToClassroom($classroom, $course_id)
    {
        $classroom->courses()
            ->syncWithoutDetaching([$course_id]);
    }

}