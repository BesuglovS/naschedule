<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\Lesson;
use App\DomainClasses\LessonLogEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Lesson::all();
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
     * @param  \App\Lesson  $lesson
     * @return \Illuminate\Http\Response
     */
    public function show(Lesson $lesson)
    {
        return $lesson;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Lesson  $lesson
     * @return \Illuminate\Http\Response
     */
    public function edit(Lesson $lesson)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Lesson  $lesson
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lesson $lesson)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Lesson  $lesson
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lesson $lesson)
    {
        //
    }

    public function destroyByIds(Request $request)
    {
        $user = Auth::user();

        $input = $request->all();

        $Ids = explode('|', $input['Ids']);

        $lessons = DB::table('lessons')
            ->whereIn('lessons.id', $Ids)
            ->get();

        foreach($lessons as $lesson) {
            if ($lesson->state == 1) {
                $newLle = new LessonLogEvent();
                $newLle->old_lesson_id = $lesson->id;
                $newLle->new_lesson_id = 0;
                $newLle->date_time = Carbon::now()->format('Y-m-d H:i:s');
                $newLle->public_comment = "";
                $newLle->hidden_comment = ($user !== null) ? $user->id . " @ " . $user->name . ": " : "";
                $newLle->save();

                $l = Lesson::find($lesson->id);
                $l->state = 0;
                $l->save();
            }
        }

        return $lessons;
    }

    public function WeeksAndAudsEdit(Request $request) {
        $input = $request->all();

        $user = Auth::user();

        $add = array();
        if (!is_null($input['add'])) {
            $addArray = explode('|', $input['add']);
            foreach ($addArray as $addItem) {
                $addItemArray = explode('@', $addItem);
                $add[$addItemArray[0]] = $addItemArray[1];
            }
        }

        $remove = array();
        if (!is_null($input['remove'])) {
            $remove = explode('|', $input['remove']);
        }

        $changeAuditorium = array();
        if (!is_null($input['changeAuditorium'])) {
            $changeAuditoriumArray = explode('|', $input['changeAuditorium']);
            foreach ($changeAuditoriumArray as $caItem) {
                $caItemArray = explode('@', $caItem);
                $changeAuditorium[$caItemArray[0]] = $caItemArray[1];
            }
        }

        $tfdId = $input['tfdId'];
        $ringId = $input['ringId'];
        $dow = $input['dow'];

        $addCalendarIdsByWeek = Calendar::IdsByWeekFromDowAndWeeks($dow, array_keys($add));
        $removeCalendarIdsByWeek = Calendar::IdsByWeekFromDowAndWeeks($dow, $remove);
        $changeAuditoriumCalendarIdsByWeek = Calendar::IdsByWeekFromDowAndWeeks($dow, array_keys($changeAuditorium));

        // Add
        foreach ($add as $addWeek => $addAuditoriumId) {
            $lesson = new Lesson();
            $lesson->state = 1;
            $lesson->discipline_teacher_id = $tfdId;
            $lesson->calendar_id = $addCalendarIdsByWeek[$addWeek];
            $lesson->ring_id = $ringId;
            $lesson->auditorium_id = $add[$addWeek];
            $lesson->save();

            $lle = new LessonLogEvent();
            $lle->old_lesson_id = 0;
            $lle->new_lesson_id = $lesson->id;
            $lle->date_time = Carbon::now()->format('Y-m-d H:i:s');
            $lle->public_comment = "";
            $lle->hidden_comment = ($user !== null) ? $user->id . " @ " . $user->name . ": " : "";;
            $lle->save();
        }

        // Remove
        foreach ($remove as $removeWeek) {
            $lessonCalendarId = $removeCalendarIdsByWeek[$removeWeek];

            $lesson = DB::table('lessons')
                ->where('lessons.discipline_teacher_id', '=', $tfdId)
                ->where('lessons.calendar_id', '=', $lessonCalendarId)
                ->where('lessons.ring_id', '=', $ringId)
                ->where('lessons.state', '=', 1)
                ->first();

            if (!is_null($lesson)) {
                $new_lesson = Lesson::find($lesson->id);
                $new_lesson->state = 0;
                $new_lesson->save();

                $lle = new LessonLogEvent();
                $lle->old_lesson_id = $lesson->id;
                $lle->new_lesson_id = 0;
                $lle->date_time = Carbon::now()->format('Y-m-d H:i:s');
                $lle->public_comment = "";
                $lle->hidden_comment = ($user !== null) ? $user->id . " @ " . $user->name . ": " : "";
                $lle->save();
            }
        }

        // changeAuditorium
        foreach ($changeAuditorium as $changeAuditoriumWeek => $changeAuditoriumAuditoriumId) {
            $lessonCalendarId = $changeAuditoriumCalendarIdsByWeek[$changeAuditoriumWeek];

            $lesson = DB::table('lessons')
                ->where('lessons.discipline_teacher_id', '=', $tfdId)
                ->where('lessons.calendar_id', '=', $lessonCalendarId)
                ->where('lessons.ring_id', '=', $ringId)
                ->where('lessons.state', '=', 1)
                ->first();

            if (!is_null($lesson)) {
                $old_lesson = Lesson::find($lesson->id);
                $old_lesson->state = 0;
                $old_lesson->save();

                $new_lesson = new Lesson();
                $new_lesson->state = 1;
                $new_lesson->discipline_teacher_id = $tfdId;
                $new_lesson->calendar_id = $lessonCalendarId;
                $new_lesson->ring_id = $ringId;
                $new_lesson->auditorium_id = $changeAuditoriumAuditoriumId;
                $new_lesson->save();

                $lle = new LessonLogEvent();
                $lle->old_lesson_id = $old_lesson->id;
                $lle->new_lesson_id = $new_lesson->id;
                $lle->date_time = Carbon::now()->format('Y-m-d H:i:s');
                $lle->public_comment = "";
                $lle->hidden_comment = ($user !== null) ? $user->id . " @ " . $user->name . ": " : "";
                $lle->save();
            }
        }

        return array('success' => 'Done');
    }

    public function GroupScheduleAdd(Request $request) {
        $input = $request->all();

        $user = Auth::user();

        if ((!isset($input['tfdId'])) || (!isset($input['dows']))  || (!isset($input['weeks']))
            || (!isset($input['ringIds']))  || (!isset($input['weeksAuds'])))
        {
            return array("error" => "tfdId, dows, weeks, ringIds и weeksAuds обязательные параметры");
        }

        $tfdId = $input['tfdId'];

        $dows = explode('|', $input['dows']);
        usort($dows, function($a, $b){
            if ($a === $b) return 0;
            return ($a < $b) ? -1 : 1;
        });

        $weeks = explode('|', $input['weeks']);
        usort($weeks, function($a, $b){
            if ($a === $b) return 0;
            return ($a < $b) ? -1 : 1;
        });

        $ringIds = explode('|', $input['ringIds']);

        $waArray = explode('|', $input['weeksAuds']);
        $weeksAuds = array();
        foreach($waArray as $wa) {
            $waItemArray  = explode('@', $wa);
            $weeksAuds[$waItemArray[0]] = $waItemArray[1];
        }

        $calendarByWeekAndDowIds = Calendar::IdsByWeekAndDowFromDowsAndWeeks($dows, $weeks);

        foreach ($weeks as $week) {
            foreach ($dows as $dow) {
                foreach ($ringIds as $ringId) {
                    $lesson = new Lesson();
                    $lesson->state = 1;
                    $lesson->discipline_teacher_id = $tfdId;
                    $lesson->calendar_id = $calendarByWeekAndDowIds[$week][$dow];
                    $lesson->ring_id = $ringId;
                    $lesson->auditorium_id = $weeksAuds[$week];
                    $lesson->save();

                    $lle = new LessonLogEvent();
                    $lle->old_lesson_id = 0;
                    $lle->new_lesson_id = $lesson->id;
                    $lle->date_time = Carbon::now()->format('Y-m-d H:i:s');
                    $lle->public_comment = "";
                    $lle->hidden_comment = ($user !== null) ? $user->id . " @ " . $user->name . ": " : "";
                    $lle->save();
                }
            }
        }
    }
}
