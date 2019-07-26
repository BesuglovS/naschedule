<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Faculty extends Model
{
    public $timestamps = false;

    public static function FSGall()
    {
        return DB::table('faculty_student_group')->get();
    }

    public static function GetStudentGroupIdsFromFacultyId($facultyId)
    {
        $facultyGroupIds = DB::table('faculty_student_group')
            ->where('faculty_id', '=', $facultyId)
            ->select('student_group_id')
            ->get()
            ->map(function($item) { return $item->student_group_id;})
            ->toArray();

        $studentIds = DB::table('student_student_group')
            ->whereIn('student_group_id', $facultyGroupIds)
            ->select('student_id')
            ->get()
            ->map(function($item) { return $item->student_id;});

        $groupIds = DB::table('student_student_group')
            ->whereIn('student_id', $studentIds)
            ->select('student_group_id')
            ->get()
            ->map(function($item) { return $item->student_group_id;})
            ->unique();

        return $groupIds;
    }

    public function student_groups()
    {
        return $this->belongsToMany(StudentGroup::class, "faculty_student_group")->withPivot('id');
    }
}
