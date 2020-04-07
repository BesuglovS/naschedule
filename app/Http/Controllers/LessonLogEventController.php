<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\StudentGroup;
use App\LessonLogEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonLogEventController extends Controller
{
    public $limit = 100;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LessonLogEvent  $lessonLogEvent
     * @return \Illuminate\Http\Response
     */
    public function show(LessonLogEvent $lessonLogEvent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LessonLogEvent  $lessonLogEvent
     * @return \Illuminate\Http\Response
     */
    public function edit(LessonLogEvent $lessonLogEvent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LessonLogEvent  $lessonLogEvent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LessonLogEvent $lessonLogEvent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LessonLogEvent  $lessonLogEvent
     * @return \Illuminate\Http\Response
     */
    public function destroy(LessonLogEvent $lessonLogEvent)
    {
        //
    }

    public function ByGroup(Request $request) {
        $input = $request->all();

        if ((!isset($input['groupId'])) || (!isset($input['weeks'])))
        {
            return array("error" => "groupId и weeks обязательные параметры");
        }

        $groupId = $input['groupId'];
        $groupIds = StudentGroup::GetGroupsOfStudentFromGroup($groupId);

        $weeks = explode('|', $input['weeks']);
        sort($weeks);

        $calendarIds = Calendar::IdsFromWeeks($weeks);

        $events = DB::table('lesson_log_events')
            ->leftJoin('lessons as lessonOld', 'lesson_log_events.old_lesson_id', '=', 'lessonOld.id')
            ->leftJoin('calendars as cOld', 'lessonOld.calendar_id', '=', 'cOld.id')
            ->leftJoin('rings as rOld', 'lessonOld.ring_id', '=', 'rOld.id')
            ->leftJoin('auditoriums as aOld', 'lessonOld.auditorium_id', '=', 'aOld.id')
            ->leftJoin('discipline_teacher as dcOld', 'lessonOld.discipline_teacher_id', '=', 'dcOld.id')
            ->leftJoin('teachers as tOld', 'dcOld.teacher_id', '=', 'tOld.id')
            ->leftJoin('disciplines as dOld', 'dcOld.discipline_id', '=', 'dOld.id')
            ->leftJoin('student_groups as sgOld', 'dOld.student_group_id', '=', 'sgOld.id')

            ->leftJoin('lessons as lessonNew', 'lesson_log_events.new_lesson_id', '=', 'lessonNew.id')
            ->leftJoin('calendars as cNew', 'lessonNew.calendar_id', '=', 'cNew.id')
            ->leftJoin('rings as rNew', 'lessonNew.ring_id', '=', 'rNew.id')
            ->leftJoin('auditoriums as aNew', 'lessonNew.auditorium_id', '=', 'aNew.id')
            ->leftJoin('discipline_teacher as dcNew', 'lessonNew.discipline_teacher_id', '=', 'dcNew.id')
            ->leftJoin('teachers as tNew', 'dcNew.teacher_id', '=', 'tNew.id')
            ->leftJoin('disciplines as dNew', 'dcNew.discipline_id', '=', 'dNew.id')
            ->leftJoin('student_groups as sgNew', 'dNew.student_group_id', '=', 'sgNew.id')

            ->select('lesson_log_events.id as lessonLogEventId', 'lesson_log_events.date_time as lessonLogEventDateTime',
                'lesson_log_events.public_comment as lessonLogEventPublicComment', 'lesson_log_events.hidden_comment as lessonLogEventHiddenComment',

                'lessonOld.id as lessonOldId',
                'cOld.date as lessonOldCalendarDate',
                'rOld.time as lessonOldRingTime',
                'aOld.name as lessonOldAuditoriumName',
                'tOld.fio as lessonOldTeacherFio', 'dOld.name as lessonOldDisciplineName',
                'sgOld.name as lessonOldStudentGroupName',

                'lessonNew.id as lessonNewId',
                'cNew.date as lessonNewCalendarDate',
                'rNew.time as lessonNewRingTime',
                'aNew.name as lessonNewAuditoriumName',
                'tNew.fio as lessonNewTeacherFio', 'dNew.name as lessonNewDisciplineName',
                'sgNew.name as lessonNewStudentGroupName'
            )
            ->where(function($q) use ($calendarIds) {
                $q->whereIn('cOld.id', $calendarIds)
                    ->orWhereIn('cNew.id', $calendarIds);
            })
            ->Where(function($q) use ($groupIds) {
                $q->whereIn('sgOld.id', $groupIds)
                    ->orWhereIn('sgNew.id', $groupIds);
            })
            ->orderBy('lesson_log_events.date_time')
            ->get();
        return $events;
    }

    public function startsWith ($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    public function Dates()
    {
        $eventDates = DB::table('lesson_log_events')
            ->select('date_time')
            ->get()
            ->map(function ($item) {
                return mb_substr($item->date_time, 0, 10);
            })
            ->toArray();

        $eventDates = array_values(array_unique($eventDates));
        usort($eventDates, "strcmp");

        return $eventDates;

    }

    public function ByDateInfo(Request $request) {
        $input = $request->all();

        if (!isset($input['date']))
        {
            return array("error" => "date обязательный параметр");
        }

        $date = $input['date'];
        if (array_key_exists('weeks', $input)) {
            $weeks = explode('|', $input['weeks']);
            sort($weeks);
        } else {
            $weeks = range(1, Calendar::WeekCount());
        }

        $calendarIds = Calendar::IdsFromWeeks($weeks);

        $carbonStartDate = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $carbonEndDate = $carbonStartDate->copy()->addDay();


        $events = DB::table('lesson_log_events')
            ->leftJoin('lessons as lessonOld', 'lesson_log_events.old_lesson_id', '=', 'lessonOld.id')
            ->leftJoin('calendars as cOld', 'lessonOld.calendar_id', '=', 'cOld.id')
            ->leftJoin('rings as rOld', 'lessonOld.ring_id', '=', 'rOld.id')
            ->leftJoin('auditoriums as aOld', 'lessonOld.auditorium_id', '=', 'aOld.id')
            ->leftJoin('discipline_teacher as dcOld', 'lessonOld.discipline_teacher_id', '=', 'dcOld.id')
            ->leftJoin('teachers as tOld', 'dcOld.teacher_id', '=', 'tOld.id')
            ->leftJoin('disciplines as dOld', 'dcOld.discipline_id', '=', 'dOld.id')
            ->leftJoin('student_groups as sgOld', 'dOld.student_group_id', '=', 'sgOld.id')

            ->leftJoin('lessons as lessonNew', 'lesson_log_events.new_lesson_id', '=', 'lessonNew.id')
            ->leftJoin('calendars as cNew', 'lessonNew.calendar_id', '=', 'cNew.id')
            ->leftJoin('rings as rNew', 'lessonNew.ring_id', '=', 'rNew.id')
            ->leftJoin('auditoriums as aNew', 'lessonNew.auditorium_id', '=', 'aNew.id')
            ->leftJoin('discipline_teacher as dcNew', 'lessonNew.discipline_teacher_id', '=', 'dcNew.id')
            ->leftJoin('teachers as tNew', 'dcNew.teacher_id', '=', 'tNew.id')
            ->leftJoin('disciplines as dNew', 'dcNew.discipline_id', '=', 'dNew.id')
            ->leftJoin('student_groups as sgNew', 'dNew.student_group_id', '=', 'sgNew.id')

            ->select('lesson_log_events.id as lessonLogEventId', 'lesson_log_events.date_time as lessonLogEventDateTime')
            ->whereBetween('lesson_log_events.date_time', array($carbonStartDate, $carbonEndDate))
            ->where(function ($query) use ($calendarIds) {
                $query->whereIn('cOld.id', $calendarIds)
                    ->orWhereIn('cNew.id', $calendarIds);})
            ->orderBy('lesson_log_events.date_time')
            ->get();

        $totalCount = count($events);

        $result = array(
            "total-count" => $totalCount,
            "parts" => array()
        );
        $current = 0;
        if (count($events) !== 0) {
            do {
                $end = $current + $this->limit;
                if ($end > $totalCount - 1) {
                    $end = $totalCount - 1;
                }

                $carbonStart = Carbon::createFromFormat('Y-m-d H:i:s', $events[$current]->lessonLogEventDateTime);
                $carbonEnd = Carbon::createFromFormat('Y-m-d H:i:s', $events[$end]->lessonLogEventDateTime);

                $result["parts"][] = array(
                    "offset" => $current,
                    "times" => $carbonStart->format('H:i:s') . " - " . $carbonEnd->format('H:i:s')
                );

                $current = $end;
            } while ($current !== $totalCount - 1);
        }

        return $result;
    }

    public function ByDate(Request $request) {
        $input = $request->all();

        if (!isset($input['date']) || !isset($input['offset']))
        {
            return array("error" => "date и offset обязательные параметры");
        }

        $date = $input['date'];
        $offset = intval($input['offset']);
        $limit = $this->limit;
        $carbonStartDate = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $carbonEndDate = $carbonStartDate->copy()->addDay();
        if (array_key_exists('weeks', $input)) {
            $weeks = explode('|', $input['weeks']);
            sort($weeks);
        } else {
            $weeks = range(1, Calendar::WeekCount());
        }
        $calendarIds = Calendar::IdsFromWeeks($weeks);

        $events = DB::table('lesson_log_events')
            ->leftJoin('lessons as lessonOld', 'lesson_log_events.old_lesson_id', '=', 'lessonOld.id')
            ->leftJoin('calendars as cOld', 'lessonOld.calendar_id', '=', 'cOld.id')
            ->leftJoin('rings as rOld', 'lessonOld.ring_id', '=', 'rOld.id')
            ->leftJoin('auditoriums as aOld', 'lessonOld.auditorium_id', '=', 'aOld.id')
            ->leftJoin('discipline_teacher as dcOld', 'lessonOld.discipline_teacher_id', '=', 'dcOld.id')
            ->leftJoin('teachers as tOld', 'dcOld.teacher_id', '=', 'tOld.id')
            ->leftJoin('disciplines as dOld', 'dcOld.discipline_id', '=', 'dOld.id')
            ->leftJoin('student_groups as sgOld', 'dOld.student_group_id', '=', 'sgOld.id')

            ->leftJoin('lessons as lessonNew', 'lesson_log_events.new_lesson_id', '=', 'lessonNew.id')
            ->leftJoin('calendars as cNew', 'lessonNew.calendar_id', '=', 'cNew.id')
            ->leftJoin('rings as rNew', 'lessonNew.ring_id', '=', 'rNew.id')
            ->leftJoin('auditoriums as aNew', 'lessonNew.auditorium_id', '=', 'aNew.id')
            ->leftJoin('discipline_teacher as dcNew', 'lessonNew.discipline_teacher_id', '=', 'dcNew.id')
            ->leftJoin('teachers as tNew', 'dcNew.teacher_id', '=', 'tNew.id')
            ->leftJoin('disciplines as dNew', 'dcNew.discipline_id', '=', 'dNew.id')
            ->leftJoin('student_groups as sgNew', 'dNew.student_group_id', '=', 'sgNew.id')

            ->select('lesson_log_events.id as lessonLogEventId', 'lesson_log_events.date_time as lessonLogEventDateTime',
                'lesson_log_events.public_comment as lessonLogEventPublicComment', 'lesson_log_events.hidden_comment as lessonLogEventHiddenComment',

                'lessonOld.id as lessonOldId',
                'cOld.date as lessonOldCalendarDate',
                'rOld.time as lessonOldRingTime',
                'aOld.name as lessonOldAuditoriumName',
                'tOld.fio as lessonOldTeacherFio', 'dOld.name as lessonOldDisciplineName',
                'sgOld.name as lessonOldStudentGroupName',

                'lessonNew.id as lessonNewId',
                'cNew.date as lessonNewCalendarDate',
                'rNew.time as lessonNewRingTime',
                'aNew.name as lessonNewAuditoriumName',
                'tNew.fio as lessonNewTeacherFio', 'dNew.name as lessonNewDisciplineName',
                'sgNew.name as lessonNewStudentGroupName'
            )
            ->whereBetween('lesson_log_events.date_time', array($carbonStartDate, $carbonEndDate))
            ->where(function ($query) use ($calendarIds) {
                $query->whereIn('cOld.id', $calendarIds)
                    ->orWhereIn('cNew.id', $calendarIds);})
            ->orderBy('lesson_log_events.date_time')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $totalCount = DB::table('lesson_log_events')->count();

        return array(
            "total-count" => $totalCount,
            "offset" => $offset,
            "limit" => $limit,
            "events" => $events
        );
    }

    public function ByTeacher(Request $request) {
        $input = $request->all();

        if ((!isset($input['teacherId'])) || (!isset($input['weeks'])))
        {
            return array("error" => "teacherId и weeks обязательные параметры");
        }

        $teacherId = $input['teacherId'];

        $weeks = explode('|', $input['weeks']);
        sort($weeks);

        $calendarIds = Calendar::IdsFromWeeks($weeks);

        $events = DB::table('lesson_log_events')
            ->leftJoin('lessons as lessonOld', 'lesson_log_events.old_lesson_id', '=', 'lessonOld.id')
            ->leftJoin('calendars as cOld', 'lessonOld.calendar_id', '=', 'cOld.id')
            ->leftJoin('rings as rOld', 'lessonOld.ring_id', '=', 'rOld.id')
            ->leftJoin('auditoriums as aOld', 'lessonOld.auditorium_id', '=', 'aOld.id')
            ->leftJoin('discipline_teacher as dcOld', 'lessonOld.discipline_teacher_id', '=', 'dcOld.id')
            ->leftJoin('teachers as tOld', 'dcOld.teacher_id', '=', 'tOld.id')
            ->leftJoin('disciplines as dOld', 'dcOld.discipline_id', '=', 'dOld.id')
            ->leftJoin('student_groups as sgOld', 'dOld.student_group_id', '=', 'sgOld.id')

            ->leftJoin('lessons as lessonNew', 'lesson_log_events.new_lesson_id', '=', 'lessonNew.id')
            ->leftJoin('calendars as cNew', 'lessonNew.calendar_id', '=', 'cNew.id')
            ->leftJoin('rings as rNew', 'lessonNew.ring_id', '=', 'rNew.id')
            ->leftJoin('auditoriums as aNew', 'lessonNew.auditorium_id', '=', 'aNew.id')
            ->leftJoin('discipline_teacher as dcNew', 'lessonNew.discipline_teacher_id', '=', 'dcNew.id')
            ->leftJoin('teachers as tNew', 'dcNew.teacher_id', '=', 'tNew.id')
            ->leftJoin('disciplines as dNew', 'dcNew.discipline_id', '=', 'dNew.id')
            ->leftJoin('student_groups as sgNew', 'dNew.student_group_id', '=', 'sgNew.id')

            ->select('lesson_log_events.id as lessonLogEventId', 'lesson_log_events.date_time as lessonLogEventDateTime',
                'lesson_log_events.public_comment as lessonLogEventPublicComment', 'lesson_log_events.hidden_comment as lessonLogEventHiddenComment',

                'lessonOld.id as lessonOldId',
                'cOld.date as lessonOldCalendarDate',
                'rOld.time as lessonOldRingTime',
                'aOld.name as lessonOldAuditoriumName',
                'tOld.fio as lessonOldTeacherFio', 'dOld.name as lessonOldDisciplineName',
                'sgOld.name as lessonOldStudentGroupName',

                'lessonNew.id as lessonNewId',
                'cNew.date as lessonNewCalendarDate',
                'rNew.time as lessonNewRingTime',
                'aNew.name as lessonNewAuditoriumName',
                'tNew.fio as lessonNewTeacherFio', 'dNew.name as lessonNewDisciplineName',
                'sgNew.name as lessonNewStudentGroupName'
            )
            ->where(function($q) use ($calendarIds) {
                $q->whereIn('cOld.id', $calendarIds)
                    ->orWhereIn('cNew.id', $calendarIds);
            })
            ->where(function($q) use ($teacherId) {
                $q->where('tOld.id', '=', $teacherId)
                    ->orWhere('tNew.id', '=', $teacherId);
            })
            ->orderBy('lesson_log_events.date_time')
            ->get();
        return $events;
    }
}
