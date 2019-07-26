<?php

namespace App\DomainClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Integer;
use stdClass;

class Discipline extends Model
{
    public $timestamps = false;

    // attestation
    // 0: "нет",
    // 1: "зачёт",
    // 2: "экзамен",
    // 3: "зачёт и экзамен",
    // 4: "зачёт с оценкой"

    public static function all_attestation()
    {
        $attestation = [];

        $attestationItem = new stdClass();
        $attestationItem->id = 0;
        $attestationItem->name = "нет";
        $attestation[] = $attestationItem;

        $attestationItem = new stdClass();
        $attestationItem->id = 1;
        $attestationItem->name = "зачёт";
        $attestation[] = $attestationItem;

        $attestationItem = new stdClass();
        $attestationItem->id = 2;
        $attestationItem->name = "экзамен";
        $attestation[] = $attestationItem;

        $attestationItem = new stdClass();
        $attestationItem->id = 3;
        $attestationItem->name = "зачёт и экзамен";
        $attestation[] = $attestationItem;

        $attestationItem = new stdClass();
        $attestationItem->id = 4;
        $attestationItem->name = "зачёт с оценкой";
        $attestation[] = $attestationItem;

        return $attestation;
    }

    public static function attestation_string(int $attestation_id)
    {
        $attestation = [];
        $attestation[0] = "нет";
        $attestation[1] = "зачёт";
        $attestation[2] = "экзамен";
        $attestation[3] = "зачёт и экзамен";
        $attestation[4] = "зачёт с оценкой";

        return $attestation[$attestation_id];
    }

    public static function TDIdsFromDisciplineIds($disciplineIds)
    {
        return DB::table('discipline_teacher')
            ->whereIn('discipline_id', $disciplineIds)
            ->select('id')
            ->get()
            ->map(function($item) { return $item->id;})
            ->toArray();
    }

    public static function IdsFromTeacherId($teacherId)
    {
        return DB::table('discipline_teacher')
            ->where('teacher_id', '=', $teacherId)
            ->select('id')
            ->get()
            ->map(function($item) { return $item->id;});
    }

    public static function DTall()
    {
        return DB::table('discipline_teacher')->get();
    }

    public function student_group()
    {
        return $this->belongsTo(StudentGroup::class);
    }

    public function teacher()
    {
        return $this->belongsToMany(Teacher::class, 'discipline_teacher')->withPivot('id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public static function ListFormIds($IdsList) {
        return DB::table('disciplines')
            ->whereIn('id', $IdsList)
            ->get();
    }

    public static function ListFormIdsWithGroupNames($IdsList) {
        return DB::table('disciplines')
            ->whereIn('disciplines.id', $IdsList)
            ->join('student_groups', 'student_group_id', '=', 'student_groups.id')
            ->select('disciplines.*', 'student_groups.name as group_name')->whereIn('disciplines.id', $IdsList)
            ->get();
    }

    public static function IdsFromGroupId($groupId) {
        $groupIds = StudentGroup::GetGroupsOfStudentFromGroup($groupId);
        return static::IdsFromGroupIdsStraight($groupIds);
    }

    public static function IdsFromGroupIdsStraight($groupIds) {
        return DB::table('disciplines')
            ->whereIn('student_group_id', $groupIds)
            ->select('id')
            ->get()
            ->map(function($item) { return $item->id;});
    }
}
