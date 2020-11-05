<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\Discipline;
use App\DomainClasses\Lesson;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class LessonDescriptionController extends Controller
{
    public function teacherEditIndex() {
        $userId = Auth::id();

        $teacher = DB::table('teachers')
            ->where('user_id', '=', $userId)
            ->first();

        if ($userId <= 3) {
            return Redirect::to("/admin");
        } else {
            $teachers = Teacher::all()->sortBy('fio');

            $weekCount = Calendar::WeekCount();

            return view('teacher.weekSchedule', compact('teachers', 'weekCount', 'teacher'));
        }
    }

    public function updateLessonDescription(Request $request) {
        $input = $request->all();

        $lessonId = $input["lessonId"];
        $description = $input["description"];

        $lesson = Lesson::find($lessonId);

        if ($lesson !== null) {
            $lesson->description = $description;
            $lesson->save();
        }

        return $lesson;
    }

    public function showIndex() {
        $dates = Calendar::all()->sortBy('date');
        $groups = StudentGroup::FacultiesGroups();

        return view('teacher.dayIndex', compact('dates', 'groups'));
    }

    public function GroupDay(Request $request) {
        $input = $request->all();

        $groupId = $input["groupId"];
        $calendarId = $input["calendarId"];

        $groupDisciplineIds = Discipline::IdsFromGroupId($groupId);
        $disciplineTeacherIds = Discipline::TDIdsFromDisciplineIds($groupDisciplineIds);

        $lessonsList = Lesson::GetDailyTFDLessonsWithDescription($disciplineTeacherIds, $calendarId);

        return $lessonsList;
    }
}
