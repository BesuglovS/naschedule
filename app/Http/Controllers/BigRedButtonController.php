<?php

namespace App\Http\Controllers;

use App\DomainClasses\Auditorium;
use App\DomainClasses\Calendar;
use App\DomainClasses\Lesson;
use App\DomainClasses\LessonLogEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BigRedButtonController extends Controller
{
    public function CorrectBlankAudsForBuildings() {
        ini_set('max_execution_time', 300);

        $blankAud1Name = '-';
        $blankAud2Name = '--';
        $blankAud3Name = '---';

        $blankAud1 = DB::table('auditoriums')
            ->where('name', '=', $blankAud1Name)
            ->first();
        $blankAud2 = DB::table('auditoriums')
            ->where('name', '=', $blankAud2Name)
            ->first();
        $blankAud3 = DB::table('auditoriums')
            ->where('name', '=', $blankAud3Name)
            ->first();

        $blankAudIds = array();
        $blankAudIds[] = $blankAud1->id;
        $blankAudIds[] = $blankAud2->id;
        $blankAudIds[] = $blankAud3->id;

        $blankAudLessons = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->whereIn('lessons.auditorium_id', $blankAudIds)
            ->where('lessons.state', '=', 1)
            ->select(
                'lessons.id as lessonsId',
                'lessons.auditorium_id as auditorium_id',
                'student_groups.name as studentGroupsName',
                'calendars.date as lessonDate'
            )
            ->get()
            ->toArray();

        $result = array();

        foreach($blankAudLessons as $blankAudLesson) {
            $groupNameStart = explode(' ', $blankAudLesson->studentGroupsName)[0];
            $lessonDow = Calendar::CarbonDayOfWeek(Carbon::createFromFormat('Y-m-d', $blankAudLesson->lessonDate));

            $start = ['1', '2', '3', '4'];
            $five = ['5'];
            $six = ['6'];
            $finish = ['7', '8', '9', '10', '11'];

            $blankAudId = $blankAud1->id;

            if (in_array($groupNameStart, $start)) {
                $blankAudId = $blankAud1->id;
            }

            if (in_array($groupNameStart, $finish)) {
                $blankAudId = $blankAud2->id;
            }

            if ((in_array($groupNameStart, $five)) && (in_array($lessonDow, [1,3,5]))) {
                $blankAudId = $blankAud2->id;
            }
            if ((in_array($groupNameStart, $five)) && (in_array($lessonDow, [2,4,6]))) {
                $blankAudId = $blankAud3->id;
            }

            if ((in_array($groupNameStart, $six)) && (in_array($lessonDow, [2,4,6]))) {
                $blankAudId = $blankAud2->id;
            }
            if ((in_array($groupNameStart, $six)) && (in_array($lessonDow, [1,3,5]))) {
                $blankAudId = $blankAud3->id;
            }



            if ($blankAudLesson->auditorium_id !== $blankAudId) {
//                $result[] = array(
//                    'oldAudId' => $blankAudLesson->auditorium_id,
//                    'newAudId' => $blankAudId,
//                    'date' => $blankAudLesson->lessonDate,
//                    'dow' => $lessonDow,
//                    'groupName' => $blankAudLesson->studentGroupsName
//                );

                $lesson = Lesson::find($blankAudLesson->lessonsId);
                $lesson->auditorium_id = $blankAudId;
                $lesson->save();
            }
        }

        //return $result;
        return array('OK' => 'Success');
    }

    public function RemoveDuplicateLessons(Request $request) {
        $user = Auth::user();

        $lessons = DB::table('lessons')
            ->where('lessons.state', '=', 1)
            ->get();

        $byCalendarRing = array();

        foreach($lessons as $lesson) {
            $calendarRing = $lesson->calendar_id . '+' . $lesson->ring_id;
            if (!array_key_exists($calendarRing, $byCalendarRing)) {
                $byCalendarRing[$calendarRing] = array();
            }
            $byCalendarRing[$calendarRing][] = $lesson;
        }

        $lessonPairs = array();

        foreach($byCalendarRing as $calendarRing => $groupLessons) {
            for($i = 0; $i < count($groupLessons); $i++) {
                for($j = 0; $j < count($groupLessons); $j++) {
                    if ($i !== $j &&
                        $groupLessons[$i]->discipline_teacher_id === $groupLessons[$j]->discipline_teacher_id) {
                        $lessonPairs[] = array($groupLessons[$i], $groupLessons[$j]);
                    }
                }
            }
        }

        foreach($lessonPairs as $lessonPair) {
            $lessonId = $lessonPair[0]->id > $lessonPair[1]->id ? $lessonPair[0]->id : $lessonPair[1]->id;

            $newLle = new LessonLogEvent();
            $newLle->old_lesson_id = $lessonId;
            $newLle->new_lesson_id = 0;
            $newLle->date_time = Carbon::now()->format('Y-m-d H:i:s');
            $newLle->public_comment = "";
            $newLle->hidden_comment = ($user !== null) ? $user->id . " @ " . $user->name . ": " : " Remove duplicate";
            $newLle->save();

            $l = Lesson::find($lessonId);
            $l->state = 0;
            $l->save();
        }

        return $lessonPairs;
    }

    public function TMP(Request $request) {

    }
}
