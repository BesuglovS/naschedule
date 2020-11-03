<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\Lesson;
use App\DomainClasses\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class LessonDescriptionController extends Controller
{
    public function teacherEditIndex() {
        $user = Auth::user();
        if ($user->id <= 3) {
            return Redirect::to("/admin");
        } else {
            $teachers = Teacher::all()->sortBy('fio');

            $weekCount = Calendar::WeekCount();

            return view('teacher.weekSchedule', compact('teachers', 'weekCount'));
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
}
