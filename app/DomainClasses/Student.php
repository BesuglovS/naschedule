<?php

namespace App\DomainClasses;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    public $timestamps = false;

    public static function GetHappy()
    {
        $result = array();
        $list = DB::table('students')
            ->where('expelled', '=', 0)
            ->get()
            ->toArray();
        $carbonNow = Carbon::now();
        foreach ($list as $student) {
            $birthDate = Carbon::createFromFormat('Y-m-d', $student->birth_date);
            if (($birthDate->month == $carbonNow->month) &&
                ($birthDate->day == $carbonNow->day))
            {
                $result[] = $student;
            }
        }
        return $result;
    }

    public function student_groups()
    {
        return $this->belongsToMany(StudentGroup::class, "student_student_group")->withPivot('id');
    }

    public static function ListOfNotExpelled()
    {
        return DB::table('students')
            ->where('expelled', '=', 0)
            ->get();
    }
}
