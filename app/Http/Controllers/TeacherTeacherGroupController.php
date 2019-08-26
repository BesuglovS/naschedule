<?php

namespace App\Http\Controllers;

use App\DomainClasses\Teacher;
use App\DomainClasses\TeacherGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherTeacherGroupController extends Controller
{
    public function destroy(Request $request, int $teacher_teacher_group_id)
    {
        DB::table('teacher_teacher_group')
            ->where('id', $teacher_teacher_group_id)
            ->take(1)
            ->delete();

        return redirect('/teacherGroups/' . $request->teacher_group_id);
    }

    public function store(Request $request)
    {
        $teacher = Teacher::find($request->teacher_id);
        $teacherGroup = TeacherGroup::find($request->teacher_group_id);

        $teacherGroup->teachers()->attach($teacher);

        return redirect('/teacherGroups/' . $request->teacher_group_id);
    }

    public function get(Request $request)
    {
        $teacher_group_id = $request->teacher_group_id;

        $teacher_group = TeacherGroup::find($teacher_group_id);

        $teachers = $teacher_group->teachers;

        return $teachers;
    }
}
