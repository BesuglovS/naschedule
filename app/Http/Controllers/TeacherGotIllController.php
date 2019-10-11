<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Ring;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherGotIllController extends Controller
{
    public function index() {
        $teachers = Teacher::all()->sortBy('fio');
        $weekCount = Calendar::WeekCount();

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);

        $semesterStarts = ConfigOption::SemesterStarts();
        $semesterStarts = mb_substr($semesterStarts, 8, 2) . '.' . mb_substr($semesterStarts, 5, 2) . '.' . mb_substr($semesterStarts, 0, 4);

        $calendars = Calendar::all()->sortBy('date');

        return view('main.teacherGotIll', compact('teachers', 'weekCount', 'currentWeek', 'semesterStarts', 'calendars'));
    }

    public function loadIllInfo(Request $request) {
        $input = $request->all();
        $user = Auth::user();

        if((!isset($input['teacherId'])) || (!isset($input['calendarFromId'])) || (!isset($input['calendarToId'])))
        {
            return array("error" => "teacherId, calendarFromId и calendarToId обязательные параметры");
        }
        $teacherId = $input['teacherId'];
        $calendarFromId = $input['calendarFromId'];
        $calendarToId = $input['calendarToId'];

        $rings = Ring::all()->toArray();
        usort($rings, function($a, $b) {
            $aTime = Carbon::createFromFormat('H:i:s', $a['time'])->format('Y-m-d H:i:s');
            $bTime = Carbon::createFromFormat('H:i:s', $b['time'])->format('Y-m-d H:i:s');

            if ($aTime === $bTime) return 0;

            return ($aTime < $bTime) ? -1 : 1;
        });
        $ringsById = array();
        for($i = 0; $i < count($rings); $i++) {
            $rings[$i]['dayOrder'] = $i;
            $ringsById[$rings[$i]["id"]] = $rings[$i];
        }

        $calendarIds = Calendar::CalendarIdsBetweenTwoIds($calendarFromId, $calendarToId);

        $teacherLessons = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('lessons.state', '=', 1)
            ->whereIn('lessons.calendar_id', $calendarIds)
            ->where('teachers.id', '=', $teacherId)
            ->select('lessons.*',
                'teachers.id as teachersId',
                'teachers.fio as teachersFio',
                'disciplines.name as disciplinesName',
                'student_groups.id as studentGroupsId',
                'student_groups.name as studentGroupsName',
                'calendars.date as calendarsDate',
                'rings.time as ringsTime'
            )
            ->get();

        $result = array();

        foreach ($teacherLessons as $teacherLesson) {
            $studentGroupIds = StudentGroup::GetGroupsOfStudentFromGroup($teacherLesson->studentGroupsId);

            $groupDayLessons = DB::table('lessons')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->where('lessons.state', '=', 1)
                ->where('lessons.calendar_id', '=', $teacherLesson->calendar_id)
                ->whereIn('student_groups.id', $studentGroupIds)
                ->select('lessons.*',
                    'teachers.id as teachersId',
                    'teachers.fio as teachersFio',
                    'disciplines.name as disciplinesName',
                    'student_groups.name as studentGroupsName',
                    'calendars.date as calendarsDate',
                    'rings.time as ringsTime'
                )
                ->get()
                ->toArray();

            $lessonDayOrder = $ringsById[$teacherLesson->ring_id]['dayOrder'];

            $earlierLessons = array_filter($groupDayLessons, function($lesson, $k) use ($teacherLesson, $lessonDayOrder, $ringsById) {
                return $lesson->id !== $teacherLesson->id && $ringsById[$lesson->ring_id]['dayOrder'] < $lessonDayOrder;
            }, ARRAY_FILTER_USE_BOTH);
            $teacherLesson->earlierLessons = $earlierLessons;
            $teacherLesson->earlierLessonsExists = count($earlierLessons) !== 0;
            $earlierLessonsTeacherIds = array_values(array_unique(array_map(function ($lesson) { return $lesson->teachersId;}, $earlierLessons)));
            if (count($earlierLessonsTeacherIds) === 1 && $earlierLessonsTeacherIds[0] == $teacherLesson->teachersId) {
                $teacherLesson->earlierLessonsExists = false;
            }

            $latterLessons = array_filter($groupDayLessons, function($lesson, $k) use ($teacherLesson, $lessonDayOrder, $ringsById) {
                return $lesson->id !== $teacherLesson->id && $ringsById[$lesson->ring_id]['dayOrder'] > $lessonDayOrder;
            }, ARRAY_FILTER_USE_BOTH);
            $teacherLesson->latterLessons = $latterLessons;
            $latterLessonsTeacherIds = array_values(array_unique(array_map(function ($lesson) { return $lesson->teachersId;}, $latterLessons)));
            $teacherLesson->latterLessonsExists = count($latterLessons) !== 0;
            if (count($latterLessonsTeacherIds) === 1 && $latterLessonsTeacherIds[0] == $teacherLesson->teachersId) {
                $teacherLesson->latterLessonsExists = false;
            }

            $carbonLessonDate = Carbon::createFromFormat('Y-m-d', $teacherLesson->calendarsDate);

            // today + 6 next days lessons
            $allCalendars = Calendar::all()->toArray();
            $nextWeekCalendarIds = array_filter($allCalendars, function($v, $k) use ($carbonLessonDate) {
                $carbonDate = Carbon::createFromFormat('Y-m-d', $v['date']);
                $diff = $carbonLessonDate->diffInDays($carbonDate, false);

                return ($diff >= 0) && ($diff <= 6);
            }, ARRAY_FILTER_USE_BOTH);

            dd($nextWeekCalendarIds);

            //$teacherLesson->groupLessons = $groupDayLessons;
            unset($teacherLesson->earlierLessons);
            unset($teacherLesson->latterLessons);
        }

        return $teacherLessons;
    }
}
