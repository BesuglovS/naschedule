<?php

namespace App\Http\Controllers;

use App\DomainClasses\Faculty;
use App\DomainClasses\StudentGroup;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $faculties = Faculty::all()->sortBy('sorting_order');

        return view('faculties.index', compact('faculties'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('faculties.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newFaculty = new Faculty();
        $newFaculty->name = $request->name;
        $newFaculty->letter = $request->letter;
        $newFaculty->sorting_order = $request->sorting_order;

        $newFaculty->schedule_signing_title = " ";
        $newFaculty->dean_signing_schedule = " ";
        $newFaculty->session_signing_title = " ";
        $newFaculty->dean_signing_session_schedule = " ";
        $newFaculty->save();

        return redirect('/faculties');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Faculty  $faculty
     * @return \Illuminate\Http\Response
     */
    public function show(Faculty $faculty)
    {
        $studentGroups = StudentGroup::all()->sortBy('name');

        $facultyStudentGroups = $faculty->student_groups->sortBy('name');

        return view('faculties.show', compact('faculty', 'studentGroups', 'facultyStudentGroups'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Faculty  $faculty
     * @return \Illuminate\Http\Response
     */
    public function edit(Faculty $faculty)
    {
        return view('faculties.edit', compact('faculty'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Faculty  $faculty
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Faculty $faculty)
    {
        $faculty->name = $request->name;
        $faculty->letter = $request->letter;
        $faculty->sorting_order = $request->sorting_order;
        $faculty->save();

        return redirect('faculties');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Faculty  $faculty
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faculty $faculty)
    {
        $facultyStudentGroupsCount = $faculty->student_groups->count();

        if ($facultyStudentGroupsCount !== 0)
        {
            return back()->with('error', 'Нельзя удалить параллель (' . $faculty->name . '). В ней есть классы.');
        }

        Faculty::destroy($faculty->id);
        return redirect('faculties');
    }
}
