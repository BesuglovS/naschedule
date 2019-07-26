<?php

namespace App\Http\Controllers;

use App\DomainClasses\Student;
use App\DomainClasses\StudentGroup;
use Illuminate\Http\Request;

class StudentGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $studentGroups = StudentGroup::all()->sortBy('name');

        return view('studentGroups.index', compact('studentGroups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('studentGroups.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newStudentGroup = new StudentGroup();
        $newStudentGroup->name = $request->name;
        $newStudentGroup->save();

        return redirect('/studentGroups');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StudentGroup  $studentGroup
     * @return \Illuminate\Http\Response
     */
    public function show(StudentGroup $studentGroup)
    {
        $groupStudents = $studentGroup->students->sort(function($a, $b) {
            if($a->f === $b->f) {
                if($a->i === $b->i) {
                    if($a->o === $b->o) {
                        return 0;
                    }
                    return $a->o < $b->o ? -1 : 1;
                }
                return $a->i < $b->i ? -1 : 1;
            }
            return $a->f < $b->f ? -1 : 1;
        });

        $students = Student::all();

        return view('studentGroups.show', compact('studentGroup', 'groupStudents', 'students'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StudentGroup  $studentGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentGroup $studentGroup)
    {
        return view('studentGroups.edit', compact('studentGroup'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StudentGroup  $studentGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentGroup $studentGroup)
    {
        $studentGroup->name = $request->name;

        $studentGroup->save();

        return redirect('studentGroups');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StudentGroup  $studentGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentGroup $studentGroup)
    {
        $studentGroupDisciplinesCount = $studentGroup->disciplines->count();
        $studentGroupStudentsCount = $studentGroup->students->count();

        if ($studentGroupDisciplinesCount !== 0)
        {
            return back()->with('error', 'Нельзя удалить класс (' . $studentGroup->name . '). Ему назначены дисциплины.');
        }

        if ($studentGroupStudentsCount !== 0)
        {
            return back()->with('error', 'Нельзя удалить класс (' . $studentGroup->name . '). В него включены ученики.');
        }

        StudentGroup::destroy($studentGroup->id);
        return redirect('studentGroups');
    }
}
