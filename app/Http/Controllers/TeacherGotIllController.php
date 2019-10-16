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

        $teacherLessons = Teacher::CalendarSchedule($teacherId, $calendarIds);

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
            $teacherLesson->earlierLessonsExists = count($earlierLessons) !== 0;
            $earlierLessonsTeacherIds = array_values(array_unique(array_map(function ($lesson) { return $lesson->teachersId;}, $earlierLessons)));
            if (count($earlierLessonsTeacherIds) === 1 && $earlierLessonsTeacherIds[0] == $teacherLesson->teachersId) {
                $teacherLesson->earlierLessonsExists = false;
            }

            $latterLessons = array_filter($groupDayLessons, function($lesson, $k) use ($teacherLesson, $lessonDayOrder, $ringsById) {
                return $lesson->id !== $teacherLesson->id && $ringsById[$lesson->ring_id]['dayOrder'] > $lessonDayOrder;
            }, ARRAY_FILTER_USE_BOTH);
            $latterLessonsTeacherIds = array_values(array_unique(array_map(function ($lesson) { return $lesson->teachersId;}, $latterLessons)));
            $teacherLesson->latterLessonsExists = count($latterLessons) !== 0;
            if (count($latterLessonsTeacherIds) === 1 && $latterLessonsTeacherIds[0] == $teacherLesson->teachersId) {
                $teacherLesson->latterLessonsExists = false;
            }

            $carbonLessonDate = Carbon::createFromFormat('Y-m-d', $teacherLesson->calendarsDate);

            // today + 6 next days lessons
            $allCalendars = Calendar::all()->toArray();
            $nextWeekCalendars = array_filter($allCalendars, function($v, $k) use ($carbonLessonDate) {
                $carbonDate = Carbon::createFromFormat('Y-m-d', $v['date']);
                $diff = $carbonLessonDate->diffInDays($carbonDate, false);

                return ($diff >= 0) && ($diff <= 6);
            }, ARRAY_FILTER_USE_BOTH);

            $nextWeekCalendarIds = collect($nextWeekCalendars)->pluck('id')->toArray();

            $weekLessons = DB::table('lessons')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->where('lessons.state', '=', 1)
                ->whereIn('lessons.calendar_id', $nextWeekCalendarIds)
                ->where('student_groups.id', '=', $teacherLesson->studentGroupsId)
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

            $weekLessonsByCalendarId = array();

            foreach($weekLessons as $weekLesson) {
                if (!array_key_exists($weekLesson->calendar_id, $weekLessonsByCalendarId)) {
                    $weekLessonsByCalendarId[$weekLesson->calendar_id] = array();
                }

                $weekLessonsByCalendarId[$weekLesson->calendar_id][] = $weekLesson;
            }

            foreach($weekLessonsByCalendarId as $calendarId => $calendarLessons) {
                usort($calendarLessons, function($a, $b) {
                    $aValue = intval(mb_substr($a->ringsTime,0,2)) * 60 + intval(mb_substr($a->ringsTime,3,2));
                    $bValue = intval(mb_substr($b->ringsTime,0,2)) * 60 + intval(mb_substr($b->ringsTime,3,2));

                    if ($aValue === $bValue) return 0;
                    return ($aValue < $bValue) ? -1 : 1;
                });
                $weekLessonsByCalendarId[$calendarId] = $calendarLessons;
                $lessons = array($calendarLessons[0], end($calendarLessons));

                foreach($lessons as $lessonForExchange) {
                    $teacherId = $lessonForExchange->teachersId;

                    $sourceTeacherLessons = Teacher::CalendarSchedule($teacherId, array($lessonForExchange->calendar_id));

                    $srcPositiveDiff = false;
                    $srcNegativeDiff = false;
                    foreach($sourceTeacherLessons as $sourceTeacherLesson) {
                        $aValue = intval(mb_substr($lessonForExchange->ringsTime,0,2)) * 60 + intval(mb_substr($lessonForExchange->ringsTime,3,2));
                        $bValue = intval(mb_substr($sourceTeacherLesson->ringsTime,0,2)) * 60 + intval(mb_substr($sourceTeacherLesson->ringsTime,3,2));
                        $diff = $aValue - $bValue;
                        if ($diff > 0) $srcPositiveDiff = true;
                        if ($diff < 0) $srcNegativeDiff = true;
                    }

                    $minDiff = 2000;
                    $busy = false;
                    $positiveDiff = false;
                    $negativeDiff = false;

                    $switchTeacherLessons = Teacher::CalendarSchedule($teacherId, array($teacherLesson->calendar_id));

                    foreach($switchTeacherLessons as $switchTeacherLesson) {
                        $aValue = intval(mb_substr($teacherLesson->ringsTime,0,2)) * 60 + intval(mb_substr($teacherLesson->ringsTime,3,2));
                        $bValue = intval(mb_substr($switchTeacherLesson->ringsTime,0,2)) * 60 + intval(mb_substr($switchTeacherLesson->ringsTime,3,2));
                        $diff = $aValue - $bValue;
                        if ($diff === 0) $busy = true;
                        if ($diff > 0) $positiveDiff = true;
                        if ($diff < 0) $negativeDiff = true;
                        if ($minDiff > abs($diff)) {
                            $minDiff = abs($diff);
                        }
                    }

                    if (count($switchTeacherLessons) !== 0 && !$busy && $minDiff < 80) {
                        if (!array_key_exists('possibleFill', $teacherLesson)) {
                            $teacherLesson->possibleFill = array();
                        }

                        $teacherLesson->possibleFill[] = array(
                            'lessonForExchangeId' => $lessonForExchange->id,
                            'teacherFio' => $lessonForExchange->teachersFio,
                            'disciplinesName' => $lessonForExchange->disciplinesName,
                            'lessonForExchangeDate' => $lessonForExchange->calendarsDate,
                            'lessonForExchangeTime' => $lessonForExchange->ringsTime,
                            'earlierSourceLessonsExists' => $srcPositiveDiff,
                            'latterSourceLessonsExists' => $srcNegativeDiff,
                            'earlierTargetLessonsExists' => $positiveDiff,
                            'latterTargetLessonsExists' => $negativeDiff
                        );
                    }
                }
            }

            //dd($teacherLesson);

            $substituteTeacherDisciplines = DB::table('discipline_teacher')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->where('disciplines.name', '=', $teacherLesson->disciplinesName)
                ->where('discipline_teacher.teacher_id', '!=', $teacherLesson->teachersId)
                ->get();

            $elementaryLesson = in_array(intval(explode(' ', $teacherLesson->studentGroupsName)[0]), array(1,2,3,4));

            $substituteTeacherDisciplinesFiltered = $substituteTeacherDisciplines->filter(function ($value, $key) use ($elementaryLesson) {
                if ($elementaryLesson) {
                    return in_array(intval(explode(' ', $value->name)[0]), array(1,2,3,4));
                } else {
                    return !in_array(intval(explode(' ', $value->name)[0]), array(1,2,3,4));
                }
            });

            $substituteTeacherIds = $substituteTeacherDisciplinesFiltered->pluck('teacher_id')->unique();

            $teacherLesson->substitutes = array();

            foreach($substituteTeacherIds as $substituteTeacherId) {
                $substituteTeacher = Teacher::find($substituteTeacherId);

                $substituteTeacherLessons = Teacher::CalendarSchedule($substituteTeacher->id, array($teacherLesson->calendar_id));

                $busy = false;
                $positiveDiff = false;
                $negativeDiff = false;
                $minDiff = 2000;
                $nearestLessons = array();
                $diffs = array();
                foreach($substituteTeacherLessons as $substituteTeacherLesson) {
                    $aValue = intval(mb_substr($teacherLesson->ringsTime,0,2)) * 60 + intval(mb_substr($teacherLesson->ringsTime,3,2));
                    $bValue = intval(mb_substr($substituteTeacherLesson->ringsTime,0,2)) * 60 + intval(mb_substr($substituteTeacherLesson->ringsTime,3,2));
                    $diff = $aValue - $bValue;
                    if ($diff === 0) $busy = true;
                    if ($diff > 0) $positiveDiff = true;
                    if ($diff < 0) $negativeDiff = true;
                    if ($minDiff >= abs($diff)) {
                        if ($minDiff === abs($diff)) {
                            $nearestLessons[] = $substituteTeacherLesson;
                        } else {
                            $nearestLessons = array($substituteTeacherLesson);
                        }

                        $minDiff = abs($diff);
                    }

                    $diffs[] = array(
                        '$teacherLesson->ringsTime' => $teacherLesson->ringsTime,
                        '$substituteTeacherLesson->ringsTime' => $substituteTeacherLesson->ringsTime,
                        '$aValue' => $aValue,
                        '$bValue' => $bValue,
                        '$diff' => $diff,
                        '$minDiff' => $minDiff
                    );
                }



                if (count($substituteTeacherLessons) !== 0 && !$busy && $minDiff < 80) {
                    $teacherLesson->substitutes[] = array(
                        'teacherFio' => $substituteTeacher->fio,
                        'teacherId' => $substituteTeacher->id,
                        'earlierLessonsExists' => $positiveDiff,
                        'latterLessonsExists' => $negativeDiff,
                        'nearestLessons' => $nearestLessons,
                        '$diffs' => $diffs
                    );
                }
            }
        }

        return $teacherLessons;
    }
}
