<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\StudentGroup;
use App\LessonLogEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonLogEventController extends Controller
{
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
}
