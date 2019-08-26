<?php

namespace App\Http\Controllers;

use App\DomainClasses\Teacher;
use App\DomainClasses\TeacherGroup;
use Illuminate\Http\Request;

class TeacherGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $teacherGroups = TeacherGroup::all()->sortBy('name');

        return view('teacherGroups.index', compact('teacherGroups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('teacherGroups.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newTeacherGroup = new TeacherGroup();
        $newTeacherGroup->name = $request->name;
        $newTeacherGroup->save();

        return redirect('/teacherGroups');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TeacherGroup  $teacherGroup
     * @return \Illuminate\Http\Response
     */
    public function show(TeacherGroup $teacherGroup)
    {
        $groupTeachers = $teacherGroup->teachers->sortBy('fio');

        $teachers = Teacher::all()->sortBy('fio');

        return view('teacherGroups.show', compact('teacherGroup', 'groupTeachers', 'teachers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TeacherGroup  $teacherGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(TeacherGroup $teacherGroup)
    {
        return  view('teacherGroups.edit', compact('teacherGroup'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TeacherGroup  $teacherGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TeacherGroup $teacherGroup)
    {
        $teacherGroup->name = $request->name;

        $teacherGroup->save();

        return redirect('teacherGroups');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TeacherGroup  $teacherGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(TeacherGroup $teacherGroup)
    {
        $teacherGroupTeachersCount = $teacherGroup->teachers->count();

        if ($teacherGroupTeachersCount !== 0)
        {
            return back()->with('error', 'Нельзя удалить группу (' . $teacherGroup->name . '). В ней есть учителя.');
        }

        TeacherGroup::destroy($teacherGroup->id);
        return redirect('teacherGroups');
    }
}
