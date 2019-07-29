<?php

namespace App\Http\Controllers;

use App\DomainClasses\Auditorium;
use App\DomainClasses\Exam;
use App\DomainClasses\ExamLogEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $exams = DB::table('exams')
            ->join('disciplines', 'disciplines.id' , '=', 'exams.discipline_id')
            ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->leftJoin('auditoriums as consAud', 'consAud.id' , '=', 'exams.consultation_auditorium_id')
            ->leftJoin('auditoriums as examAud', 'examAud.id' , '=', 'exams.exam_auditorium_id')
            ->select('exams.*', 'disciplines.name as disciplineName',
                'consAud.name as consultationAuditoriumName', 'examAud.name as examAuditoriumName',
                'student_groups.name as student_group_name', 'teachers.fio as teacherFio')
            ->where('exams.is_active', '=', '1')
            ->get();

        $exams = $exams->sort(function($a, $b) {
            if ($a->student_group_name == $b->student_group_name) {
                return ($a->disciplineName < $b->disciplineName) ? -1 : 1;
            }

            $num1 = explode(" ", $a->student_group_name)[0];
            $num2 = explode(" ", $b->student_group_name)[0];

            if ($num1 == $num2)
            {
                return $a->student_group_name < $b->student_group_name ? -1 : 1;
            }
            else
            {
                return ($num1 < $num2) ? -1 : 1;
            }
        });

        return view('exams.index', compact('exams'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $auditoriums = Auditorium::allSorted();

        $disciplines = DB::table('disciplines')
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->join('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->select('disciplines.*', 'student_groups.name as groupName')
            ->get();

        $disciplines = $disciplines->sort(function($a, $b) {
            if ($a->groupName == $b->groupName) {
                return ($a->name < $b->name) ? -1 : 1;
            }

            $num1 = explode(" ", $a->groupName)[0];
            $num2 = explode(" ", $b->groupName)[0];

            if ($num1 == $num2)
            {
                return $a->groupName < $b->groupName ? -1 : 1;
            }
            else
            {
                return ($num1 < $num2) ? -1 : 1;
            }
        });

        return view('exams.create', compact('auditoriums', 'disciplines'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newExam = new Exam();
        $newExam->is_active = "1";
        $newExam->discipline_id = $request->discipline_id;
        $newExam->consultation_datetime = Carbon::parse($request->consultation_datetime)->format('Y-m-d H:i:s');
        $newExam->consultation_auditorium_id = $request->consultation_auditorium_id;
        $newExam->exam_datetime = Carbon::parse($request->exam_datetime)->format('Y-m-d H:i:s');
        $newExam->exam_auditorium_id = $request->exam_auditorium_id;
        $newExam->save();

        $ele = new ExamLogEvent();
        $ele->old_exam_id = 0;
        $ele->new_exam_id = $newExam->id;
        $ele->datetime = Carbon::now()->format('Y-m-d H:i:s');
        $ele->save();

        return redirect('/exams');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Exam  $exam
     * @return \Illuminate\Http\Response
     */
    public function show(Exam $exam)
    {
        $exam = DB::table('exams')
            ->join('disciplines', 'disciplines.id' , '=', 'exams.discipline_id')
            ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->leftJoin('auditoriums as consAud', 'consAud.id' , '=', 'exams.consultation_auditorium_id')
            ->leftJoin('auditoriums as examAud', 'examAud.id' , '=', 'exams.exam_auditorium_id')
            ->select('exams.*', 'disciplines.name as disciplineName',
                'disciplines.id as disciplineId',
                'consAud.name as consultationAuditoriumName', 'examAud.name as examAuditoriumName',
                'student_groups.name as student_group_name', 'teachers.fio as teacherFio')
            ->where('exams.id', '=', $exam->id)
            ->first();

        $examIds = DB::table('exams')
            ->where('exams.discipline_id', '=', $exam->discipline_id)
            ->get()
            ->pluck('id');

        $exams = DB::table('exams')
            ->join('disciplines', 'disciplines.id' , '=', 'exams.discipline_id')
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->leftJoin('auditoriums as consAud', 'consAud.id' , '=', 'exams.consultation_auditorium_id')
            ->leftJoin('auditoriums as examAud', 'examAud.id' , '=', 'exams.exam_auditorium_id')
            ->select('exams.*', 'disciplines.name as disciplineName',
                'disciplines.id as disciplineId',
                'consAud.name as consultationAuditoriumName', 'examAud.name as examAuditoriumName',
                'student_groups.name as student_group_name')
            ->whereIn('exams.id', $examIds)
            ->get();

        $examsDict = array();
        foreach ($exams as $examItem)
        {
            $examsDict[$examItem->id] = $examItem;
        }

        $examEvents = DB::table('exam_log_events')
            ->whereIn('new_exam_id', $examIds)
            ->orderBy('datetime', 'asc')
            ->get();

        foreach ($examEvents as $examEvent)
        {
            $examEvent->old_exam = $examsDict[$examEvent->old_exam_id];
            $examEvent->new_exam = $examsDict[$examEvent->new_exam_id];
        }

        return view('exams.show', compact('exam', 'examEvents'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Exam  $exam
     * @return \Illuminate\Http\Response
     */
    public function edit(Exam $exam)
    {
        $exam = DB::table('exams')
            ->join('disciplines', 'disciplines.id' , '=', 'exams.discipline_id')
            ->leftJoin('auditoriums as consAud', 'consAud.id' , '=', 'exams.consultation_auditorium_id')
            ->leftJoin('auditoriums as examAud', 'examAud.id' , '=', 'exams.exam_auditorium_id')
            ->select('exams.*', 'disciplines.name as disciplineName',
                'consAud.name as consultationAuditoriumName', 'examAud.name as examAuditoriumName')
            ->where('exams.id', '=', $exam->id)
            ->first();

        $auditoriums = Auditorium::all();

        $disciplines = DB::table('disciplines')
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->select('disciplines.*', 'student_groups.name as groupName')
            ->get();

        return view('exams.edit', compact('exam', 'auditoriums', 'disciplines'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Exam  $exam
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Exam $exam)
    {
        $exam->is_active = "0";
        $exam->save();

        $newExam = new Exam();
        $newExam->is_active = "1";
        $newExam->discipline_id = $request->discipline_id;
        $newExam->consultation_datetime = Carbon::parse($request->consultation_datetime)->format('Y-m-d H:i:s');
        $newExam->consultation_auditorium_id = $request->consultation_auditorium_id;
        $newExam->exam_datetime = Carbon::parse($request->exam_datetime)->format('Y-m-d H:i:s');
        $newExam->exam_auditorium_id = $request->exam_auditorium_id;
        $newExam->save();

        $ele = new ExamLogEvent();
        $ele->old_exam_id = $exam->id;
        $ele->new_exam_id = $newExam->id;
        $ele->datetime = Carbon::now()->format('Y-m-d H:i:s');
        $ele->save();

        return redirect('exams');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Exam  $exam
     * @return \Illuminate\Http\Response
     */
    public function destroy(Exam $exam)
    {
        $exam->is_active = "0";
        $exam->save();

        $ele = new ExamLogEvent();
        $ele->old_exam_id = $exam->id;
        $ele->new_exam_id = 0;
        $ele->datetime = Carbon::now()->format('Y-m-d H:i:s');
        $ele->save();

        return redirect('exams');
    }
}
