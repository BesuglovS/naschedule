<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\DomainClasses\Auditorium;
use App\DomainClasses\Building;
use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Discipline;
use App\DomainClasses\Exam;
use App\DomainClasses\Faculty;
use App\DomainClasses\Lesson;
use App\DomainClasses\LessonLogEvent;
use App\DomainClasses\Ring;
use App\DomainClasses\Student;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use Illuminate\Support\Facades\DB;


class OldApiController extends Controller
{
    public function index() {
        return view('api.index');
    }

    public function api(Request $request) {
        ini_set('max_execution_time', 60);

        $input = $request->all();

        $action = $input["action"];

        $allowed_actions = array("list", "groupsBundle", "bundle", "update", "dailySchedule", "groupExams",
            "weekSchedule", "weeksSchedule", "groupSchedule", "TeacherWeekSchedule", "TeacherSchedule",
            "disciplineLessons", "LastLessons", "teacherLessons", "teacherWeeksSchedule", "teacherExams",
            "facultyWeeksSchedule", "buildingEvents", "freeAuditoriums");

        if (!in_array($action, $allowed_actions))
        {
            return array("error" => "Неизвестное действие (action)");
        }

        switch ($action) {
            case "update":
            case "bundle":
                $bundle = array();
                $bundle["auditoriums"] =  $this->GetAuditoriumsList();
                $bundle["calendars"] = $this->GetCalendarsList();
                $bundle["disciplines"] = $this->GetDisciplinesList();
                $bundle["lessons"] = $this->GetLessonsList();
                $bundle["rings"] = $this->GetRingsList();
                $bundle["students"] = $this->GetStudentsList();
                $bundle["studentGroups"] = $this->GetStudentGroupsList();
                $bundle["studentsInGroups"] = $this->GetStudentInGroupsList();
                $bundle["teachers"] = $this->GetTechersList();
                $bundle["teacherForDisciplines"] = $this->GetTFDList();

                $bundle["configOptions"] = $this->GetConfigOptionsList();
                $bundle["lessonLogEvents"] = $this->GetLogEvents();

                $bundle["faculties"] = $this->GetFacultiesList();
                $bundle["groupsInFaculties"] = $this->GetGroupInFaculties();

                return $bundle;
                break;
            case "groupsBundle":
                $bundle = array();
                $bundle["studentGroups"] = $this->GetStudentGroupsList();
                $bundle["faculties"] = $this->GetFacultiesList();
                $bundle["groupsInFaculties"] = $this->GetGroupInFaculties();

                return $bundle;
                break;
            case "list":
                if(!isset($input['listtype']))
                {
                    return array("error" => "listtype - обязательный параметр при list запросе.");
                }
                else
                {
                    $listType = $input["listtype"];
                }

                switch ($listType) {
                    case "auditoriums":             return $this->GetAuditoriumsList();
                    case "calendars":               return $this->GetCalendarsList();
                    case "configOptions":           return $this->GetConfigOptionsList();
                    case "disciplines":             return $this->GetDisciplinesList($input);
                    case "lessons":                 return $this->GetLessonsList();
                    case "rings":                   return $this->GetRingsList();
                    case "students":                return $this->GetStudentsList();
                    case "studentGroups":           return $this->GetStudentGroupsList();
                    case "mainStudentGroups":       return $this->GetMainStudentGroupsList();
                    case "studentsInGroups":        return $this->GetStudentInGroupsList();
                    case "teachers":                return $this->GetTechersList();
                    case "teacherForDisciplines":   return $this->GetTFDList();
                    case "lessonLogEvents":         return $this->GetLogEvents();
                    case "faculties":               return $this->GetFacultiesList();
                    case "buildings":               return $this->GetBuildingsList();
                    case "groupsInFaculties":       return $this->GetGroupInFaculties();
                    case "tfdListExpanded":         return $this->GetTfdListExpanded($input);
                    case "groupDisciplines":        return $this->GetGroupDisciplinesList($input);
                }
                break;
            case "dailySchedule":           return $this->DailySchedule($input);
            case "weekSchedule":            return $this->WeekSchedule($input);
            case "groupExams":              return $this->GroupExams($input);
            case "TeacherWeekSchedule":     return $this->TeacherWeekSchedule($input);
            case "disciplineLessons":       return $this->DisciplineLessons($input);
            case "weeksSchedule":           return $this->GetWeeksSchedule($input);
            case "LastLessons":             return $this->LastLessons($input);
            case "teacherLessons":          return $this->GetTeacherLessons($input);
            case "teacherWeeksSchedule":    return $this->GetTeacherWeeksSchedule($input);
            case "teacherExams":            return $this->GetTeacherExams($input);
            case "facultyWeeksSchedule":    return $this->GetFacultyWeeksSchedule($input);
            case "buildingEvents":          return $this->GetBuildingEvents($input);
            case "freeAuditoriums":         return $this->GetFreeAuditoriums($input);
        }

        return array("error" => "Whoops, looks like something went wrong :-)");
    }

    private function DailySchedule($input) {
        if (((!isset($input['groupId'])) && (!isset($input['groupIds']))) || (!isset($input['date'])))
        {
            return array("error" => "groupId или groupIds и date (yyyy-mm-dd) обязательные параметры");
        }

        if (isset($input['groupId']))
        {
            $groupId = $input["groupId"];
        }
        if (isset($input['groupIds']))
        {
            $groupId = $input['groupIds'];
        }

        $date = $input["date"];

        $groupDisciplineIds = Discipline::IdsFromGroupId($groupId);
        $disciplineTeacherIds = Discipline::TDIdsFromDisciplineIds($groupDisciplineIds);
        $calendarId = Calendar::IdfromDate($date);

        $lessonsList =  Lesson::GetDailyTFDLessons($disciplineTeacherIds, $calendarId);

        $lessonsList->map(function ($lesson) use ($date) {
            $dt = $date . " " . $lesson->time;
            $now = Carbon::now();
            $lessonStart = Carbon::createFromFormat('Y-m-d H:i', $dt);
            $diff = $now->diffInMinutes($lessonStart);
            $onGoing = (($diff >= 0) && ($diff < 80)) ? 1 : 0;

            $lesson->onGoing = $onGoing;

            $lesson->Time = substr($lesson->time, 0, 5);
            unset($lesson->time);

            $lesson->discName = $lesson->disc_name;
            unset($lesson->disc_name);

            $lesson->FIO = $lesson->fio;
            unset($lesson->fio);

            $lesson->audName = $lesson->aud_name;
            unset($lesson->aud_name);

            $lesson->groupName = $lesson->group_name;
            unset($lesson->group_name);

            unset($lesson->id);
        });

        $resultItem = array();
        $resultItem["studentGroupName"] = StudentGroup::NameFromId($groupId);
        $resultItem["studentGroupId"] = $groupId;
        $resultItem["Lessons"] = $lessonsList;

        $result = array($resultItem);

        return $result;
    }

