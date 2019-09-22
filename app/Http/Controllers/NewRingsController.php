<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\Lesson;
use App\DomainClasses\LessonLogEvent;
use App\DomainClasses\Ring;
use App\DomainClasses\StudentGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NewRingsController extends Controller
{
    public function index()
    {
        $calendars = Calendar::all()->sortBy('date');

        $rings = Ring::all();

        $ringTimePairs1 = array(
            "08:30" => "08:00",
            "09:20" => "08:50",
            "10:20" => "09:50",
            "11:20" => "10:50",
            "12:10" => "11:40",
            "13:00" => "12:30",
            "13:50" => "13:20",
            "14:40" => "14:10",
            "15:30" => "15:00",
        );

        $ringTimePairs2 = array(
            "08:00" => "08:30",
            "08:50" => "09:20",
            "09:50" => "10:20",
            "10:50" => "11:20",
            "11:40" => "12:10",
            "12:30" => "13:00",
            "13:20" => "13:50",
            "14:10" => "14:40",
            "15:00" => "15:30",
        );

        $ringIdPairs1 = $this->RingIdPairsFromTimePairs($ringTimePairs1);
        $ringIdPairs2 = $this->RingIdPairsFromTimePairs($ringTimePairs2);

        return view('main.NewRings', compact('calendars', 'rings', 'ringIdPairs1', 'ringIdPairs2'));
    }

    public function ChangeRings(Request $request)
    {
        ini_set('max_execution_time', 0);

        $input = $request->all();
        $user = Auth::user();

        if(!isset($input['fromCalendarId']) || !isset($input['toCalendarId']) || !isset($input['ringIds']))
        {
            return array("error" => "fromDate и toDate - обязательные параметры");
        }
        $fromCalendarId = $input["fromCalendarId"];
        $toCalendarId = $input["toCalendarId"];
        $ringIds = $input["ringIds"];
        $ringIds = explode( '|', $ringIds);
        $ringIdPairs = array();
        foreach($ringIds as $ringId) {
            $pair = explode('*', $ringId);
            $ringIdPairs[$pair[0]] = intval($pair[1]);
        }

        $fromCalendar = Calendar::find($fromCalendarId);
        $fromDateCarbon = Carbon::createFromFormat('Y-m-d', $fromCalendar->date);

        $toCalendar = Calendar::find($toCalendarId);
        $toDateCarbon = Carbon::createFromFormat('Y-m-d', $toCalendar->date);

        $allCalendars = Calendar::all()->toArray();

        $calendars = array_filter($allCalendars, function ($item) use ($fromDateCarbon, $toDateCarbon) {
            $calendarCarbon = Carbon::createFromFormat('Y-m-d', $item["date"]);

            return (($calendarCarbon >= $fromDateCarbon) && ($calendarCarbon <= $toDateCarbon));
        });

        $calendarIds = collect($calendars)->pluck('id');

        $fromRingIds = array_keys($ringIdPairs);

        $lessonsToChange = DB::table('lessons')
            ->where('lessons.state', '=', 1)
            ->whereIn('lessons.calendar_id', $calendarIds)
            ->whereIn('lessons.ring_id', $fromRingIds)
            ->select('lessons.id', 'lessons.ring_id')
            ->get();

        //dd($lessonsToChange);

        foreach($lessonsToChange as $lesson) {
            $old_lesson = Lesson::find($lesson->id);
            $old_lesson->state = 0;
            $old_lesson->save();

            $new_lesson = new Lesson();
            $new_lesson->state = 1;
            $new_lesson->discipline_teacher_id = $old_lesson->discipline_teacher_id;
            $new_lesson->calendar_id = $old_lesson->calendar_id;
            $new_lesson->ring_id = $ringIdPairs[$old_lesson->ring_id];
            $new_lesson->auditorium_id = $old_lesson->auditorium_id;
            $new_lesson->save();

            $lle = new LessonLogEvent();
            $lle->old_lesson_id = $old_lesson->id;
            $lle->new_lesson_id = $new_lesson->id;
            $lle->date_time = Carbon::now()->format('Y-m-d H:i:s');
            $lle->public_comment = "";
            $lle->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": ": "") . "New rings";
            $lle->save();
        }

        return array("OK" => "Success");
    }

    /**
     * @param array $ringTimePairs
     * @return array
     */
    public function RingIdPairsFromTimePairs(array $ringTimePairs): array
    {
        set_time_limit(300);

        $fromTime = array_keys($ringTimePairs);

        $ringIdPairs = array();

        foreach ($fromTime as $fromTimeItem) {
            $fromId = Ring::IdfromTime($fromTimeItem);

            if ($fromId !== "") {
                $toId = Ring::IdfromTime($ringTimePairs[$fromTimeItem]);

                if ($toId !== "") {
                    $ringIdPairs[$fromId] = $toId;
                }
            }
        }
        return $ringIdPairs;
    }
}
