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
}
