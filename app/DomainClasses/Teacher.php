<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Teacher extends Model
{
    public $timestamps = false;

    public function disciplines()
    {
        return $this->belongsToMany(Discipline::class, 'discipline_teacher')->withPivot('id');
    }

    public static function OrderByFio()
    {
        return DB::table('teachers')
            ->orderBy('fio', 'asc')
            ->get();
    }

    public static function IdAndFioList()
    {
        return DB::table('teachers')
            ->select('id', 'fio')
            ->orderBy('fio', 'asc')
            ->get();
    }

    public static function CalendarSchedule($teacherId, $calendarIds)
    {
        return DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('lessons.state', '=', 1)
            ->whereIn('lessons.calendar_id',  $calendarIds)
            ->where('teachers.id', '=', $teacherId)
            ->orderBy('rings.time')
            ->select('lessons.*',
                'teachers.id as teachersId',
                'teachers.fio as teachersFio',
                'disciplines.name as disciplinesName',
                'student_groups.id as studentGroupsId',
                'student_groups.name as studentGroupsName',
                'calendars.date as calendarsDate',
                'rings.time as ringsTime'
            )
            ->get()
            ->toArray();
    }

    public function teacher_groups()
    {
        return $this->belongsToMany(StudentGroup::class, "teacher_teacher_group")->withPivot('id');
    }
}
