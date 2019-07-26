<?php

namespace App\Http\Controllers;

use App\DomainClasses\Discipline;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisciplineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $disciplines = DB::table('disciplines')
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->leftJoin('discipline_teacher','disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->leftJoin('teachers','discipline_teacher.teacher_id', '=', 'teachers.id')
            ->select('disciplines.*', 'student_groups.name as groupName', 'teachers.fio')
            ->orderBy('student_groups.name', 'asc')
            ->orderBy('disciplines.name', 'asc')
            ->get();

        return view('disciplines.index', compact('disciplines'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $studentGroups = StudentGroup::all()->sortBy('name');

        return view('disciplines.create', compact('studentGroups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newDiscipline = new Discipline();
        $newDiscipline->name = $request->name;
        $newDiscipline->student_group_id = $request->student_group_id;

        $newDiscipline->attestation = "1";
        $newDiscipline->auditorium_hours = "0";
        $newDiscipline->auditorium_hours_per_week = "0";
        $newDiscipline->lecture_hours = "0";
        $newDiscipline->practical_hours = "0";

        $newDiscipline->save();

        return redirect('/disciplines');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Discipline  $discipline
     * @return \Illuminate\Http\Response
     */
    public function show(Discipline $discipline)
    {
        $discipline = DB::table('disciplines')
            ->where('disciplines.id', '=', $discipline->id)
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->select('disciplines.*', 'student_groups.name as groupName', 'teachers.fio as teacherFio',
                'discipline_teacher.id as discipline_teacher_id')
            ->first();

        $teachers = Teacher::all()->sortBy('fio');

        return view('disciplines.show', compact('discipline', 'teachers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Discipline  $discipline
     * @return \Illuminate\Http\Response
     */
    public function edit(Discipline $discipline)
    {
        $discipline = DB::table('disciplines')
            ->where('disciplines.id', '=', $discipline->id)
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->select('disciplines.*', 'student_groups.name as groupName')
            ->first();

        $studentGroups = StudentGroup::all();

        return view('disciplines.edit', compact('discipline', 'studentGroups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Discipline  $discipline
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discipline $discipline)
    {
        $discipline->name = $request->name;
        $discipline->attestation = $request->attestation;
        $discipline->student_group_id = $request->student_group_id;
        $discipline->save();

        return redirect('disciplines');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Discipline  $discipline
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discipline $discipline)
    {
        $disciplineTeachersCount = $discipline->teacher->count();

        if ($disciplineTeachersCount !== 0)
        {
            $discipline = DB::table('disciplines')
                ->where('disciplines.id', '=', $discipline->id)
                ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
                ->select('disciplines.*', 'student_groups.name as groupName')
                ->first();

            return back()->with('error', 'Нельзя удалить дисциплину (' .
                $discipline->groupName . ' ' . $discipline->name .
                '). Ей назначен учитель.');
        }

        Discipline::destroy($discipline->id);
        return redirect('disciplines');
    }
}
