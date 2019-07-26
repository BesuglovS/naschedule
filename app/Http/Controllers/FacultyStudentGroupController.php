<?php

namespace App\Http\Controllers;

use App\DomainClasses\Faculty;
use App\DomainClasses\Student;
use App\DomainClasses\StudentGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacultyStudentGroupController extends Controller
{
    public function destroy(Request $request, int $faculty_student_group_id)
    {
        DB::table('faculty_student_group')
            ->where('id', $faculty_student_group_id)
            ->take(1)
            ->delete();

        return redirect('/faculties/' . $request->faculty_id);
    }

    public function store(Request $request)
    {
        $faculty = Faculty::find($request->faculty_id);
        $studentGroup = StudentGroup::find($request->student_group_id);

        $faculty->student_groups()->attach($studentGroup);

        return redirect('/faculties/' . $request->faculty_id);
    }
}
