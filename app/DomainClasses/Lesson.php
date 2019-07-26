<?php

namespace App\DomainClasses;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lesson extends Model
{
    public $timestamps = false;

    public static function GetDailyBuildingLessons($calendarId, $buildingId)
    {
        $result = DB::table('lessons')
            ->where('calendar_id', '=', $calendarId)
            ->where('auditoriums.building_id', '=', $buildingId)
            ->where('state', '=', 1)
            ->join('discipline_teacher as tfd', 'discipline_teacher_id', '=', 'tfd.id')
            ->join('disciplines', 'tfd.discipline_id', '=', 'disciplines.id')
            ->join('teachers', 'teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'student_group_id', '=', 'student_groups.id')
            ->join('rings', 'ring_id', '=', 'rings.id')
            ->join('auditoriums', 'auditorium_id', '=', 'auditoriums.id')
            ->select('lessons.id', 'rings.time', 'disciplines.name as disc_name',
                'teachers.fio', 'auditoriums.name as aud_name',
                'student_groups.name as group_name')
            ->orderBy('rings.time')
            ->get();
        $result->map(function ($lesson) {
            $lesson->time = substr($lesson->time, 0, 5);
        });
        return $result;
    }

    public static function GetDailyTFDLessons($disciplineTeacherIds, $calendarId)
    {
        if ($calendarId == "")
        {
            return collect();
        }

        $result = DB::table('lessons')
            ->join('discipline_teacher as tfd', 'lessons.discipline_teacher_id', '=', 'tfd.id')
            ->join('disciplines', 'tfd.discipline_id', '=', 'disciplines.id')
            ->join('teachers', 'tfd.teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->select('lessons.id', 'rings.time', 'disciplines.name as disc_name',
                'teachers.fio', 'auditoriums.name as aud_name',
                'student_groups.name as group_name')

            ->where('lessons.calendar_id', '=', $calendarId)
            ->where('lessons.state', '=', 1)
            ->whereIn('lessons.discipline_teacher_id', $disciplineTeacherIds)
            ->orderBy('rings.time')
            ->get();
        $result->map(function ($lesson) {
            $lesson->time = substr($lesson->time, 0, 5);
        });
        return $result;
    }

    public static function GetWeekTFDLessons($disciplineTeacherIds, $week)
    {
        $weekCalendarIds = Calendar::IdsFromWeek($week);
        return DB::table('lessons')
            ->whereIn('calendar_id', $weekCalendarIds)
            ->where('lessons.state', '=', 1)
            ->whereIn('discipline_teacher_id', $disciplineTeacherIds)
            ->join('discipline_teacher as tfd', 'discipline_teacher_id', '=', 'tfd.id')
            ->join('disciplines', 'tfd.discipline_id', '=', 'disciplines.id')
            ->join('teachers', 'teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'student_group_id', '=', 'student_groups.id')
            ->join('rings', 'ring_id', '=', 'rings.id')
            ->join('auditoriums', 'auditorium_id', '=', 'auditoriums.id')
            ->join('calendars', 'calendar_id', '=', 'calendars.id')
            ->select('lessons.id', 'rings.time', 'disciplines.name as disc_name',
                'teachers.fio', 'auditoriums.name as aud_name',
                'student_groups.name as group_name', 'calendars.date')
            ->orderBy('calendars.date')
            ->orderBy('rings.time')
            ->get();
    }

    public static function GetTFDLessons($disciplineTeacherIds)
    {
        return DB::table('lessons')
            ->where('lessons.state', '=', 1)
            ->whereIn('discipline_teacher_id', $disciplineTeacherIds)
            ->join('discipline_teacher as tfd', 'discipline_teacher_id', '=', 'tfd.id')
            ->join('disciplines', 'tfd.discipline_id', '=', 'disciplines.id')
            ->join('teachers', 'teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'student_group_id', '=', 'student_groups.id')
            ->join('rings', 'ring_id', '=', 'rings.id')
            ->join('auditoriums', 'auditorium_id', '=', 'auditoriums.id')
            ->join('calendars', 'calendar_id', '=', 'calendars.id')
            ->select('lessons.id', 'rings.time', 'disciplines.name as disc_name',
                'teachers.fio', 'auditoriums.name as aud_name',
                'student_groups.name as group_name', 'calendars.date',
                'tfd.id as tfd_id')
            ->orderBy('calendars.date')
            ->orderBy('rings.time')
            ->get();
    }

    public static function GetTFDLessonsQuery($disciplineTeacherIds)
    {
        return DB::table('lessons')
            ->where('lessons.state', '=', 1)
            ->whereIn('discipline_teacher_id', $disciplineTeacherIds)
            ->join('discipline_teacher as tfd', 'discipline_teacher_id', '=', 'tfd.id')
            ->join('disciplines', 'tfd.discipline_id', '=', 'disciplines.id')
            ->join('teachers', 'teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'student_group_id', '=', 'student_groups.id')
            ->join('rings', 'ring_id', '=', 'rings.id')
            ->join('auditoriums', 'auditorium_id', '=', 'auditoriums.id')
            ->join('calendars', 'calendar_id', '=', 'calendars.id')
            ->select('lessons.id', 'rings.time', 'disciplines.name as disc_name',
                'teachers.fio', 'auditoriums.name as aud_name',
                'student_groups.name as group_name', 'calendars.date',
                'tfd.id as tfd_id');
    }

    public static function GetWeekTFDLessons_OldAPI($disciplineTeacherIds, $week)
    {
        $weekCalendarIds = Calendar::IdsFromWeek($week);
        return DB::table('lessons')
            ->whereIn('calendar_id', $weekCalendarIds)
            ->where('lessons.state', '=', 1)
            ->whereIn('discipline_teacher_id', $disciplineTeacherIds)
            ->join('discipline_teacher as tfd', 'discipline_teacher_id', '=', 'tfd.id')
            ->join('disciplines', 'tfd.discipline_id', '=', 'disciplines.id')
            ->join('teachers', 'teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'student_group_id', '=', 'student_groups.id')
            ->join('rings', 'ring_id', '=', 'rings.id')
            ->join('auditoriums', 'auditorium_id', '=', 'auditoriums.id')
            ->join('calendars', 'calendar_id', '=', 'calendars.id')
            ->select('lessons.id', 'rings.time', 'disciplines.name as disc_name',
                'teachers.fio', 'auditoriums.name as aud_name',
                'student_groups.name as group_name', 'calendars.date',
                'tfd.id as tfd_id')
            ->orderBy('calendars.date')
            ->orderBy('rings.time')
            ->get();
    }

    public static function GetMonthStatByTfdId($id)
    {
        $result = array();
        $ids = array();
        $ids[] = $id;
        $tfdLessons = Lesson::GetTFDLessonsQuery($ids)->get();
        $result["count"] = count($tfdLessons);
        $tfdLessons->map(function ($lesson) use (&$result) {
            $lessonDate = Carbon::createFromFormat('Y-m-d', $lesson->date);
            $month = $lessonDate->month;
            if (!array_key_exists($month, $result))
            {
                $result[$month] = 2;
            }
            else
            {
                $result[$month] = $result[$month] + 2;
            }
        });
        return $result;
    }

    public static function GetScheduleHoursByTfdId($tfdId)
    {
        $lessons = DB::table('lessons')
            ->where('lessons.state', '=', 1)
            ->where('discipline_teacher_id', '=', $tfdId)
            ->count();
        return $lessons*2;
    }

    public static function GetLessonDow($lessonDate)
    {
        return Carbon::createFromFormat('Y-m-d', $lessonDate)->isoFormat('E');
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    public function ring()
    {
        return $this->belongsTo(Ring::class);
    }

    public function auditorium()
    {
        return $this->belongsTo(Auditorium::class);
    }

//    public function schedule_notes()
//    {
//        return $this->hasMany(ScheduleNote::class);
//    }

    public function Event()
    {
        return $this->belongsTo(LessonLogEvent::class);
    }
}
