<?php

namespace App\Http\Controllers;

use App\DomainClasses\Auditorium;
use App\DomainClasses\Building;
use App\DomainClasses\Calendar;
use App\DomainClasses\Faculty;
use App\DomainClasses\Ring;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use App\DomainClasses\TeacherGroup;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
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

    function startsWith ($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }
}