    private function WeekSchedule($input) {
        if ((!isset($input['groupId'])) || (!isset($input['week'])))
        {
            return array("error" => "groupId и week обязательные параметры");
        }

        $groupId = $input["groupId"];
        $week = $input["week"];

        $groupDisciplineIds = Discipline::IdsFromGroupId($groupId);
        $disciplineTeacherIds = Discipline::TDIdsFromDisciplineIds($groupDisciplineIds);
        $lessons = Lesson::GetWeekTFDLessons($disciplineTeacherIds, $week);

        $lessons->map(function ($lesson) {
            $dt = Carbon::createFromFormat('Y-m-d', $lesson->date);
            $dow = $dt->dayOfWeek;
            $dow = ($dow == 0) ? 7 : $dow;

            $lesson->Time = $lesson->time;
            unset($lesson->time);

            $lesson->discName = $lesson->disc_name;
            unset($lesson->disc_name);

            $lesson->FIO = $lesson->fio;
            unset($lesson->fio);

            $lesson->audName = $lesson->aud_name;
            unset($lesson->aud_name);

            $lesson->groupName = $lesson->group_name;
            unset($lesson->group_name);

            unset($lesson->id);

            $lesson->dow = (string) $dow;
        });

        return $lessons;
    }

    private function GroupExams($input) {
        if (!isset($input['groupId']))
        {
            return array("error" => "groupId обязательный параметр");
        }

        $groupId = $input["groupId"];

        $Exams = Exam::FromGroupId_OldAPI($groupId);

        $Exams->map(function ($exam) {
            $default_datetime = "01.01.2020 0:00";

            $exam->DisciplineId = $exam->discipline_id;
            unset($exam->discipline_id);

            $exam->ConsultationDateTime =
                ($exam->consultation_datetime == $default_datetime) ? "" :
                    $exam->consultation_datetime;
            unset($exam->consultation_datetime);

            $exam->ConsultationAuditoriumId = ($exam->cons_aud_id == null) ? "" : $exam->cons_aud_id;
            unset($exam->cons_aud_id);

            $exam->ExamDateTime =
                ($exam->exam_datetime == $default_datetime) ? "" :
                    $exam->exam_datetime;
            unset($exam->exam_datetime);

            $exam->ExamAuditoriumId = ($exam->exam_aud_id == null) ? "" : $exam->exam_aud_id;
            unset($exam->exam_aud_id);

            $exam->ConsultationAuditoriumName = ($exam->cons_aud == null) ? "" : $exam->cons_aud;
            unset($exam->cons_aud);

            $exam->ExamAuditoriumName = ($exam->exam_aud == null) ? "" : $exam->exam_aud;
            unset($exam->exam_aud);

            $exam->DisciplineName = $exam->disc_name;
            unset($exam->disc_name);

            $exam->TeacherFIO = $exam->teacher_fio;
            unset($exam->teacher_fio);

            $exam->StudentGroupName = $exam->group_name;
            unset($exam->group_name);

            //unset($exam->id);
        });

        $result = array();
        $result[$groupId] = array();
        $result[$groupId]["Exams"] = $Exams;
        $result[$groupId]["groupId"] = (string) $groupId;
        $result[$groupId]["groupName"] = StudentGroup::NameFromId($groupId);

        return $result;
    }

    private function TeacherWeekSchedule($input) {
        if ((!isset($input['teacherId'])) || (!isset($input['week'])))
        {
            return array("error" => "teacherId и week обязательные параметры");
        }

        $teacherId = $input["teacherId"];
        $week = $input["week"];

        $tfdIds = Discipline::IdsFromTeacherId($teacherId);
        $lessons = Lesson::GetWeekTFDLessons_OldAPI($tfdIds, $week);

        $lessons->map(function ($lesson) {
            $dt = Carbon::createFromFormat('Y-m-d', $lesson->date);
            $dow = $dt->dayOfWeek;
            $dow = ($dow == 0) ? 7 : $dow;

            $lesson->Date = $lesson->date;
            unset($lesson->date);

            $lesson->Time = $lesson->time;
            unset($lesson->time);

            $lesson->groupName = $lesson->group_name;
            unset($lesson->group_name);

            $lesson->disciplineName = $lesson->disc_name;
            unset($lesson->disc_name);

            $lesson->auditoriumName = $lesson->aud_name;
            unset($lesson->aud_name);

            $lesson->TeacherForDisciplineId = (string) $lesson->tfd_id;
            unset($lesson->tfd_id);

            unset($lesson->id);
            unset($lesson->fio);

            $lesson->dow = $dow;
        });

        return $lessons;
    }

