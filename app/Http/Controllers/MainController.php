<?php

namespace App\Http\Controllers;

use App\DomainClasses\Auditorium;
use App\DomainClasses\Building;
use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Faculty;
use App\DomainClasses\Lesson;
use App\DomainClasses\LessonLogEvent;
use App\DomainClasses\Ring;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use App\DomainClasses\TeacherGroup;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function root()
    {
        return view('root');
    }

    public function groupSchedule()
    {
        $studentGroups = StudentGroup::allSorted()->toArray();
        $group_id = -1;
        $weekCount = Calendar::WeekCount();
        $auditoriums = Auditorium::all()->toArray();

        return view('main.groupSchedule', compact('studentGroups', 'group_id', 'weekCount', 'auditoriums'));
    }

    public function groupScheduleWithId(int $group_id)
    {
        $studentGroups = StudentGroup::allSorted()->toArray();
        $weekCount = Calendar::WeekCount();

        return view('main.groupSchedule', compact('studentGroups', 'group_id', 'weekCount'));
    }

    public function teacherGroupSchedule()
    {
        $teachers = Teacher::all()->sortBy('fio');
        $teacherGroups = TeacherGroup::all()->sortBy('name');
        $weekCount = Calendar::WeekCount();
        $rings = Ring::all()->toArray();

        return view('main.teacherGroupSchedule', compact('teachers', 'teacherGroups', 'weekCount', 'rings'));
    }

    public function facultySchedule()
    {
        $faculty_id = -1;
        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();
        $auditoriums = Auditorium::all()->toArray();

        return view('main.facultySchedule', compact('faculty_id', 'faculties', 'weekCount', 'auditoriums'));
    }

    public function facultyScheduleWithId(int $faculty_id)
    {
        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();
        $auditoriums = Auditorium::all()->toArray();

        return view('main.facultySchedule', compact('faculty_id', 'faculties', 'weekCount', 'auditoriums'));
    }

    public function teacherSchedule()
    {
        $teachers = Teacher::all()->sortBy('fio');

        $weekCount = Calendar::WeekCount();

        return view('main.teacherSchedule', compact('teachers', 'weekCount'));
    }

    public function groupSession()
    {
        $studentGroups = $this->GetSortedExamStudentGroups();
        $group_id = -1;

        return view('main.groupSession', compact('studentGroups', 'group_id'));
    }

    public function groupSessionWithId(int $group_id)
    {
        $studentGroups = $this->GetSortedExamStudentGroups();

        return view('main.groupSession', compact('studentGroups', 'group_id'));
    }

    /**
     * @return Collection
     */
    public function GetSortedExamStudentGroups(): Collection
    {
        $studentGroups = StudentGroup::allSorted()->toArray();

        $examGroupIds = DB::table('exams')
            ->join('disciplines', 'exams.discipline_id', '=', 'disciplines.id')
            ->select('disciplines.student_group_id')
            ->distinct()
            ->where('exams.is_active', '=', 1)
            ->pluck('disciplines.student_group_id');

        $studentGroups = array_filter($studentGroups, function ($item) use ($examGroupIds) {
            $groupGroupsIds = StudentGroup::GetGroupsOfStudentFromGroup($item["id"]);

            $intersectCount = $examGroupIds->intersect($groupGroupsIds)->count();

            return ($intersectCount !== 0);
        });

        $studentGroups = new Collection($studentGroups);
        $studentGroups = $studentGroups->sort(function ($a, $b) {

            $num1 = explode(" ", $a["name"])[0];
            $num2 = explode(" ", $b["name"])[0];

            if ($num1 == $num2) {
                if ($a["name"] == $b["name"]) return 0;
                return $a["name"] < $b["name"] ? -1 : 1;
            } else {
                return ($num1 < $num2) ? -1 : 1;
            }
        });
        return $studentGroups;
    }

    public function buildingEvents()
    {
        $weekCount = Calendar::WeekCount();
        $buildings = Building::all()->sortBy('name');

        return view('main.buildingEvents', compact('weekCount', 'buildings'));
    }

    public function disciplineHours(Request $request) {
        $input = $request->all();

        $groupId = -1;
        if (isset($input['groupId']))
        {
            $groupId = $input["groupId"];
        }

        $studentGroups = StudentGroup::allSorted()->toArray();

        $weekCount = Calendar::WeekCount();

        return view('main.disciplineHours', compact('studentGroups', 'groupId', 'weekCount'));
    }

    public function teacherHours(Request $request) {
        $input = $request->all();

        $teacherId = -1;
        if (isset($input['groupId']))
        {
            $teacherId = $input["teacherId"];
        }

        $teachers = Teacher::all()->sortBy('fio')->toArray();

        $weekCount = Calendar::WeekCount();

        $studentGroups = StudentGroup::allSorted()->toArray();

        return view('main.teacherHours', compact('teachers', 'teacherId', 'weekCount', 'studentGroups'));
    }

    public function lessonLogEvents()
    {
        $groupId = -1;
        $studentGroups = StudentGroup::allSorted()->toArray();
        $weekCount = Calendar::WeekCount();
        $auditoriums = Auditorium::all()->toArray();

        return view('main.lessonLogEvents', compact('studentGroups', 'groupId', 'weekCount', 'auditoriums'));
    }

    public function lessonLogEventsWithId(int $groupId)
    {
        $studentGroups = StudentGroup::allSorted()->toArray();
        $weekCount = Calendar::WeekCount();
        $auditoriums = Auditorium::all()->toArray();

        return view('main.lessonLogEvents', compact('studentGroups', 'groupId', 'weekCount', 'auditoriums'));
    }

    public function auds()
    {
        $lessons = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('lessons.state', '=', 1)
            ->select('disciplines.name as disciplineName', 'auditoriums.name as audName', 'student_groups.name as groupName')
            ->get();

        $result = array();

        foreach($lessons as $lesson) {
            if (!array_key_exists($lesson->disciplineName, $result)) {
                $result[$lesson->disciplineName] = array();
                $result[$lesson->disciplineName]["1-4"] = array();
                $result[$lesson->disciplineName]["5-9"] = array();
                $result[$lesson->disciplineName]["10-11"] = array();
            }

            if (($this->startsWith($lesson->groupName, "1 ")) ||
                ($this->startsWith($lesson->groupName, "2 ")) ||
                ($this->startsWith($lesson->groupName, "3 ")) ||
                ($this->startsWith($lesson->groupName, "4 "))) {
                if (!in_array($lesson->audName, $result[$lesson->disciplineName]["1-4"])) {
                    $result[$lesson->disciplineName]["1-4"][] = $lesson->audName;
                }
            }

            if (($this->startsWith($lesson->groupName, "5 ")) ||
                ($this->startsWith($lesson->groupName, "6 ")) ||
                ($this->startsWith($lesson->groupName, "7 ")) ||
                ($this->startsWith($lesson->groupName, "8 ")) ||
                ($this->startsWith($lesson->groupName, "9 "))) {
                if (!in_array($lesson->audName, $result[$lesson->disciplineName]["5-9"])) {
                    $result[$lesson->disciplineName]["5-9"][] = $lesson->audName;
                }
            }

            if (($this->startsWith($lesson->groupName, "10 ")) ||
                ($this->startsWith($lesson->groupName, "11 "))) {
                if (!in_array($lesson->audName, $result[$lesson->disciplineName]["10-11"])) {
                    $result[$lesson->disciplineName]["10-11"][] = $lesson->audName;
                }
            }
        }

        foreach($result as $name => $list) {
            sort($list["1-4"]);
            $result[$name]["1-4"] = $list["1-4"];

            sort($list["5-9"]);
            $result[$name]["5-9"] = $list["5-9"];

            sort($list["10-11"]);
            $result[$name]["10-11"] = $list["10-11"];
        }

        return $result;
    }

    function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    public function teacherCollisions(Request $request) {
        $collisions = $this->teachersCollisions($request);

        return view('main.teacherCollisions', compact('collisions'));
    }

    public function CollisionsByTeacher() {
        $weekCount = Calendar::WeekCount();

        return view('main.CollisionsByTeacher', compact('weekCount'));
    }

    public function teachersCollisions(Request $request)
    {
        $input = $request->all();

        $calendars = Calendar::all()->pluck('id');
        $weeks = array();

        if (isset($input['weeks'])) {
            $weeks = explode('|', $input['weeks']);
            sort($weeks);

            $calendarIds = Calendar::IdsFromWeeks($weeks);
        }

        $teachers = Teacher::all()->sortBy('fio');

        $collisions = array();

        foreach ($teachers as $teacher) {
            $teacherTfds = DB::table('discipline_teacher')
                ->where('teacher_id', '=', $teacher->id)
                ->get()
                ->map(function ($item) {
                    return $item->id;
                });

            if (count($weeks) !== 0) {
                $teacherLessons = DB::table('lessons')
                    ->where('lessons.state', '=', '1')
                    ->whereIn('lessons.calendar_id', $calendarIds)
                    ->whereIn('discipline_teacher_id', $teacherTfds)
                    ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                    ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                    ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                    ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                    ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                    ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                    ->select('lessons.*', 'disciplines.name as disciplineName', 'student_groups.name as studentGroupName',
                        'calendars.date as calendarDate', 'rings.time as ringsTime', 'auditoriums.name as auditoriumName')
                    ->get();
            } else {
                $teacherLessons = DB::table('lessons')
                    ->where('lessons.state', '=', '1')
                    ->whereIn('discipline_teacher_id', $teacherTfds)
                    ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                    ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                    ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                    ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                    ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                    ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                    ->select('lessons.*', 'disciplines.name as disciplineName', 'student_groups.name as studentGroupName',
                        'calendars.date as calendarDate', 'rings.time as ringsTime', 'auditoriums.name as auditoriumName')
                    ->get();
            }

            $lessons = array();

            foreach ($teacherLessons as $teacherLesson) {
                if (!array_key_exists($teacherLesson->calendar_id, $lessons)) {
                    $lessons[$teacherLesson->calendar_id] = array();
                }

                $collisionLessons = array();
                foreach ($lessons[$teacherLesson->calendar_id] as $dayLesson) {
                    if ($teacherLesson->ring_id === $dayLesson->ring_id) {
                        $collisionLessons[] = $dayLesson;
                    }
                }

                $lessons[$teacherLesson->calendar_id][] = $teacherLesson;

                if (count($collisionLessons) !== 0) {
                    if (!array_key_exists($teacher->id, $collisions)) {
                        $collisions[$teacher->id] = array();
                        $collisions[$teacher->id]['fio'] = $teacher->fio;
                        $collisions[$teacher->id]['collisions'] = array();
                    }

                    $collisionLessons[] = $teacherLesson;
                    $collisions[$teacher->id]['collisions'][$collisionLessons[0]->calendar_id][] = $collisionLessons;
                }
            }
        }
        return $collisions;
    }

    public function putAudsIndex()
    {
        $calendars = Calendar::all()->sortBy('date');

        return view('main.putAuds', compact('calendars'));
    }

    public function putDailyAuds(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();

        if(!isset($input['date']))
        {
            return array("error" => "date - обязательный параметр");
        }
        $date = $input["date"];

        $calendarId = Calendar::IdfromDate($date);

        $rulesData = array(
            "Ауд. 102" => array("Лаврова Тамара Васильевна", "Гурьянова Алла Александровна", "Соколова Елена Юрьевна", "Гаврилова Екатерина Егоровна", "Соловьева Ольга Борисовна"),
            "Ауд. 107" => array("Мидзяева Дария Николаевна", "Гаврилова Екатерина Егоровна", "Соловьева Ольга Борисовна", "Орлова Наталья Евгеньевна", "Маслова Надежда Георгиевна", "Мельникова Мария Александровна"),
            "Ауд. 109" => array("Сальникова Анна Александровна", "Соколова Елена Юрьевна", "Орлова Наталья Евгеньевна", "Гаврилова Екатерина Егоровна", "Соловьева Ольга Борисовна"),
            "Ауд. 110" => array("Морозова Светлана Владимировна", "Гаврилова Екатерина Егоровна", "Соловьева Ольга Борисовна", "Орлова Наталья Евгеньевна", "Мельникова Мария Александровна", "Маслова Надежда Георгиевна"),
            "Ауд. 115" => array("Абрамова Елена Анатольевна", "Минебаева Алла Константиновна", "Коннова Виктория Владимировна", "Захарова Наталья Владимировна", "Орлова Наталья Евгеньевна", "Гаврилова Екатерина Егоровна"),
            "Ауд. 117" => array("Горшков Александр Александрович", "Дятлова Елена Алексеевна", "Самойлова Галина Ивановна", "Баландин Константин Александрович", "Казаков Игорь Валентинович"),
            "Ауд. 203" => array("Ермохина Любовь Павловна", "Морозов Иван Анатольевич"),
            "Ауд. 204" => array("Завершинская Ирина Андреевна", "Морозов Иван Анатольевич"),
            "Ауд. 205" => array("Балаева Зинаида Михайловна"),
            "Ауд. 206" => array("Таумов Ирбулат Джуламанович", "Царева Юлия Владимировна", "Тарсуков Владимир Петрович", "Карпенко Геннадий Юрьевич"),
            "Ауд. 207" => array("Якушева Елена Ивановна", "Дятлова Елена Алексеевна", "Самойлова Галина Ивановна", "Баландин Константин Александрович", "Казаков Игорь Валентинович"),
            "Ауд. 208" => array("Соколова Наталья Викторовна", "Морозов Иван Анатольевич"),
            "Ауд. 209" => array("Коган Ирина Исааковна", "Швалова Светлана Алексеевна", "Тарсуков Владимир Петрович", "Гранкина Евгения Александровна"),
            "Ауд. 211" => array("Исаханова Виктория Самсоновна", "Родионова Марина Юрьевна", "Морозов Иван Анатольевич"),
            "Ауд. 214" => array("Иванов Василий Николаевич"),
            "Ауд. 219" => array("Ильдарханов Ильяс Маратович", "Дятлова Елена Алексеевна", "Самойлова Галина Ивановна", "Баландин Константин Александрович", "Казаков Игорь Валентинович"),
            "Ауд. 220" => array("Шушпанова Анна Олеговна", "Морозов Иван Анатольевич"),
            "Ауд. 222" => array("Гришина Галина Михайловна", "Морозов Иван Анатольевич"),
            "Ауд. 301" => array("Жаринова Людмила Александровна", "Корнеева Зульфия Наильевна", "Косенко Елена Владимировна"),
            "Ауд. 302" => array("Безуглов Сергей Викторович", "Корнеева Зульфия Наильевна", "Косенко Елена Владимировна"),
            "Ауд. 303" => array("Никулина Татьяна Геннадьевна", "Царева Юлия Владимировна", "Наприенко Анастасия Владимировна", "Онищенко Нэлла Константиновна", "Воронова Анна Владиславовна", "Тарсуков Владимир Петрович", "Карпенко Геннадий Юрьевич"),
            "Ауд. 304" => array("Чиликина Ольга Вячеславовна", "Царева Юлия Владимировна", "Наприенко Анастасия Владимировна", "Онищенко Нэлла Константиновна", "Воронова Анна Владиславовна", "Тарсуков Владимир Петрович", "Карпенко Геннадий Юрьевич"),
            "Ауд. 305" => array("Сундукова Ксения Алексеевна", "Воронова Анна Владиславовна", "Наприенко Анастасия Владимировна", "Онищенко Нэлла Константиновна", "Тарсуков Владимир Петрович", "Карпенко Геннадий Юрьевич"),
            "Ауд. 306" => array("Родионова Марина Юрьевна", "Седова Марина Анатольевна", "Нежданова Елена Дмитриевна"),
            "Ауд. 307" => array("Тюзина Марина Борисовна", "Калуцкая Елена Николаевна", "Мелихова Татьяна Васильевна", "Силянова Ильнора Хамзяевна", "Верещагина Екатерина Константиновна", "Орлова Наталья Евгеньевна"),
            "Ауд. 308" => array("Герасимова Татьяна Анатольевна", "Макарова Татьяна Владимировна", "Кведер Лариса Владимировна"),
            "Ауд. 311" => array("Куцева Ирина Контантиновна", "Макарова Татьяна Владимировна", "Кведер Лариса Владимировна"),
        );

        $auditoriums = Auditorium::all();
        $audsById = array();
        foreach ($auditoriums as $auditorium) {
            $audsById[$auditorium->name] = $auditorium->id;
        }

        $teachers = Teacher::all();
        $teacherIdByFio = array();
        foreach ($teachers as $teacher) {
            $teacherIdByFio[$teacher->fio] = $teacher->id;
        }

        $maxRuleTeachersCount = 0;
        $rules = array();
        foreach ($rulesData as $audName => $audTeachers) {
            if (array_key_exists($audName, $audsById)) {
                $ruleTeachersCount = 0;
                $rules[$audsById[$audName]] = array();
                foreach ($audTeachers as $audTeacher) {
                    if (array_key_exists($audTeacher, $teacherIdByFio)) {
                        $rules[$audsById[$audName]][] = $teacherIdByFio[$audTeacher];
                        $ruleTeachersCount++;
                    }
                }

                if ($ruleTeachersCount > $maxRuleTeachersCount) {
                    $maxRuleTeachersCount = $ruleTeachersCount;
                }
            }
        }

        $audByTeacherId = [];
        foreach($rules as $audId => $teachersId) {
            foreach($teachersId as $teacherId) {
                if (!array_key_exists($teacherId, $audByTeacherId)) {
                    $audByTeacherId[$teacherId] = [];
                }

                $audByTeacherId[$teacherId][] = $audId;
            }
        }

        for ($ruleTeacherIndex = 0; $ruleTeacherIndex < $maxRuleTeachersCount; $ruleTeacherIndex++) {
            foreach ($rules as $audId => $teachersId) {
                if ($ruleTeacherIndex < count($teachersId)) {
                    $ruleTeacherId = $teachersId[$ruleTeacherIndex];

                    $teacherLessons = DB::table('lessons')
                        ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                        ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                        ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                        ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                        ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                        ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                        ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                        ->where('lessons.state', '=', '1')
                        ->where('calendars.id', '=', $calendarId)
                        ->where('teachers.id', '=', $ruleTeacherId)
                        ->select('lessons.id as lessonId', 'student_groups.name as studentGroupName',
                            'calendars.date as calendarsDate', 'calendars.id as calendarsId', 'rings.id as ringsId',
                            'auditoriums.id as auditoriumsId')
                        ->get();

                    $haveHighPriorityLessons = false;
                    $highPriorityTeacherIds = [];
                    for ($i = 0; $i < $ruleTeacherIndex; $i++) {
                        $highPriorityTeacherIds[] = $rules[$audId][$i];
                    }

                    foreach ($teacherLessons as $teacherLesson) {
                        $lessonDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $teacherLesson->calendarsDate));
                        $old_lesson = Lesson::find($teacherLesson->lessonId);

                        if ($old_lesson->auditorium_id === $audId) {
                            continue;
                        }

                        if (in_array($old_lesson->auditorium_id, $audByTeacherId[$ruleTeacherId])) {
                            continue;
                        }

                        if (($this->startsWith($teacherLesson->studentGroupName, "1 ")) ||
                            ($this->startsWith($teacherLesson->studentGroupName, "2 ")) ||
                            ($this->startsWith($teacherLesson->studentGroupName, "3 ")) ||
                            ($this->startsWith($teacherLesson->studentGroupName, "4 "))) {
                            continue;
                        }

                        if (($this->startsWith($teacherLesson->studentGroupName, "5 ") &&
                                in_array($lessonDow, array(2, 4, 6))) ||
                            ($this->startsWith($teacherLesson->studentGroupName, "6 ") &&
                                in_array($lessonDow, array(1, 3, 5)))) {
                            continue;
                        }

                        $audLessons = DB::table('lessons')
                            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                            ->where('lessons.state', '=', '1')
                            ->where('calendars.id', '=', $teacherLesson->calendarsId)
                            ->where('rings.id', '=', $teacherLesson->ringsId)
                            ->where('auditoriums.id', '=', $audId)
                            ->whereIn('teachers.id', $highPriorityTeacherIds)
                            ->select('lessons.id as lessonId', 'student_groups.name as studentGroupName', 'calendars.date as calendarsDate')
                            ->get();

                        if ($audLessons->count() !== 0) {
                            continue;
                        }

                        $old_lesson->state = 0;
                        $old_lesson->save();

                        $new_lesson = new Lesson();
                        $new_lesson->state = 1;
                        $new_lesson->discipline_teacher_id = $old_lesson->discipline_teacher_id;
                        $new_lesson->calendar_id = $old_lesson->calendar_id;
                        $new_lesson->ring_id = $old_lesson->ring_id;
                        $new_lesson->auditorium_id = $audId;
                        $new_lesson->save();

                        $lle = new LessonLogEvent();
                        $lle->old_lesson_id = $old_lesson->id;
                        $lle->new_lesson_id = $new_lesson->id;
                        $lle->date_time = Carbon::now()->format('Y-m-d H:i:s');
                        $lle->public_comment = "";
                        $lle->hidden_comment = ($user !== null) ? ($user->id . " @ " . $user->name . ": ") : "";
                        $lle->save();
                    }
                }
            }
        }

        return array("OK" => "Success");
    }

    public function clearDailyAuds(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();

        if (!isset($input['date'])) {
            return array("error" => "date - обязательный параметр");
        }
        $date = $input["date"];

        $calendarId = Calendar::IdfromDate($date);

        $groups = StudentGroup::all();
        $groupIds = [];
        foreach ($groups as $group) {
            if ((strpos($group->name, "5 ") === 0) || (strpos($group->name, "6 ") === 0) || (strpos($group->name, "7 ") === 0) ||
                (strpos($group->name, "8 ") === 0) || (strpos($group->name, "9 ") === 0) || (strpos($group->name, "10 ") === 0) ||
                (strpos($group->name, "11 ") === 0)) {
                $groupIds[] = $group->id;
            }
        }

        $calendarLessons = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('lessons.state', '=', '1')
            ->where('calendars.id', '=', $calendarId)
            ->whereIn('student_groups.id', $groupIds)
            ->select('lessons.id as lessonId', 'student_groups.name as studentGroupName',
                'calendars.date as calendarsDate', 'lessons.auditorium_id')
            ->get();

        $emptyAudId = DB::table('auditoriums')->where('name', '=', '--')->first()->id;

        foreach($calendarLessons as $calendarLesson) {
            $lessonDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $calendarLesson->calendarsDate));

            if ((strpos($calendarLesson->studentGroupName, "1 ") === 0) || (strpos($calendarLesson->studentGroupName, "2 ") === 0) ||
                (strpos($calendarLesson->studentGroupName, "3 ") === 0) || (strpos($calendarLesson->studentGroupName, "4 ") === 0)) {
                $emptyAudId = DB::table('auditoriums')->where('name', '=', '-')->first()->id;
            }

            if ($calendarLesson->auditorium_id === $emptyAudId) {
                continue;
            }

            if (($this->startsWith($calendarLesson->studentGroupName, "5 ") &&
                    in_array($lessonDow, array(2, 4, 6))) ||
                ($this->startsWith($calendarLesson->studentGroupName, "6 ") &&
                    in_array($lessonDow, array(1, 3, 5)))) {
                continue;
            }

            $old_lesson = Lesson::find($calendarLesson->lessonId);

            $old_lesson->state = 0;
            $old_lesson->save();

            $new_lesson = new Lesson();
            $new_lesson->state = 1;
            $new_lesson->discipline_teacher_id = $old_lesson->discipline_teacher_id;
            $new_lesson->calendar_id = $old_lesson->calendar_id;
            $new_lesson->ring_id = $old_lesson->ring_id;
            $new_lesson->auditorium_id = $emptyAudId;
            $new_lesson->save();

            $lle = new LessonLogEvent();
            $lle->old_lesson_id = $old_lesson->id;
            $lle->new_lesson_id = $new_lesson->id;
            $lle->date_time = Carbon::now()->format('Y-m-d H:i:s');
            $lle->public_comment = "";
            $lle->hidden_comment = ($user !== null) ? ($user->id . " @ " . $user->name . ": ") : "";
            $lle->save();
        }

        return array("OK" => "Success");
    }

    public function BlankAuds(Request $request) {
        $calendars = Calendar::all()->sortBy('date');

        return view('main.blankAuds', compact('calendars'));
    }

    public function GetBlankAuds(Request $request) {
        $input = $request->all();

        if(!isset($input['date']))
        {
            return array("error" => "date - обязательный параметр");
        }
        $date = $input["date"];
        $calendarId = Calendar::IdfromDate($date);

        $audNames = array('-', '--', '---');

        $audIds = DB::table('auditoriums')
            ->whereIn('name', $audNames)
            ->get()
            ->pluck('id');

        $lessonsWithBlankAuds = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('lessons.state', '=', 1)
            ->where('lessons.calendar_id', '=', $calendarId)
            ->whereIn('lessons.auditorium_id', $audIds)
            ->select('lessons.id as lessonId', 'student_groups.name as studentGroupsName',
                'disciplines.name as disciplinesName', 'teachers.fio as teachersFio',
                'calendars.date as calendarsDate', 'rings.id as ringsId', 'rings.time as ringsTime',
                'auditoriums.name as auditoriumsName')
            ->get()
            ->toArray();

        $lessonsWithBlankAuds = array_filter($lessonsWithBlankAuds, function($k, $v) {
            $lessonDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $k->calendarsDate));

            if (($this->startsWith($k->studentGroupsName, "5 ") &&
                    in_array($lessonDow, array(2, 4, 6))) ||
                ($this->startsWith($k->studentGroupsName, "6 ") &&
                    in_array($lessonDow, array(1, 3, 5)))) {
                return false;
            }

            return !(($this->startsWith($k->studentGroupsName, "1 ")) ||
                ($this->startsWith($k->studentGroupsName, "2 ")) ||
                ($this->startsWith($k->studentGroupsName, "3 ")) ||
                ($this->startsWith($k->studentGroupsName, "4 ")));
        }, ARRAY_FILTER_USE_BOTH);

        usort($lessonsWithBlankAuds, function($a, $b) {
            if ($a->teachersFio < $b->teachersFio) return -1;
            if ($a->teachersFio > $b->teachersFio) return 1;
            $aVal = mb_substr($a->ringsTime, 0, 2) * 60 + mb_substr($a->ringsTime, 3, 2);
            $bVal = mb_substr($b->ringsTime, 0, 2) * 60 + mb_substr($b->ringsTime, 3, 2);

            if ($aVal === $bVal) return 0;

            return ($aVal < $bVal) ? -1 : 1;
        });

        return $lessonsWithBlankAuds;
    }

    public function GetBlankAudsChap(Request $request) {
        $input = $request->all();

        if(!isset($input['date']))
        {
            return array("error" => "date - обязательный параметр");
        }
        $date = $input["date"];
        $calendarId = Calendar::IdfromDate($date);

        $audNames = array('-', '--', '---');

        $audIds = DB::table('auditoriums')
            ->whereIn('name', $audNames)
            ->get()
            ->pluck('id');

        $lessonsWithBlankAuds = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('lessons.state', '=', 1)
            ->where('lessons.calendar_id', '=', $calendarId)
            ->whereIn('lessons.auditorium_id', $audIds)
            ->select('lessons.id as lessonId', 'student_groups.name as studentGroupsName',
                'disciplines.name as disciplinesName', 'teachers.fio as teachersFio',
                'calendars.date as calendarsDate', 'rings.id as ringsId', 'rings.time as ringsTime',
                'auditoriums.name as auditoriumsName')
            ->get()
            ->toArray();

        $lessonsWithBlankAuds = array_filter($lessonsWithBlankAuds, function($k, $v) {
            return (($this->startsWith($k->studentGroupsName, "1 ")) ||
                ($this->startsWith($k->studentGroupsName, "2 ")) ||
                ($this->startsWith($k->studentGroupsName, "3 ")) ||
                ($this->startsWith($k->studentGroupsName, "4 ")));
        }, ARRAY_FILTER_USE_BOTH);

        usort($lessonsWithBlankAuds, function($a, $b) {
            if ($a->teachersFio < $b->teachersFio) return -1;
            if ($a->teachersFio > $b->teachersFio) return 1;
            $aVal = mb_substr($a->ringsTime, 0, 2) * 60 + mb_substr($a->ringsTime, 3, 2);
            $bVal = mb_substr($b->ringsTime, 0, 2) * 60 + mb_substr($b->ringsTime, 3, 2);

            if ($aVal === $bVal) return 0;

            return ($aVal < $bVal) ? -1 : 1;
        });

        return $lessonsWithBlankAuds;
    }

    public function lle() {
        $lleController = new LessonLogEventController();
        $dates = $lleController->Dates();

        return view('main.lle', compact( 'dates'));
    }

    public function lleTeacher() {
        $teachers = Teacher::all()->sortBy('fio');
        $weekCount = Calendar::WeekCount();

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);

        $semesterStarts = ConfigOption::SemesterStarts();

        $semesterStarts = mb_substr($semesterStarts, 8, 2) . '.' . mb_substr($semesterStarts, 5, 2) . '.' . mb_substr($semesterStarts, 0, 4);

        return view('main.lleTeacher', compact('teachers', 'weekCount', 'currentWeek', 'semesterStarts'));
    }

    public function facultyDisciplinesIndex() {
        $faculties = Faculty::all()->sortBy('sorting_order')->toArray();

        return view('main.facultyDisciplines', compact('faculties'));
    }

    public function fillBlankAuds() {
        $weekCount = Calendar::WeekCount();
        $buildings = Building::all()->sortBy('name');
        $rings = Ring::all()->toArray();
        $semesterStarts = ConfigOption::SemesterStarts();


        $audsWithBuilding = DB::table('auditoriums')
            ->join('buildings', 'auditoriums.building_id', '=', 'buildings.id')
            ->select('auditoriums.id as id', 'auditoriums.name as name',
                'buildings.id as buildingsId', 'buildings.name as buildingsName')
            ->get();

        $audsWithBuilding = $audsWithBuilding->groupBy('buildingsId')->toArray();

        $chap = "false";

        return view('main.fillBlankAuds', compact('weekCount', 'buildings', 'audsWithBuilding', 'rings', 'semesterStarts', 'chap'));
    }

    public function fillBlankAudsChap() {
        $weekCount = Calendar::WeekCount();
        $buildings = Building::all()->sortBy('name');
        $rings = Ring::all()->toArray();
        $semesterStarts = ConfigOption::SemesterStarts();


        $audsWithBuilding = DB::table('auditoriums')
            ->join('buildings', 'auditoriums.building_id', '=', 'buildings.id')
            ->select('auditoriums.id as id', 'auditoriums.name as name',
                'buildings.id as buildingsId', 'buildings.name as buildingsName')
            ->get();

        $audsWithBuilding = $audsWithBuilding->groupBy('buildingsId')->toArray();

        $chap = "true";

        return view('main.fillBlankAuds', compact('weekCount', 'buildings', 'audsWithBuilding', 'rings', 'semesterStarts', 'chap'));
    }
}
