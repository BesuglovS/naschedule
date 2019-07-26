<?php

namespace App\Http\Controllers;

use App\DomainClasses\Student;
use App\DomainClasses\StudentGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentStudentGroupController extends Controller
{
    public function destroy(Request $request, int $student_student_group_id)
    {
        DB::table('student_student_group')
            ->where('id', $student_student_group_id)
            ->take(1)
            ->delete();

        return redirect('/studentGroups/' . $request->student_group_id);
    }

    public function store(Request $request)
    {
        $student = Student::find($request->student_id);
        $studentGroup = StudentGroup::find($request->student_group_id);

        $studentGroup->students()->attach($student);

        return redirect('/studentGroups/' . $request->student_group_id);
    }
}
