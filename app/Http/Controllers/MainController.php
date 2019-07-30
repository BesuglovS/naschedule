<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
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
        return view('main.groupSchedule');
    }

    public function facultySchedule()
    {
        return view('main.facultySchedule');
    }

    public function teacherSchedule()
    {
        $teachers = Teacher::all()->sortBy('fio');

        $weekCount = Calendar::WeekCount();

        return view('main.teacherSchedule', compact('teachers', 'weekCount'));
    }

    public function groupSession()
    {
        $studentGroups = StudentGroup::allSorted()->toArray();
        $group_id = -1;

        $examGroupIds = DB::table('exams')
            ->join('disciplines', 'exams.discipline_id', '=', 'disciplines.id')
            ->select('disciplines.student_group_id')
            ->distinct()
            ->where('exams.is_active', '=', 1)
            ->pluck('disciplines.student_group_id');

        $studentGroups = array_filter($studentGroups, function($item) use ($examGroupIds) {
            $groupGroupsIds = StudentGroup::GetGroupsOfStudentFromGroup($item["id"]);

            $intersectCount = $examGroupIds->intersect($groupGroupsIds)->count();

            return ($intersectCount !== 0);
        });

        $studentGroups = new Collection($studentGroups);
        $studentGroups = $studentGroups->sort(function($a, $b) {

            $num1 = explode(" ", $a["name"])[0];
            $num2 = explode(" ", $b["name"])[0];

            if ($num1 == $num2)
            {
                if ($a["name"] == $b["name"]) return 0;
                return $a["name"] < $b["name"] ? -1 : 1;
            }
            else
            {
                return ($num1 < $num2) ? -1 : 1;
            }
        });

        return view('main.groupSession', compact('studentGroups', 'group_id'));
    }

    public function groupSessionWithId(int $group_id)
    {
        $studentGroups = StudentGroup::allSorted()->toArray();

        return view('main.groupSession', compact('studentGroups', 'group_id'));
    }
}
