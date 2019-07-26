<?php

namespace App\Http\Controllers;

use App\DomainClasses\Discipline;
use App\DomainClasses\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisciplineTeacherController extends Controller
{
    public function destroy(Request $request, int $discipline_teacher_id)
    {
        $lessonsCount = DB::table('lessons')
            ->where('lessons.discipline_teacher_id', '=', $discipline_teacher_id)
            ->count();

        if ($lessonsCount !== 0)
        {
            $dt = DB::table('discipline_teacher')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->where('discipline_teacher.id', '=', $discipline_teacher_id)
                ->select('disciplines.name as disciplineName', 'teachers.fio as teacherFio',
                    'student_groups.name as groupName')
                ->first();

            return back()->with('error', 'Нельзя удалить связь учителя с преметом (' .
                $dt->teacherFio . ' / ' . $dt->groupName . ' ' . $dt->disciplineName .
                '). Для этой связки есть или были назначены занятия.');
        }

        DB::table('discipline_teacher')
            ->where('id', $discipline_teacher_id)
            ->take(1)
            ->delete();

        return back();
    }

    public function store(Request $request)
    {
        $teacher = Teacher::find($request->teacher_id);
        $discipline = Discipline::find($request->discipline_id);

        $teacher->disciplines()->attach($discipline);

        return back();
    }
}
