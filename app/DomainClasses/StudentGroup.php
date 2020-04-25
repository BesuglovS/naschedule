<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StudentGroup extends Model
{
    public $timestamps = false;

    public static function allSorted()
    {
        $result = StudentGroup::all()->sort(function($a, $b) {

            $num1 = explode(" ", $a->name)[0];
            $num2 = explode(" ", $b->name)[0];

            if ($num1 == $num2)
            {
                if ($a->name == $b->name) return 0;
                return $a->name < $b->name ? -1 : 1;
            }
            else
            {
                return ($num1 < $num2) ? -1 : 1;
            }
        });

        return $result;
    }

    public static function NameFromId($groupId)
    {
        $name = $studentIds = DB::table('student_groups')
            ->where('id', '=', $groupId)
            ->select('name')
            ->get()
            ->map(function($item) { return $item->name;});
        return (count($name) > 0) ? $name[0] : "";
    }

    public static function StudentIdsFromGroupIds($groupIds)
    {
        return DB::table('student_student_group')
            ->whereIn('student_student_group.student_group_id', $groupIds)
            ->select('student_student_group.student_id')
            ->get()
            ->map(function($item) { return $item->student_id;});
    }

    public static function StudentIdsFromGroupId($groupId)
    {
        return DB::table('student_student_group')
            ->where('student_student_group.student_group_id', '=', $groupId)
            ->select('student_student_group.student_id')
            ->get()
            ->map(function($item) { return $item->student_id;});
    }

    public static function FacultiesGroups()
    {
        return DB::table('student_groups')
            ->join('faculty_student_group', 'faculty_student_group.student_group_id', '=', 'student_groups.id')
            ->join('faculties', 'faculties.id', '=', 'faculty_student_group.faculty_id')
            ->select(
                'student_groups.id as groupId',
                'student_groups.name as name',
                'faculty_student_group.faculty_id as facultyId',
                'faculties.sorting_order as facultiesSortingOrder'
            )
            ->orderBy('faculties.sorting_order')
            ->orderBy('student_groups.name')
            ->get();
    }

    public static function FacultyGroupsFromGroupName($groupName)
    {
        $studentIds = DB::table('student_student_group')
            ->join('student_groups', 'student_student_group.student_group_id', '=', 'student_groups.id')
            ->where('student_groups.name', '=', $groupName)
            ->select('student_id')
            ->get()
            ->map(function($item) { return $item->student_id;});
        $groupIds = DB::table('student_student_group')
            ->whereIn('student_id', $studentIds)
            ->select('student_group_id')
            ->get()
            ->map(function($item) { return $item->student_group_id;})
            ->unique();

        return DB::table('student_groups')
            ->join('faculty_student_group', 'faculty_student_group.student_group_id', '=', 'student_groups.id')
            ->join('faculties', 'faculties.id', '=', 'faculty_student_group.faculty_id')
            ->select(
                'student_groups.id as groupId',
                'student_groups.name as name',
                'faculty_student_group.faculty_id as facultyId',
                'faculties.sorting_order as facultiesSortingOrder'
            )
            ->whereIn('student_groups.id', $groupIds)
            ->orderBy('faculties.sorting_order')
            ->orderBy('student_groups.name')
            ->get();
    }

    public function disciplines()
    {
        return $this->hasMany(Discipline::class);
    }


    public function faculties()
    {
        return $this->belongsToMany(Faculty::class, "faculty_student_group")->withPivot('id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, "student_student_group")->withPivot('id');
    }

    public static function mainStudentGroups()
    {
        $groups = StudentGroup::orderBy('name')->get();
//        $result = array();
//        for ($i = 1; $i < count($groups); $i++) {
//            if ((strpos($groups[$i],'+Н') == false) &&
//                (strpos($groups[$i],' + ') == false) &&
//                (strpos($groups[$i],'-А-') == false) &&
//                (strpos($groups[$i],'-Н-') == false) &&
//                (strpos($groups[$i],'-Ф-') == false) &&
//                (strpos($groups[$i],'I') == false))
//            {
//                $result[] = $groups[$i];
//            }
//        }

        //dd($groups);

        return $groups;
    }

    public static function GetGroupsOfStudentFromGroup($groupId)
    {
        $studentIds = DB::table('student_student_group')
            ->where('student_group_id', '=', $groupId)
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

    public static function GetGroupsOfStudentFromGroups($groupIds)
    {
        $studentIds = DB::table('student_student_group')
            ->whereIn('student_group_id', $groupIds)
            ->select('student_id')
            ->get()
            ->map(function($item) { return $item->student_id;});

        $groupIds = DB::table('student_student_group')
            ->whereIn('student_id', $studentIds)
            ->select('student_group_id')
            ->get()
            ->map(function($item) { return $item->student_group_id;})
            ->unique()
            ->toArray();

        sort($groupIds);

        return $groupIds;
    }

    public static function GetGroupsOfStudentFromGroupsConnected($groupIds)
    {
        $result = array();

        foreach($groupIds as $groupId) {
            $studentIds = DB::table('student_student_group')
                ->where('student_group_id', '=', $groupId)
                ->select('student_id')
                ->get()
                ->map(function($item) { return $item->student_id;});

            $groupIds = DB::table('student_student_group')
                ->whereIn('student_id', $studentIds)
                ->select('student_group_id')
                ->get()
                ->map(function($item) { return $item->student_group_id;})
                ->unique()
                ->toArray();

            foreach($groupIds as $id) {
                if (!array_key_exists($id, $result)) {
                    $result[$id] = array();
                }

                $result[$id][] = $groupId;
            }
        }

        return $result;
    }
}
