<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Faculty;
use App\DomainClasses\Lesson;
use App\DomainClasses\LessonLogEvent;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WeekController extends Controller
{
    public function index() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();

        $weeks = array();
        for($w = 1; $w <= $weekCount; $w++) {
            $start = $css->clone();
            $end = $start->clone()->addDays(6);
            $weeks[$w] = $start->format("d.m") . " - " . $end->format('d.m');

            $css = $css->addWeek();
        }

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);

        return view('week.index', compact('faculties', 'weekCount', 'weeks', 'currentWeek'));
    }

    public function copyWeekSchedule(Request $request) {
        $input = $request->all();
        $user = Auth::user();

        if ((!isset($input['facultyId'])) || (!isset($input['fromWeek'])) || (!isset($input['toWeek'])) || (!isset($input['dows'])))
        {
            return array("error" => "facultyId, fromWeek, toWeek и dows обязательные параметры");
        }

        $facultyId = $input["facultyId"];
        $fromWeek = $input["fromWeek"];
        $toWeek = $input["toWeek"];
        $dows = explode('|', $input["dows"]);

        $facultyGroups = Faculty::GetStudentGroupIdsFromFacultyId($facultyId);
        $fromCalendarIds = Calendar::IdsFromDowsAndWeek($dows, $fromWeek);

        $weekFacultyLessons = DB::table('lessons')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->where('lessons.state', '=', 1)
            ->wherein('student_groups.id', $facultyGroups)
            ->whereIn('lessons.calendar_id', $fromCalendarIds)
            ->select('lessons.*', 'calendars.date as lessonDate')
            ->get();

        $ss = Carbon::parse(ConfigOption::SemesterStarts());
        $css = $ss->startOfWeek();

        foreach ($weekFacultyLessons as $lesson) {
            $lessonDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $lesson->lessonDate));

            if (in_array($lessonDow, $dows)) {
                $newLesson = new Lesson();
                $newLesson->state = 1;
                $newLesson->discipline_teacher_id = $lesson->discipline_teacher_id;
                $newLesson->ring_id = $lesson->ring_id;
                $newLesson->auditorium_id = $lesson->auditorium_id;

                $newCalendarId = Calendar::IdFromDowAndWeek($lessonDow, $toWeek);

                if ($newCalendarId !== "") {
                    $newLesson->calendar_id = $newCalendarId;

                    $newLesson->save();

                    $lle = new LessonLogEvent();
                    $lle->old_lesson_id = 0;
                    $lle->new_lesson_id = $newLesson->id;
                    $lle->date_time = Carbon::now()->format('Y-m-d H:i:s');
                    $lle->public_comment = "";
                    $lle->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "") . "copy";
                    $lle->save();
                }
            }
        }

        return array("success" => "OK");
    }

    public function deleteWeekSchedule(Request $request) {
        $input = $request->all();
        $user = Auth::user();

        if ((!isset($input['facultyId'])) || (!isset($input['week'])) || (!isset($input['dows'])))
        {
            return array("error" => "facultyId, week и dows обязательные параметры");
        }

        $facultyId = $input["facultyId"];
        $week = $input["week"];
        $dows = explode('|', $input["dows"]);

        $facultyGroups = Faculty::GetStudentGroupIdsFromFacultyId($facultyId);
        $fromCalendarIds = Calendar::IdsFromDowsAndWeek($dows, $week);

        $weekFacultyLessons = DB::table('lessons')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->where('lessons.state', '=', 1)
            ->wherein('student_groups.id', $facultyGroups)
            ->whereIn('lessons.calendar_id', $fromCalendarIds)
            ->select('lessons.*', 'calendars.date as lessonDate')
            ->get();

        foreach ($weekFacultyLessons as $lesson) {
            $lessonDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $lesson->lessonDate));

            if (in_array($lessonDow, $dows)) {
                $l = Lesson::find($lesson->id);
                $l->state = 0;
                $l->save();

                $lle = new LessonLogEvent();
                $lle->old_lesson_id = $lesson->id;
                $lle->new_lesson_id = 0;
                $lle->date_time = Carbon::now()->format('Y-m-d H:i:s');
                $lle->public_comment = "";
                $lle->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "") . "delete";
                $lle->save();
            }
        }

        return array("success" => "OK");
    }
}