    /**
     * @return Collection|static[]
     */
    private function GetAuditoriumsList()
    {
        $result = Auditorium::all();

        $result->map(function ($aud) {
            $aud->AuditoriumId = (string) $aud->id;
            unset($aud->id);

            $aud->Name = $aud->name;
            unset($aud->name);

            unset($aud->building_id);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetCalendarsList()
    {
        $result = Calendar::all();

        $result->map(function ($cal) {
            $cal->CalendarId = (string) $cal->id;
            unset($cal->id);

            $cal->Date = $cal->date;
            unset($cal->date);

            unset($cal->state);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetConfigOptionsList()
    {
        $result =  ConfigOption::all();

        $result->map(function ($co) {
            $co->ConfigOptionId = (string) $co->id;
            unset($co->id);

            $co->Key = $co->key;
            unset($co->key);

            $co->Value = $co->value;
            unset($co->value);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetDisciplinesList($input)
    {
        if (isset($input['teacherId']))
        {
            $teacherId = $input['teacherId'];

            $result = DB::table('disciplines')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
                ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->where('discipline_teacher.teacher_id', '=', $teacherId)
                ->select('disciplines.id', 'disciplines.name', 'disciplines.attestation',
                    'disciplines.auditorium_hours', 'disciplines.lecture_hours', 'disciplines.practical_hours',
                    'disciplines.student_group_id', 'disciplines.auditorium_hours_per_week', 'student_groups.name as groupName',
                    'teachers.id as teacherId', 'teachers.fio as teacherFio')
                ->get();
        }
        else
        {
            if (isset($input['groupId']))
            {
                $groupId = $input['groupId'];

                $groupIds = StudentGroup::GetGroupsOfStudentFromGroup($groupId);

                $result = DB::table('disciplines')
                    ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                    ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
                    ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                    ->whereIn('disciplines.student_group_id', $groupIds)
                    ->select('disciplines.id', 'disciplines.name', 'disciplines.attestation',
                        'disciplines.auditorium_hours', 'disciplines.lecture_hours', 'disciplines.practical_hours',
                        'disciplines.student_group_id', 'disciplines.auditorium_hours_per_week', 'student_groups.name as groupName',
                        'teachers.id as teacherId', 'teachers.fio as teacherFio')
                    ->get();
            }
            else {
                $result = Discipline::all();
            }
        }

        $result->map(function ($disc) {
            $disc->DisciplineId = (string) $disc->id;
            unset($disc->id);

            $disc->Name = $disc->name;
            unset($disc->name);

            $disc->Attestation = (string) $disc->attestation;
            unset($disc->attestation);

            $disc->AuditoriumHours = (string) $disc->auditorium_hours;
            unset($disc->auditorium_hours);

            $disc->AuditoriumHoursPerWeek = (string) $disc->auditorium_hours_per_week;
            unset($disc->auditorium_hours_per_week);

            $disc->LectureHours = (string) $disc->lecture_hours;
            unset($disc->lecture_hours);

            $disc->PracticalHours = (string) $disc->practical_hours;
            unset($disc->practical_hours);

            $disc->StudentGroupId = (string) $disc->student_group_id;
            unset($disc->student_group_id);

            unset($disc->auditorium_hours_per_week);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetLessonsList()
    {
        $result =  Lesson::all();

        $result->map(function ($l) {
            $l->LessonId = (string) $l->id;
            unset($l->id);

            $l->IsActive = (string) $l->state;
            unset($l->state);

            $l->TeacherForDisciplineId = (string) $l->discipline_teacher_id;
            unset($l->discipline_teacher_id);

            $l->CalendarId = (string) $l->calendar_id;
            unset($l->calendar_id);

            $l->RingId = (string) $l->ring_id;
            unset($l->ring_id);

            $l->AuditoriumId = (string) $l->auditorium_id;
            unset($l->auditorium_id);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetRingsList()
    {
        $result =  Ring::all();

        $result->map(function ($ring) {
            $ring->RingId = (string) $ring->id;
            unset($ring->id);

            $ring->Time = $ring->time;
            unset($ring->time);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetStudentsList()
    {
        $result =  Student::ListOfNotExpelled();

        $result->map(function ($st) {
            $st->StudentId = (string) $st->id;
            unset($st->id);

            $st->F = $st->f;
            unset($st->f);

            $st->I = $st->i;
            unset($st->i);

            $st->O = $st->o;
            unset($st->o);

            $st->Starosta = (string) $st->starosta;
            unset($st->starosta);

            $st->NFactor = (string) $st->n_factor;
            unset($st->n_factor);

            $st->Expelled = (string) $st->expelled;
            unset($st->expelled);

            unset($st->zach_number);
            unset($st->birth_date);
            unset($st->address);
            unset($st->phone);
            unset($st->orders);
            unset($st->paid_edu);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetStudentGroupsList()
    {
        $result = StudentGroup::all();

        $result->map(function ($sg) {
            $sg->StudentGroupId = (string) $sg->id;
            unset($sg->id);

            $sg->Name = $sg->name;
            unset($sg->name);
        });

        return $result;
    }

    /**
     * @return array
     */
    private function GetMainStudentGroupsList()
    {
        $result = StudentGroup::mainStudentGroups();

        $result->map(function ($sg) {
            $sg->StudentGroupId = (string) $sg->id;
            unset($sg->id);

            $sg->Name = $sg->name;
            unset($sg->name);
        });

        return $result;
    }

    /**
     * @return \Illuminate\Support\Collection|static[]
     */
    private function GetStudentInGroupsList()
    {
        $result = DB::table('student_student_group')->get();

        $result->map(function ($sig) {
            $sig->StudentsInGroupsId = (string) $sig->id;
            unset($sig->id);

            $sig->StudentId = (string) $sig->student_id;
            unset($sig->student_id);

            $sig->StudentGroupId = (string) $sig->student_group_id;
            unset($sig->student_group_id);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetTechersList()
    {
        $result = Teacher::OrderByFio();

        $result->map(function ($t) {
            $t->TeacherId = (string) $t->id;
            unset($t->id);

            $t->FIO = (string) $t->fio;
            unset($t->fio);

            unset($t->phone);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetTFDList()
    {
        $result = Discipline::DTall();

        $result->map(function ($tfd) {
            $tfd->TeacherForDisciplineId = (string) $tfd->id;
            unset($tfd->id);

            $tfd->TeacherId = (string) $tfd->teacher_id;
            unset($tfd->teacher_id);

            $tfd->DisciplineId = (string) $tfd->discipline_id;
            unset($tfd->discipline_id);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetLogEvents()
    {
        $result = LessonLogEvent::all();

        $result->map(function ($lle) {
            $lle->LessonLogEventId = (string) $lle->id;
            unset($lle->id);

            $lle->OldLessonId = (string) $lle->old_lesson_id;
            unset($lle->old_lesson_id);

            $lle->NewLessonId = (string) $lle->new_lesson_id;
            unset($lle->new_lesson_id);

            $lle->DateTime = (string) $lle->date_time;
            unset($lle->date_time);

            $lle->Comment = $lle->public_comment;
            unset($lle->public_comment);

            unset($lle->hidden_comment);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetFacultiesList()
    {
        $result = Faculty::all();

        $result->map(function ($f) {
            $f->FacultyOd = (string) $f->id;
            unset($f->id);

            $f->Name = $f->name;
            unset($f->name);

            $f->Letter = $f->letter;
            unset($f->letter);

            $f->SortingOrder = (string) $f->sorting_order;
            unset($f->sorting_order);

            unset($f->schedule_signing_title);
            unset($f->dean_signing_schedule);
            unset($f->session_signing_title);
            unset($f->dean_signing_session_schedule);

        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetBuildingsList()
    {
        $result = Building::all();

        $result->map(function ($b) {
            $b->BuildingId = (string) $b->id;
            unset($b->id);

            $b->Name = $b->name;
            unset($b->name);
        });

        return $result;
    }

    /**
     * @return Collection|static[]
     */
    private function GetGroupInFaculties()
    {
        $result = Faculty::FSGall();

        $result->map(function ($gif) {
            $gif->GroupsInFacultyId = (string) $gif->id;
            unset($gif->id);

            $gif->FacultyId = (string) $gif->faculty_id;
            unset($gif->faculty_id);

            $gif->StudentGroupId = (string) $gif->student_group_id;
            unset($gif->student_group_id);
        });

        return $result;
    }

    private function GetTfdListExpanded($input)
    {
        if (isset($input['teacherId']))
        {
            $teacherId = $input["teacherId"];

            $result = DB::table('discipline_teacher')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->select('discipline_teacher.id as TeacherForDisciplineId', 'disciplines.name as disciplineName', 'teachers.fio as teacherFIO',
                    'student_groups.name as studentGroupName')
                ->where('discipline_teacher.teacher_id', '=', $teacherId)
                ->get();

            return $result;
        }
        else
        {
            $result = DB::table('discipline_teacher')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->select('discipline_teacher.id as TeacherForDisciplineId', 'disciplines.name as disciplineName', 'teachers.fio as teacherFIO',
                    'student_groups.name as studentGroupName')
                ->get();

            return $result;
        }
    }

    private function DisciplineLessons($input)
    {
        if (!isset($input['tfdId']))
        {
            return array("error" => "tfdId обязательный параметр");
        }

        $tfdId = $input["tfdId"];

        $result = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->select('lessons.id as LessonId', 'calendars.date as Date', 'rings.time as Time',  'auditoriums.name as Name')
            ->where('lessons.state', '=', '1')
            ->where('lessons.discipline_teacher_id', '=', $tfdId)
            ->get();

        return $result;
    }

    private function GetGroupDisciplinesList($input)
    {
        if (!isset($input['groupId']))
        {
            return array("error" => "groupId обязательный параметр");
        }

        $groupId = $input["groupId"];

        $smallLessons = false;

        $groupName = StudentGroup::find($groupId)->name;

        $groupNamePieces = explode(" ", $groupName);
        $groupNameNumber = $groupNamePieces[0];
        if (is_numeric($groupNameNumber) && intval($groupNameNumber) < 12) {
            $schoolGroup = true;
        }
        if (is_numeric($groupNameNumber) && intval($groupNameNumber) < 8) {
            $smallLessons = true;
        }

        $groupIds = StudentGroup::GetGroupsOfStudentFromGroup($groupId);

        $data = DB::table('disciplines')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->select('disciplines.name as Name', 'disciplines.attestation as Attestation',
                'disciplines.auditorium_hours as AuditoriumHours', 'student_groups.name  AS GroupName',
                'discipline_teacher.id as TFDID', 'disciplines.lecture_hours as LectureHours',
                'disciplines.practical_hours as PracticalHours', 'disciplines.auditorium_hours_per_week as AuditoriumHoursPerWeek',
                'teachers.fio as FIO', 'disciplines.id as DiscId')
            ->whereIn('disciplines.student_group_id', $groupIds)
            ->get();

        $FakeTFD = -2;

        $result = array();

        foreach($data as $disc)
        {
            if ($disc->TFDID == "")
            {
                $disc->TFDID = $FakeTFD;
                $FakeTFD--;
            }

            $result[$disc->TFDID] = array();
            $result[$disc->TFDID]["Name"] = $disc->Name;
            $result[$disc->TFDID]["AuditoriumHours"] = $schoolGroup ?
                $disc->AuditoriumHoursPerWeek : $disc->AuditoriumHours;
            $result[$disc->TFDID]["Attestation"] = $disc->Attestation;
            $result[$disc->TFDID]["LectureHours"] = $disc->LectureHours;
            $result[$disc->TFDID]["PracticalHours"] = $disc->PracticalHours;
            $result[$disc->TFDID]["GroupName"] = $disc->GroupName;
            $result[$disc->TFDID]["teacherFIO"] = $disc->FIO;
        }

        foreach ($result as $tfdId => $discData)
        {
            $hours = DB::table('lessons')
                ->where('lessons.discipline_teacher_id', '=', $tfdId)
                ->count();

            $result[$tfdId]["hoursCount"] = $smallLessons ? $hours : $hours*2;
        }

        return $result;
    }

    private function GetWeeksSchedule($input)
    {
        if ((!isset($input['groupId'])) || (!isset($input['weeks'])))
        {
            return array("error" => "groupId и weeks обязательные параметры");
        }

        Carbon::setWeekStartsAt(Carbon::MONDAY);
        Carbon::setWeekEndsAt(Carbon::SUNDAY);

        $weeks = explode('|', $input['weeks']);
        sort($weeks);
        $groupId = $input['groupId'];

        $ss = ConfigOption::SemesterStarts();

        $css = Carbon::createFromFormat('Y-m-d', $ss);
        $css = $css->startOfWeek();
        $ssString = $css->format('Y-m-d');

        $groupIds = StudentGroup::GetGroupsOfStudentFromGroup($groupId);

        if (!array_key_exists('compactResult', $input))
        {
            $result = array();

            for ($i = 0; $i < count($weeks); $i++) {
                $weeksCalendars = Calendar::IdsFromWeeks($weeks);

                $rawLessons = DB::table('lessons')
                    ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                    ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                    ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                    ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                    ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                    ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                    ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                    ->where('lessons.state', '=', 1)
                    ->whereIn('disciplines.student_group_id', $groupIds)
                    ->whereIn('lessons.calendar_id', $weeksCalendars)
                    ->select('lessons.id as lessonId', 'calendars.date AS date', 'rings.time as Time', 'disciplines.name AS discName',
                        'teachers.fio as FIO', 'auditoriums.name AS audName', 'student_groups.name AS groupName', 'teachers.id as teacherId')
                    ->get();

                $lessons = array();

                $rawLessons->map(function ($lesson) {
                    $lesson->dow = date("N", strtotime($lesson->date));
                    $lessons[] = $lesson;
                });

                $result[$weeks[$i]] = $lessons;
            }

            return $result;
        }
        else
        {
            $weeksCalendars = Calendar::IdsFromWeeks($weeks);

            $rawLessons = DB::table('lessons')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->where('lessons.state', '=', 1)
                ->whereIn('disciplines.student_group_id', $groupIds)
                ->whereIn('lessons.calendar_id', $weeksCalendars)
                ->select('lessons.id as lessonId', 'disciplines.name as discName', 'rings.time as startTime', 'calendars.date as date',
                    'teachers.fio as teacherFIO', 'auditoriums.name as auditoriumName', 'discipline_teacher.id as tfdId',
                    'student_groups.name as groupName', 'student_groups.id as groupId', 'rings.id as ringId', 'teachers.id as teacherId')
                ->get();

            $lessons = array("1" => array(), "2" => array(), "3" => array(), "4" => array(),
                "5" => array(), "6" => array(), "7" => array());
            $timeArray = array();

            $semesterStarts = Carbon::parse(ConfigOption::SemesterStarts());

            foreach ($rawLessons as $lesson)
            {
                $lessonWeek = Calendar::WeekFromDate($lesson->date, $semesterStarts);
                $dow = Carbon::createFromFormat('Y-m-d', $lesson->date)->isoFormat('E');
                $lesson->dow = $dow;

                $time = mb_substr($lesson->startTime, 0, 5);
                if (!array_key_exists($time, $lessons[$dow]))
                {
                    if (!in_array($time, $timeArray))
                    {
                        $timeArray[] = $time;
                    }
                    $lessons[$dow][$time] = array();
                }


                $tfd = $lesson->tfdId;
                if (!array_key_exists($tfd, $lessons[$dow][$time]))
                {
                    $lessons[$dow][$time][$tfd] = array();
                }

                $lessonAud = $lesson->auditoriumName;
                if (!array_key_exists("weeksAndAuds", $lessons[$dow][$time][$tfd]))
                {
                    $lessons[$dow][$time][$tfd]["weeksAndAuds"] = array();
                }
                if (!array_key_exists("lessons", $lessons[$dow][$time][$tfd]))
                {
                    $lessons[$dow][$time][$tfd]["lessons"] = array();
                }
                if (!array_key_exists($lessonAud, $lessons[$dow][$time][$tfd]["weeksAndAuds"]))
                {
                    $lessons[$dow][$time][$tfd]["weeksAndAuds"][$lessonAud] = array();
                }
                $lessons[$dow][$time][$tfd]["weeksAndAuds"][$lessonAud][] = $lessonWeek;
                $lesson->week = $lessonWeek;

                $lessons[$dow][$time][$tfd]["lessons"][] = $lesson;
            }

            return $lessons;
        }
    }

    private function LastLessons($input)
    {
        $groupIds = array();

        $groupId = -1;
        if (isset($input['groupId']))
        {
            $groupId = $input['groupId'];

            $groupIds = StudentGroup::GetGroupsOfStudentFromGroup($groupId);
        }

        $facultyId = -1;
        if (isset($input['facultyId']))
        {
            $facultyId = $input['facultyId'];

            $groupIds = Faculty::GetStudentGroupIdsFromFacultyId($facultyId);
        }

        $disciplines = DB::table('disciplines')
            ->leftJoin('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->whereIn('student_groups.id', $groupIds)
            ->select('discipline_teacher.id as TFDID', 'disciplines.name as Name',
                'disciplines.attestation as Attestation', 'disciplines.auditorium_hours as AuditoriumHours',
                'student_groups.name AS GroupName', 'teachers.fio as FIO')
            ->distinct()
            ->get();

        $discList = array();
        foreach ($disciplines as $discipline)
        {
            $lastLessonDateList = DB::table('lessons')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->where('lessons.discipline_teacher_id', '=', $discipline->TFDID)
                ->where('lessons.state', '=', 1)
                ->orderBy('calendars.date', 'DESC')
                ->limit(1)
                ->select('calendars.date')
                ->get();

            $lastLessonDate = $lastLessonDateList->isEmpty() ? "" : $lastLessonDateList[0]->date;

            $disciplineResult = array();
            $disciplineResult["Name"] = $discipline->Name;
            $disciplineResult["Attestation"] = $discipline->Attestation;
            $disciplineResult["GroupName"] = $discipline->GroupName;
            $disciplineResult["teacherFIO"] = $discipline->FIO;
            $disciplineResult["lastLessonDate"] = $lastLessonDate;
            $discList[] = $disciplineResult;
        }

        usort($discList, function ($a, $b)
        {
            $aDate = DateTime::createFromFormat('Y-m-d', $a["lastLessonDate"]);
            $bDate = DateTime::createFromFormat('Y-m-d', $b["lastLessonDate"]);

            if ($aDate > $bDate) {
                $result = 1;
            } else {
                if ($aDate < $bDate) {
                    $result = -1;
                } else {
                    $result = ($a["GroupName"] > $b["GroupName"]) ? 1 : (($a["GroupName"] < $b["GroupName"]) ? -1 : 0);
                }
            }

            return $result;
        });

        return $discList;
    }

    private function GetTeacherLessons($input)
    {
        if(!isset($input['teacherId']))
        {
            return array("error" => "teacherId - обязательный параметр");
        }

        $teacherId = $input['teacherId'];

        $lessons = DB::table('lessons')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->where('teachers.id', '=', $teacherId)
            ->where('lessons.state', '=', 1)
            ->orderBy('calendars.date', 'asc')
            ->orderBy('rings.time', 'asc')
            ->select('lessons.id as LessonId', 'disciplines.name as disciplineName',
                'student_groups.name as studentGroupname', 'calendars.date as Date',
                'rings.time as Time', 'auditoriums.name as auditoriumName')
            ->get();

        return $lessons;
    }

    private function GetTeacherWeeksSchedule($input)
    {
        if (!isset($input['teacherId']))
        {
            return array("error" => "teacherId обязательный параметр");
        }
        $teacherId = $input['teacherId'];

        $weeks = array();
        if (!isset($input['weeks']))
        {
            $weekCount = Calendar::WeekCount();
            $weeks = range(1, $weekCount);
        } else {
            $weeks = explode('|', $input['weeks']);
            sort($weeks);
        }

        $semesterStarts = ConfigOption::SemesterStarts();

        $result = array();

        if (!array_key_exists('compactResult', $input))
        {
            $weeksCount = count($weeks);
            for ($i = 0; $i < $weeksCount; $i++) {
                $week = $weeks[$i];

                $calendarIds = Calendar::IdsFromWeeks(array($week));

                $lessons = DB::table('lessons')
                    ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                    ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                    ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                    ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                    ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                    ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                    ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                    ->where('teachers.id', '=', $teacherId)
                    ->where('lessons.state', '=', 1)
                    ->whereIn('calendars.id', $calendarIds)
                    ->select('calendars.date as Date', 'rings.time as Time', 'student_groups.name as groupName',
                        'disciplines.name as disciplineName', 'auditoriums.name as auditoriumName',
                        'discipline_teacher.id as TeacherForDisciplineId')
                    ->get();

                foreach ($lessons as $lesson)
                {
                    $dow = Lesson::GetLessonDow($lesson->Date);
                    $lesson->dow = $dow;
                }

                $lessons = $lessons->toArray();

                usort($lessons, function ($a, $b) {
                    if ($a->dow < $b->dow) {
                        return -1;
                    }

                    if ($a->dow > $b->dow) {
                        return 1;
                    }

                    if ($a->Time < $b->Time) {
                        return -1;
                    }

                    if ($a->Time > $b->Time) {
                        return 1;
                    }

                    return 0;
                });

                $result[$week] = $lessons;
            }

            return $result;
        }
        else
        {
            $calendarIds = Calendar::IdsFromWeeks($weeks);

            $rawLessons = DB::table('lessons')
                ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                ->where('teachers.id', '=', $teacherId)
                ->where('lessons.state', '=', 1)
                ->whereIn('calendars.id', $calendarIds)
                ->select('disciplines.name as discName', 'rings.time as startTime', 'calendars.date as date',
                    'teachers.fio as teacherFIO', 'auditoriums.name as auditoriumName',
                    'discipline_teacher.id as tfdId', 'student_groups.name as groupName', 'student_groups.id as groupId')
                ->get();

            $lessons = array("1" => array(), "2" => array(), "3" => array(), "4" => array(),
                "5" => array(), "6" => array(), "7" => array());
            $timeArray = array();

            $SemesterStarts = Carbon::createFromFormat('Y-m-d', ConfigOption::SemesterStarts());

            foreach ($rawLessons as $lesson)
            {
                $dow = Lesson::GetLessonDow($lesson->date);
                $lesson->dow = $dow;

                $time = mb_substr($lesson->startTime, 0, 5);
                if (!array_key_exists($time, $lessons[$dow]))
                {
                    if (!in_array($time, $timeArray))
                    {
                        $timeArray[] = $time;
                    }
                    $lessons[$dow][$time] = array();
                }

                $tfd = $lesson->tfdId;
                if (!array_key_exists($tfd, $lessons[$dow][$time]))
                {
                    $lessons[$dow][$time][$tfd] = array();
                }

                $lessonAud = $lesson->auditoriumName;
                if (!array_key_exists("weeksAndAuds", $lessons[$dow][$time][$tfd]))
                {
                    $lessons[$dow][$time][$tfd]["weeksAndAuds"] = array();
                }
                if (!array_key_exists("lessons", $lessons[$dow][$time][$tfd]))
                {
                    $lessons[$dow][$time][$tfd]["lessons"] = array();
                }
                if (!array_key_exists($lessonAud, $lessons[$dow][$time][$tfd]["weeksAndAuds"]))
                {
                    $lessons[$dow][$time][$tfd]["weeksAndAuds"][$lessonAud] = array();
                }
                $lessons[$dow][$time][$tfd]["weeksAndAuds"][$lessonAud][] = Calendar::WeekFromDate($lesson->date, $SemesterStarts);

                $lessons[$dow][$time][$tfd]["lessons"][] = $lesson;
            }

            return $lessons;
        }
    }

    private function GetTeacherExams($input)
    {
        if(!isset($input['teacherId']))
        {
            return array("error" => "teacherId - обязательный параметр");
        }

        $teacherId = $input['teacherId'];

        $exams = DB::table('exams')
            ->join('disciplines', 'exams.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->join('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('auditoriums as consAud', 'exams.consultation_auditorium_id', '=', 'consAud.id')
            ->join('auditoriums as examAud', 'exams.exam_auditorium_id', '=', 'examAud.id')
            ->where('teachers.id', '=', $teacherId)
            ->where('exams.is_active', '=', 1)
            ->select('exams.id as ExamId', 'disciplines.name as Name', 'student_groups.name AS groupName',
                'teachers.fio as FIO', 'exams.consultation_datetime as ConsultationDateTime',
                'exams.exam_datetime as ExamDateTime', 'consAud.name as consultationAud',
                'examAud.name as examinationAud')
            ->get();

        foreach ($exams as $exam) {
            if ($exam->ConsultationDateTime == "2020-01-01 00:00:00") {
                $exam->ConsultationDateTime = "";
            }

            if ($exam->ExamDateTime == "2020-01-01 00:00:00") {
                $exam->ExamDateTime = "";
            }
        }

        return $exams;
    }

    private function GetFacultyWeeksSchedule($input)
    {
        if ((!isset($input['facultyId'])) || (!isset($input['weeks'])))
        {
            return array("error" => "facultyId и weeks обязательные параметры");
        }

        $facultyId = $input['facultyId'];
        $weeks = $input['weeks'];

        $facultyGroups = DB::table('student_groups')
            ->join('faculty_student_group', 'student_groups.id', '=', 'faculty_student_group.student_group_id')
            ->where('faculty_student_group.faculty_id', '=', $facultyId)
            ->select('student_groups.*')
            ->get()
            ->reverse()->toArray();

        usort( $facultyGroups, function( $a, $b ) {
            $aVal = intval($a->name);
            $bVal = intval($a->name);

            if ($aVal === $bVal) {
                if ($a->name === $b->name) return 0;
                return ($a->name < $b->name) ? -1 : 1;
            }

            return ($aVal < $bVal) ? -1 : 1;
        });

        Carbon::setWeekStartsAt(Carbon::MONDAY);
        Carbon::setWeekEndsAt(Carbon::SUNDAY);

        $weeks = explode('|', $input['weeks']);
        sort($weeks);

        $ss = ConfigOption::SemesterStarts();

        $css = Carbon::createFromFormat('Y-m-d', $ss);
        $css = $css->startOfWeek();

        $result = array();

        for ($index = 0; $index < sizeof($facultyGroups); $index++)
        {
            $group = $facultyGroups[$index];

            $groupId = $group->id;

            $groupIds = StudentGroup::GetGroupsOfStudentFromGroup($groupId);

            $weeksCalendars = Calendar::IdsFromWeeks($weeks);

            $rawLessons = DB::table('lessons')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->where('lessons.state', '=', 1)
                ->whereIn('disciplines.student_group_id', $groupIds)
                ->whereIn('lessons.calendar_id', $weeksCalendars)
                ->select('disciplines.name as discName', 'rings.time as startTime', 'calendars.date as date',
                    'teachers.fio as teacherFIO', 'auditoriums.name as auditoriumName', 'discipline_teacher.id as tfdId',
                    'student_groups.name as groupName', 'student_groups.id as groupId', 'lessons.id as lessonId',
                    'rings.id as ringId')
                ->get();

            $lessons = array("1" => array(), "2" => array(), "3" => array(), "4" => array(),
                "5" => array(), "6" => array(), "7" => array());
            $timeArray = array();

            $semesterStarts = Carbon::parse(ConfigOption::SemesterStarts());

            foreach ($rawLessons as $lesson)
            {
                $lessonWeek = Calendar::WeekFromDate($lesson->date, $semesterStarts);
                $dow = Carbon::createFromFormat('Y-m-d', $lesson->date)->isoFormat('E');
                $lesson->dow = $dow;

                $time = mb_substr($lesson->startTime, 0, 5);
                if (!array_key_exists($time, $lessons[$dow]))
                {
                    if (!in_array($time, $timeArray))
                    {
                        $timeArray[] = $time;
                    }
                    $lessons[$dow][$time] = array();
                }

                $tfd = $lesson->tfdId;
                if (!array_key_exists($tfd, $lessons[$dow][$time]))
                {
                    $lessons[$dow][$time][$tfd] = array();
                }

                $lessonAud = $lesson->auditoriumName;
                if (!array_key_exists("weeksAndAuds", $lessons[$dow][$time][$tfd]))
                {
                    $lessons[$dow][$time][$tfd]["weeksAndAuds"] = array();
                }
                if (!array_key_exists("lessons", $lessons[$dow][$time][$tfd]))
                {
                    $lessons[$dow][$time][$tfd]["lessons"] = array();
                }
                if (!array_key_exists($lessonAud, $lessons[$dow][$time][$tfd]["weeksAndAuds"]))
                {
                    $lessons[$dow][$time][$tfd]["weeksAndAuds"][$lessonAud] = array();
                }
                $lessons[$dow][$time][$tfd]["weeksAndAuds"][$lessonAud][] = $lessonWeek;

                $lessons[$dow][$time][$tfd]["lessons"][] = $lesson;
            }

            $resultItem = array();
            $resultItem["groupId"] = $group->id;
            $resultItem["groupName"] = $group->name;
            $resultItem["lessons"] = $lessons;

            $result[] = $resultItem;
        }

        return $result;
    }

    private function GetBuildingEvents($input) {
        if ((!isset($input['dow'])) || (!isset($input['weeks'])) || (!isset($input['buildingId'])))
        {
            return array("error" => "dow, weeks и buildingId обязательные параметры");
        }

        $dow = $input['dow'];

        $weeks = explode('|', $input['weeks']);
        sort($weeks);

        $buildingId = $input['buildingId'];

        $calendarIds = Calendar::IdsFromDowAndWeeks($dow, $weeks);

        $rawLessons = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->join('buildings', 'auditoriums.building_id', '=', 'buildings.id')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('lessons.state', '=', 1)
            ->where('buildings.id', '=', $buildingId)
            ->whereIn('calendars.id', $calendarIds)
            ->select('lessons.*', 'calendars.date', 'auditoriums.name as auditoriumName',
                'teachers.fio as teacherFio', 'disciplines.name as disciplineName',
                'student_groups.name as studentGroupName', 'discipline_teacher.id as disciplineTeacherId',
                'rings.time')
            ->get();

        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts());

        $schedule = array();
        $rings = array();
        $auditoriums = array();
        $result = array();

        foreach ($rawLessons as $lesson) {
            if (!array_key_exists($lesson->ring_id, $schedule))
            {
                $schedule[$lesson->ring_id] = array();
            }

            if (!array_key_exists($lesson->auditorium_id, $schedule[$lesson->ring_id]))
            {
                $schedule[$lesson->ring_id][$lesson->auditorium_id] = array();
            }

            if (!array_key_exists($lesson->discipline_teacher_id, $schedule[$lesson->ring_id][$lesson->auditorium_id]))
            {
                $schedule[$lesson->ring_id][$lesson->auditorium_id][$lesson->discipline_teacher_id] = array();
                $schedule[$lesson->ring_id][$lesson->auditorium_id][$lesson->discipline_teacher_id]["weeksAndAuds"] = array();
                $schedule[$lesson->ring_id][$lesson->auditorium_id][$lesson->discipline_teacher_id]["lessons"] = array();
            }

            if (!array_key_exists($lesson->auditoriumName,
                    $schedule[$lesson->ring_id][$lesson->auditorium_id][$lesson->discipline_teacher_id]["weeksAndAuds"]))
            {
                $schedule[$lesson->ring_id][$lesson->auditorium_id][$lesson->discipline_teacher_id]["weeksAndAuds"]
                    [$lesson->auditoriumName] = array();
            }

            if (!array_key_exists($lesson->ring_id, $rings)) {
                $rings[$lesson->ring_id] = substr($lesson->time, 0, 5);
            }

            if (!array_key_exists($lesson->auditorium_id, $auditoriums)) {
                $auditoriums[$lesson->auditorium_id] = $lesson->auditoriumName;
            }

            $lessonWeek = Calendar::WeekFromDate($lesson->date, $css);

            $schedule[$lesson->ring_id][$lesson->auditorium_id][$lesson->discipline_teacher_id]["weeksAndAuds"]
                [$lesson->auditoriumName][] = $lessonWeek;

            $schedule[$lesson->ring_id][$lesson->auditorium_id][$lesson->discipline_teacher_id]["lessons"][] = $lesson;
        }

        $buildingAuditoriums = Building::AuditoriumIds($buildingId);

        $rawEvents = DB::table('auditorium_events')
            ->join('calendars', 'auditorium_events.calendar_id', '=', 'calendars.id')
            ->join('rings', 'auditorium_events.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'auditorium_events.auditorium_id', '=', 'auditoriums.id')
            ->whereIn('auditorium_events.calendar_id', $calendarIds)
            ->whereIn('auditorium_events.auditorium_id', $buildingAuditoriums)
            ->select('auditorium_events.*',
                'calendars.date', 'rings.time', 'auditoriums.name as auditoriumName')
            ->get();

        $eventNameTfdIdDict = array();
        $eventNameTfdId = -2;

        foreach ($rawEvents as $event) {
            if (!array_key_exists($event->ring_id, $schedule))
            {
                $schedule[$event->ring_id] = array();
            }

            if (!array_key_exists($event->auditorium_id, $schedule[$event->ring_id]))
            {
                $schedule[$event->ring_id][$event->auditorium_id] = array();
            }

            $eventTfd = -1;
            if (!array_key_exists($event->name, $eventNameTfdIdDict))
            {
                $eventNameTfdIdDict[$event->name] = $eventNameTfdId;
                $eventTfd = $eventNameTfdId;
                $eventNameTfdId--;
            }
            else {
                $eventTfd = $eventNameTfdIdDict[$event->name];
            }

            if (!array_key_exists($eventTfd, $schedule[$event->ring_id][$event->auditorium_id]))
            {
                $schedule[$event->ring_id][$event->auditorium_id][$eventTfd] = array();
                $schedule[$event->ring_id][$event->auditorium_id][$eventTfd]["weeksAndAuds"] = array();
                $schedule[$event->ring_id][$event->auditorium_id][$eventTfd]["events"] = array();
            }

            if (!array_key_exists($event->auditoriumName,
                $schedule[$event->ring_id][$event->auditorium_id][$eventTfd]["weeksAndAuds"]))
            {
                $schedule[$event->ring_id][$event->auditorium_id][$eventTfd]["weeksAndAuds"]
                    [$event->auditoriumName] = array();
            }

            if (!array_key_exists($event->ring_id, $rings)) {
                $rings[$event->ring_id] = substr($event->time, 0, 5);
            }

            if (!array_key_exists($event->auditorium_id, $auditoriums)) {
                $auditoriums[$event->auditorium_id] = $event->auditoriumName;
            }

            $eventWeek = Calendar::WeekFromDate($event->date, $css);

            $schedule[$event->ring_id][$event->auditorium_id][$eventTfd]["weeksAndAuds"]
                [$event->auditoriumName][] = $eventWeek;

            $schedule[$event->ring_id][$event->auditorium_id][$eventTfd]["events"][] = $event;
        }

        $result["schedule"] = $schedule;
        $result["rings"] = $rings;
        $result["auditoriums"] = $auditoriums;

        return $result;
    }

    private function GetFreeAuditoriums($input)
    {
        if ((!isset($input['dows'])) || (!isset($input['ringIds'])))
        {
            return array("error" => "dows и ringIds обязательные параметры");
        }

        $dows = explode('|', $input['dows']);
        $ringIds = explode('|', $input['ringIds']);

        $weeks = range(1, Calendar::WeekCount());

        $auditoriums = Auditorium::all();

        $semesterStarts = Carbon::parse(ConfigOption::SemesterStarts());

        $result = array();

        foreach($dows as $dow) {
            $result[$dow] = array();

            foreach ($weeks as $week) {
                $result[$dow][$week] = array();

                foreach ($ringIds as $ringId) {
                    $result[$dow][$week][$ringId] = array();

                    foreach ($auditoriums as $auditorium) {
                        $result[$dow][$week][$ringId][] = $auditorium->id;
                    }
                }
            }
        }

        $calendarsIdsByWeekAndDow = Calendar::IdsByWeekAndDowFromDowsAndWeeks($dows, $weeks);
        $calendarsIds = array_merge(...array_values($calendarsIdsByWeekAndDow));

        $targetLessons = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->where('lessons.state', '=', 1)
            ->whereIn('lessons.calendar_id', $calendarsIds)
            ->whereIn('lessons.ring_id', $ringIds)
            ->select('lessons.*', 'calendars.date as calendarsDate')
            ->get();

        foreach ($targetLessons as $lesson) {
            $lessonWeek = Calendar::WeekFromDate($lesson->calendarsDate, $semesterStarts);
            $lessonDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $lesson->calendarsDate));
            $lessonRing = $lesson->ring_id;

            if (($key = array_search($lesson->auditorium_id, $result[$lessonDow][$lessonWeek][$lessonRing])) !== false) {
                array_splice($result[$lessonDow][$lessonWeek][$lessonRing], $key, 1);
            }
        }

        $targetAuditoriumEvents = DB::table('auditorium_events')
            ->join('calendars', 'auditorium_events.calendar_id', '=', 'calendars.id')
            ->whereIn('auditorium_events.calendar_id', $calendarsIds)
            ->whereIn('auditorium_events.ring_id', $ringIds)
            ->select('auditorium_events.*', 'calendars.date as calendarsDate')
            ->get();

        foreach ($targetAuditoriumEvents as $auditoriumEvent) {
            $auditoriumEventWeek = Calendar::WeekFromDate($auditoriumEvent->calendarsDate, $semesterStarts);
            $auditoriumEventDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $auditoriumEvent->calendarsDate));
            $auditoriumEventRing = $auditoriumEvent->ring_id;

            if (($key = array_search($auditoriumEvent->auditorium_id, $result[$auditoriumEventDow][$auditoriumEventWeek][$auditoriumEventRing])) !== false) {
                array_splice($result[$auditoriumEventDow][$auditoriumEventWeek][$auditoriumEventRing], $key, 1);
            }
        }

        return $result;
    }
}

