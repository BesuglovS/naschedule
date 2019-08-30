<?php

namespace App\Http\Controllers;

use App\DomainClasses\Discipline;
use App\DomainClasses\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $teachers = Teacher::all()->sortBy('fio');

        return view('teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newTeacher = new Teacher();
        $newTeacher->fio = $request->fio;
        $newTeacher->phone = $request->phone;
        $newTeacher->save();

        return redirect('/teachers');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function show(Teacher $teacher)
    {
        $disciplineIdsWithTeachers = DB::table('disciplines')
            ->join('discipline_teacher', 'discipline_teacher.discipline_id' , '=', 'disciplines.id')
            ->select('disciplines.id')
            ->get()
            ->pluck('id');

        $disciplines = DB::table('disciplines')
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->select('disciplines.*', 'student_groups.name as groupName')
            ->whereNotIn('disciplines.id', $disciplineIdsWithTeachers)
            ->orderBy('groupName', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $disciplines = $disciplines->sort(function($a, $b) {
            if ($a->groupName == $b->groupName) {
                return ($a->name < $b->name) ? -1 : 1;
            }

            $num1 = explode(" ", $a->groupName)[0];
            $num2 = explode(" ", $b->groupName)[0];

            if ($num1 == $num2)
            {
                return $a->groupName < $b->groupName ? -1 : 1;
            }
            else
            {
                return ($num1 < $num2) ? -1 : 1;
            }
        });

        $teacherDisciplineIds = $teacher->disciplines->pluck('id');

        $teacherDisciplines = DB::table('disciplines')
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->join('discipline_teacher', 'discipline_teacher.discipline_id' , '=', 'disciplines.id')
            ->whereIn('disciplines.id', $teacherDisciplineIds)
            ->select('disciplines.*', 'student_groups.name as groupName', 'discipline_teacher.id as discipline_teacher_id')
            ->orderBy('groupName', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $teacherDisciplines = $teacherDisciplines->sort(function($a, $b) {
            if ($a->groupName == $b->groupName) {
                return ($a->name < $b->name) ? -1 : 1;
            }

            $num1 = explode(" ", $a->groupName)[0];
            $num2 = explode(" ", $b->groupName)[0];

            if ($num1 == $num2)
            {
                return $a->groupName < $b->groupName ? -1 : 1;
            }
            else
            {
                return ($num1 < $num2) ? -1 : 1;
            }
        });

        return view('teachers.show', compact('teacher', 'disciplines', 'teacherDisciplines'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function edit(Teacher $teacher)
    {
        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Teacher $teacher)
    {
        $teacher->fio = $request->fio;
        $teacher->phone = $request->phone;
        $teacher->save();

        return redirect('teachers');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Teacher $teacher)
    {
        $teacherDisciplinesCount = $teacher->disciplines->count();

        if ($teacherDisciplinesCount !== 0)
        {
            return back()->with('error', 'Нельзя удалить учителя ( ' . $teacher->fio . ' ). Ему назначены дисциплины.');
        }

        Teacher::destroy($teacher->id);
        return redirect('teachers');
    }
}
