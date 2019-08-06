<?php

namespace App\Http\Controllers;

use App\DomainClasses\Lesson;
use App\DomainClasses\LessonLogEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
                $newLle->hidden_comment = "";
                $newLle->save();

                $l = Lesson::find($lesson->id);
                $l->state = 0;
                $l->save();
            }
        }

        return $lessons;
    }
}
