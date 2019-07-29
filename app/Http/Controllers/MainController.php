<?php

namespace App\Http\Controllers;

use App\DomainClasses\StudentGroup;
use Illuminate\Routing\Controller;

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
        return view('main.teacherSchedule');
    }

    public function groupSession()
    {
        $studentGroups = StudentGroup::allSorted()->toArray();
        $group_id = -1;

        return view('main.groupSession', compact('studentGroups', 'group_id'));
    }

    public function groupSessionWithId(int $group_id)
    {
        $studentGroups = StudentGroup::allSorted()->toArray();

        return view('main.groupSession', compact('studentGroups', 'group_id'));
    }
}
