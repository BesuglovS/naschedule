<?php

namespace App\Http\Controllers;

use App\DomainClasses\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $students = Student::all()->sort(function ($a, $b) {
            if ($a->f === $b->f) {
                if ($a->i === $b->i) {
                    if ($a->o === $b->o) {
                        return 0;
                    }
                    return ($a->o < $b->o) ? -1 : 1;
                }
                return ($a->i < $b->i) ? -1 : 1;
            }
            return ($a->f < $b->f) ? -1 : 1;
        });

        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newStudent = new Student();
        $newStudent->f = $request->f;
        $newStudent->i = $request->i;
        $newStudent->o = $request->o;

        $newStudent->birth_date = "1900-01-01";
        $newStudent->zach_number = " ";
        $newStudent->address = " ";
        $newStudent->phone = " ";
        $newStudent->orders = " ";
        $newStudent->starosta = "0";
        $newStudent->n_factor = "0";
        $newStudent->paid_edu = "0";
        $newStudent->expelled = "0";

        $newStudent->save();


        return redirect('/students');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        $student->f = $request->f;
        $student->i = $request->i;
        $student->o = $request->o;
        $student->save();

        return redirect('students');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $student)
    {
        $studentGroupsCount = $student->student_groups->count();

        if ($studentGroupsCount !== 0)
        {
            return back()->with('error', 'Нельзя удалить ученика ( ' .
                $student->f . ' ' . $student->i . ' ' . $student->o .
                '). Он включён в класс.');
        }

        Student::destroy($student->id);
        return redirect('/students');
    }
}
