<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Exam extends Model
{
    public $timestamps = false;
//    public static function FromGroupId($groupId)
//    {
//        $emptyDate = "01.01.2020 0:00";
//        $groupDisciplineIds = Discipline::IdsFromGroupId($groupId);
//        $exams =  DB::table('exams')
//            ->where('is_active', true)
//            ->whereIn('discipline_id', $groupDisciplineIds)
//            ->join('disciplines', 'discipline_id', '=', 'disciplines.id')
//            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
//            ->leftJoin('auditoriums as aud1', 'consultation_auditorium_id', '=', 'aud1.id')
//            ->leftJoin('auditoriums as aud2', 'exam_auditorium_id', '=', 'aud2.id')
//            ->select('exams.id', 'discipline_id',
//                'exams.consultation_datetime', 'exams.exam_datetime',
//                'disciplines.name as disc_name', 'student_groups.name as group_name',
//                'aud1.name as cons_aud', 'aud2.name as exam_aud')
//            ->get();
//        $exams->map(function ($exam) use ($emptyDate) {
//            $exam->teacher_fio = Discipline_Teacher::TeacherFioFromDisciplineId($exam->discipline_id);
//            if ($exam->consultation_datetime == $emptyDate) {
//                $exam->consultation_datetime = null;
//            }
//            if ($exam->exam_datetime == $emptyDate) {
//                $exam->exam_datetime = null;
//            }
//        });
//        return $exams;
//    }

    public static function FromGroupId_OldAPI($groupId)
    {
        $groupDisciplineIds = Discipline::IdsFromGroupId($groupId);
        $exams =  DB::table('exams')
            ->where('is_active', true)
            ->whereIn('exams.discipline_id', $groupDisciplineIds)
            ->join('disciplines', 'exams.discipline_id', '=', 'disciplines.id')
            ->join('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->leftJoin('auditoriums as aud1', 'consultation_auditorium_id', '=', 'aud1.id')
            ->leftJoin('auditoriums as aud2', 'exam_auditorium_id', '=', 'aud2.id')
            ->select('exams.id', 'exams.discipline_id',
                'exams.consultation_datetime', 'exams.exam_datetime',
                'disciplines.name as disc_name', 'student_groups.name as group_name',
                'aud1.name as cons_aud', 'aud2.name as exam_aud',
                'aud1.id as cons_aud_id', 'aud2.id as exam_aud_id',
                'teachers.fio as teacher_fio')
            ->get();

        return $exams;
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function consultation_auditorium()
    {
        return $this->belongsTo(Auditorium::class);
    }

    public function exam_auditorium()
    {
        return $this->belongsTo(Auditorium::class);
    }
}
