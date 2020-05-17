<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\Discipline;
use App\DomainClasses\Lesson;
use App\DomainClasses\LessonLogEvent;
use App\DomainClasses\Teacher;
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
            $lesson->description = '';
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
                $new_lesson->description = $lesson->description;
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
                    $lesson->description = '';
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

    public function changeLessonAud(Request $request) {
        $user = Auth::user();
        $input = $request->all();

        if ((!isset($input['lessonId'])) || (!isset($input['auditoriumId'])))
        {
            return array("error" => "lessonId и auditoriumId обязательные параметры");
        }
        $lessonId = $input['lessonId'];
        $auditoriumId = $input['auditoriumId'];

        $old_lesson = Lesson::find($lessonId);

        if ($old_lesson->auditorium_id !== $auditoriumId) {
            $old_lesson->state = 0;
            $old_lesson->save();

            $new_lesson = new Lesson();
            $new_lesson->state = 1;
            $new_lesson->discipline_teacher_id = $old_lesson->discipline_teacher_id;
            $new_lesson->calendar_id = $old_lesson->calendar_id;
            $new_lesson->ring_id = $old_lesson->ring_id;
            $new_lesson->auditorium_id = $auditoriumId;
            $new_lesson->description = $old_lesson->description;
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

    public function changeLessonsAud(Request $request) {
        $user = Auth::user();
        $input = $request->all();

        if ((!isset($input['lessonIds'])) || (!isset($input['auditoriumId'])))
        {
            return array("error" => "lessonIds и auditoriumId обязательные параметры");
        }
        $lessonIds = explode('|', $input['lessonIds']);
        $auditoriumId = $input['auditoriumId'];

        foreach($lessonIds as $lessonId) {
            $old_lesson = Lesson::find($lessonId);

            if ($old_lesson->auditorium_id !== $auditoriumId) {
                $old_lesson->state = 0;
                $old_lesson->save();

                $new_lesson = new Lesson();
                $new_lesson->state = 1;
                $new_lesson->discipline_teacher_id = $old_lesson->discipline_teacher_id;
                $new_lesson->calendar_id = $old_lesson->calendar_id;
                $new_lesson->ring_id = $old_lesson->ring_id;
                $new_lesson->auditorium_id = $auditoriumId;
                $new_lesson->description = $old_lesson->description;
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
    }

    public function SwitchLessons(Request $request) {
        $input = $request->all();

        if ((!isset($input['lessonId1'])) || (!isset($input['lessonId2'])))
        {
            return array("error" => "lessonId1 и lessonId2 обязательные параметры");
        }

        $this->SwapLessons($input['lessonId1'], $input['lessonId2']);
    }

    public function RemoveLessonAndReplaceWithAnother(Request $request) {
        $user = Auth::user();
        $input = $request->all();

        if ((!isset($input['lessonToRemoveId'])) || (!isset($input['lessonToMoveId'])))
        {
            return array("error" => "lessonToRemove и lessonToMove обязательные параметры");
        }

        $lesson1 = Lesson::find($input['lessonToRemoveId']);
        $lesson1->state = 0;
        $lesson1->save();
        $lle1 = new LessonLogEvent();
        $lle1->old_lesson_id = $lesson1->id;
        $lle1->new_lesson_id = 0;
        $lle1->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $lle1->public_comment = "";
        $lle1->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "" ). " RemoveAndReplace lesson " . $input['lessonToRemoveId'] . '/' . $input['lessonToMoveId'] . ' remove 1';
        $lle1->save();

        $lesson2 = Lesson::find($input['lessonToMoveId']);
        $lesson2->state = 0;
        $lesson2->save();
        $lle2 = new LessonLogEvent();
        $lle2->old_lesson_id = $lesson2->id;
        $lle2->new_lesson_id = 0;
        $lle2->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $lle2->public_comment = "";
        $lle2->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "" ). " RemoveAndReplace lesson " . $input['lessonToRemoveId'] . '/' . $input['lessonToMoveId'] . ' remove 2';
        $lle2->save();

        $new_lesson2 = new Lesson();
        $new_lesson2->state = 1;
        $new_lesson2->discipline_teacher_id = $lesson2->discipline_teacher_id;
        $new_lesson2->calendar_id = $lesson1->calendar_id;
        $new_lesson2->ring_id = $lesson1->ring_id;
        $new_lesson2->auditorium_id = $lesson1->auditorium_id;
        $new_lesson2->description = '';
        $new_lesson2->save();
        $lle4 = new LessonLogEvent();
        $lle4->old_lesson_id = 0;
        $lle4->new_lesson_id = $new_lesson2->id;
        $lle4->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $lle4->public_comment = "";
        $lle4->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "") . " RemoveAndReplace lesson " . $input['lessonToRemoveId'] . '/' . $input['lessonToMoveId'] . ' replace';
        $lle4->save();
    }

    public function SubstituteTeacherForLesson(Request $request) {
        $user = Auth::user();
        $input = $request->all();

        if ((!isset($input['lessonId'])) || (!isset($input['teacherId'])))
        {
            return array("error" => "lessonId и teacherId обязательные параметры");
        }
        $lessonId = $input['lessonId'];
        $teacherId = $input['teacherId'];

        $lesson1 = Lesson::find($input['lessonId']);
        $lesson1->state = 0;
        $lesson1->save();
        $lle1 = new LessonLogEvent();
        $lle1->old_lesson_id = $lesson1->id;
        $lle1->new_lesson_id = 0;
        $lle1->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $lle1->public_comment = "";
        $lle1->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "" ). " SubstituteTeacher For Lesson " . $input['lessonId'] . '/' . $input['teacherId'] . ' remove 1';
        $lle1->save();

        $lessonTfd = DB::table('discipline_teacher')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('discipline_teacher.id', '=', $lesson1->discipline_teacher_id)
            ->select(
                'disciplines.name as disciplinesName',
                'disciplines.student_group_id as disciplineStudentGroupId',
                'disciplines.type as disciplinesType'
            )
            ->first();

        $findTfd = DB::table('discipline_teacher')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('discipline_teacher.teacher_id', '=', $teacherId)
            ->where('disciplines.name', '=', $lessonTfd->disciplinesName)
            ->where('disciplines.student_group_id', '=', $lessonTfd->disciplineStudentGroupId)
            ->select('discipline_teacher.id')
            ->first();

        if ($findTfd == null) {
            $newDiscipline = new Discipline();
            $newDiscipline->name = $lessonTfd->disciplinesName;
            $newDiscipline->attestation = 0;
            $newDiscipline->auditorium_hours = 0;
            $newDiscipline->auditorium_hours_per_week = 0;
            $newDiscipline->lecture_hours = 0;
            $newDiscipline->practical_hours = 0;
            $newDiscipline->active = 0;
            $newDiscipline->type = $lessonTfd->disciplinesType;
            $newDiscipline->student_group_id = $lessonTfd->disciplineStudentGroupId;
            $newDiscipline->save();

            // new Tfd
            $teacher = Teacher::find($teacherId);
            $discipline = Discipline::find($newDiscipline->id);
            $teacher->disciplines()->attach($discipline);

            $findTfd = DB::table('discipline_teacher')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->where('discipline_teacher.teacher_id', '=', $teacherId)
                ->where('disciplines.name', '=', $lessonTfd->disciplinesName)
                ->where('disciplines.student_group_id', '=', $lessonTfd->disciplineStudentGroupId)
                ->select('discipline_teacher.id')
                ->first();
        }

        $new_lesson1 = new Lesson();
        $new_lesson1->state = 1;
        $new_lesson1->discipline_teacher_id = $findTfd->id;
        $new_lesson1->calendar_id = $lesson1->calendar_id;
        $new_lesson1->ring_id = $lesson1->ring_id;
        $new_lesson1->auditorium_id = $lesson1->auditorium_id;
        $new_lesson1->description = '';
        $new_lesson1->save();
        $lle2 = new LessonLogEvent();
        $lle2->old_lesson_id = 0;
        $lle2->new_lesson_id = $new_lesson1->id;
        $lle2->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $lle2->public_comment = "";
        $lle2->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "") . " SubstituteTeacher For Lesson " . $input['lessonId'] . '/' . $input['teacherId'] . ' substitute';
        $lle2->save();
    }

    /**
     * @param array $input
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     */
    public function SwapLessons($lesson1Id, $lesson2Id): void
    {
        $user = Auth::user();

        $lesson1 = Lesson::find($lesson1Id);
        $lesson1->state = 0;
        $lesson1->save();
        $lle1 = new LessonLogEvent();
        $lle1->old_lesson_id = $lesson1->id;
        $lle1->new_lesson_id = 0;
        $lle1->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $lle1->public_comment = "";
        $lle1->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "") . " switch lessons " . $lesson1Id . '/' . $lesson2Id . ' remove 1';
        $lle1->save();

        $lesson2 = Lesson::find($lesson2Id);
        $lesson2->state = 0;
        $lesson2->save();
        $lle2 = new LessonLogEvent();
        $lle2->old_lesson_id = $lesson2->id;
        $lle2->new_lesson_id = 0;
        $lle2->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $lle2->public_comment = "";
        $lle2->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "") . " switch lessons " . $lesson1Id . '/' . $lesson2Id . ' remove 2';
        $lle2->save();

        $new_lesson1 = new Lesson();
        $new_lesson1->state = 1;
        $new_lesson1->discipline_teacher_id = $lesson1->discipline_teacher_id;
        $new_lesson1->calendar_id = $lesson2->calendar_id;
        $new_lesson1->ring_id = $lesson2->ring_id;
        $new_lesson1->auditorium_id = $lesson2->auditorium_id;
        $new_lesson1->description = '';
        $new_lesson1->save();
        $lle3 = new LessonLogEvent();
        $lle3->old_lesson_id = 0;
        $lle3->new_lesson_id = $new_lesson1->id;
        $lle3->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $lle3->public_comment = "";
        $lle3->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "") . " switch lessons " . $lesson1Id . '/' . $lesson2Id . ' add 1';
        $lle3->save();

        $new_lesson2 = new Lesson();
        $new_lesson2->state = 1;
        $new_lesson2->discipline_teacher_id = $lesson2->discipline_teacher_id;
        $new_lesson2->calendar_id = $lesson1->calendar_id;
        $new_lesson2->ring_id = $lesson1->ring_id;
        $new_lesson2->auditorium_id = $lesson1->auditorium_id;
        $new_lesson2->description = '';
        $new_lesson2->save();
        $lle4 = new LessonLogEvent();
        $lle4->old_lesson_id = 0;
        $lle4->new_lesson_id = $new_lesson2->id;
        $lle4->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $lle4->public_comment = "";
        $lle4->hidden_comment = (($user !== null) ? $user->id . " @ " . $user->name . ": " : "") . " switch lessons " . $lesson1Id . '/' . $lesson2Id . ' add 2';
        $lle4->save();
    }
}
