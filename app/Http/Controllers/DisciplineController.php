<?php

namespace App\Http\Controllers;

use App\DomainClasses\Discipline;
use App\DomainClasses\Faculty;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisciplineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $input = $request->all();

        $groupId = -1;
        if (isset($input['groupId']))
        {
            $groupId = $input["groupId"];
        }

        $studentGroups = StudentGroup::allSorted()->toArray();

        $teachers = Teacher::all()->sortBy('fio');

        return view('disciplines.index', compact('studentGroups', 'groupId', 'teachers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $studentGroups = StudentGroup::all()->sortBy('name');

        return view('disciplines.create', compact('studentGroups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newDiscipline = new Discipline();
        $newDiscipline->name = $request->name;
        $newDiscipline->student_group_id = $request->student_group_id;

        $newDiscipline->attestation = $request->attestation;
        $newDiscipline->auditorium_hours = "0";
        $newDiscipline->auditorium_hours_per_week = $request->auditorium_hours_per_week;
        $newDiscipline->lecture_hours = "0";
        $newDiscipline->practical_hours = "0";
        $newDiscipline->active = ($request->active == null) ? "0" : 1;
        $newDiscipline->type = $request->type;

        $newDiscipline->save();

        return redirect('disciplines?groupId=' . $newDiscipline->student_group_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Discipline  $discipline
     * @return \Illuminate\Http\Response
     */
    public function show(Discipline $discipline)
    {
        $discipline = DB::table('disciplines')
            ->where('disciplines.id', '=', $discipline->id)
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->select('disciplines.*', 'student_groups.name as groupName', 'teachers.fio as teacherFio',
                'discipline_teacher.id as discipline_teacher_id')
            ->first();

        $teachers = Teacher::all()->sortBy('fio');

        return view('disciplines.show', compact('discipline', 'teachers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Discipline  $discipline
     * @return \Illuminate\Http\Response
     */
    public function edit(Discipline $discipline)
    {
        $discipline = DB::table('disciplines')
            ->where('disciplines.id', '=', $discipline->id)
            ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
            ->select('disciplines.*', 'student_groups.name as groupName')
            ->first();

        $studentGroups = StudentGroup::allSorted();

        return view('disciplines.edit', compact('discipline', 'studentGroups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Discipline  $discipline
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discipline $discipline)
    {
        $discipline->name = $request->name;
        $discipline->attestation = $request->attestation;
        $discipline->student_group_id = $request->student_group_id;
        $discipline->auditorium_hours_per_week = $request->auditorium_hours_per_week;
        $discipline->active = ($request->active == null) ? "0" : 1;
        $discipline->type = $request->type;
        $discipline->save();

        return redirect('disciplines?groupId=' . $discipline->student_group_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Discipline  $discipline
     * @return array
     */
    public function destroy(Discipline $discipline)
    {
        $disciplineTeachersCount = $discipline->teacher->count();

        if ($disciplineTeachersCount !== 0)
        {
            $discipline = DB::table('disciplines')
                ->where('disciplines.id', '=', $discipline->id)
                ->join('student_groups', 'student_groups.id' , '=', 'disciplines.student_group_id')
                ->select('disciplines.*', 'student_groups.name as groupName')
                ->first();

            return array("error" => "Дисциплину нельзя удалить. Ей назначен учитель.");
        }

        Discipline::destroy($discipline->id);

        return array("success" => "ok");
    }

    public function DisciplinesByGroupInfo(Request $request) {
        $input = $request->all();

        if (!isset($input['groupId']))
        {
            return array("error" => "groupId обязательный параметр");
        }

        $groupId = $input['groupId'];

        $groupIds = StudentGroup::GetGroupsOfStudentFromGroup($groupId);

        $disciplines = DB::table('disciplines')
            ->whereIn('disciplines.student_group_id', $groupIds)
            ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->select('disciplines.id as disciplineId',
                'disciplines.name as disciplineName',
                'disciplines.active',
                'teachers.id as teacherId',
                'teachers.fio as teacherFio',
                'student_groups.name as studentGroupName',
                'discipline_teacher.id as tfdId')
            ->orderBy('disciplineName', 'asc')
            ->orderBy('teacherFio', 'asc')
            ->get();

        return $disciplines;
    }

    public function DisciplinesByFacultyInfo(Request $request) {
        $input = $request->all();

        if (!isset($input['facultyId']))
        {
            return array("error" => "facultyId обязательный параметр");
        }

        $facultyId = $input['facultyId'];

        $faculty = Faculty::find($facultyId);

        if ($faculty === null) return "{}";

        $facultyStudentGroups = $faculty->student_groups;

        $studentIdsByFacultyGroup = array();
        foreach($facultyStudentGroups as $facultyStudentGroup) {
            $studentIds = StudentGroup::StudentIdsFromGroupId($facultyStudentGroup->id);
            $studentIdsByFacultyGroup[$facultyStudentGroup->id] = $studentIds;
        }

        $facultyStudentIds = StudentGroup::StudentIdsFromGroupIds($facultyStudentGroups->pluck('id'))->toArray();
        sort($facultyStudentIds);
        $facultyStudentIds = array_values(array_unique($facultyStudentIds));

        $disciplineNames = array();

        foreach ($facultyStudentGroups as $facultyStudentGroup) {

            $groupId = $facultyStudentGroup->id;

            $groupIds = StudentGroup::GetGroupsOfStudentFromGroup($groupId);

            $disciplines = DB::table('disciplines')
                ->whereIn('disciplines.student_group_id', $groupIds)
                ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
                ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->select('disciplines.id as disciplineId',
                    'disciplines.name as disciplineName',
                    'teachers.id as teacherId',
                    'teachers.fio as teacherFio',
                    'student_groups.id as studentGroupId',
                    'student_groups.name as studentGroupName',
                    'discipline_teacher.id as tfdId')
                ->orderBy('disciplineName', 'asc')
                ->orderBy('teacherFio', 'asc')
                ->get();

            foreach ($disciplines as $discipline) {
                $tfdIds = array_key_exists($discipline->disciplineName, $disciplineNames) ?
                    array_column($disciplineNames[$discipline->disciplineName]["Disciplines"], 'tfdId') :
                    array();

                if (!in_array($discipline->tfdId, $tfdIds)) {

                    if (!array_key_exists($discipline->disciplineName, $disciplineNames)) {
                        $disciplineNames[$discipline->disciplineName] = array();
                        $disciplineNames[$discipline->disciplineName]["Disciplines"] = array();
                    }

                    $disciplineNames[$discipline->disciplineName]["Disciplines"][] = $discipline;
                }
            }
        }

        foreach ($disciplineNames as $disciplineName => $disciplines) {
            $GroupsByTeacherFio = array();
            $disciplineStudentIds = array();
            $disciplineGroups = array();

            foreach ($disciplines["Disciplines"] as $discipline) {
                $studentGroupStudentIds = StudentGroup::StudentIdsFromGroupId($discipline->studentGroupId)->toArray();

                if ($discipline->teacherId !== null) {
                    $disciplineStudentIds = array_merge($disciplineStudentIds, $studentGroupStudentIds);
                }

                if (!array_key_exists($discipline->teacherFio, $GroupsByTeacherFio)) {
                    $GroupsByTeacherFio[$discipline->teacherFio] = array();
                }

                if ($discipline->teacherFio === null) {
                    $GroupsByTeacherFio[$discipline->teacherFio][] = "-1";
                } else {
                    $GroupsByTeacherFio[$discipline->teacherFio][] = array(
                        "groupId" => $discipline->studentGroupId,
                        "groupName" => $discipline->studentGroupName
                    );
                }

                $disciplineGroupsInfo = array();
                foreach($studentIdsByFacultyGroup as $groupId => $groupStudentIds) {
                    $groupStudentIdsArray = $groupStudentIds->toArray();
                    $intersect = array_intersect($groupStudentIdsArray, $studentGroupStudentIds);

                    $disciplineGroupsInfo[$groupId] = (count($intersect) !== 0) && ($discipline->teacherId !== null);
                }

                $discipline->GroupsInfo = $disciplineGroupsInfo;
            }

            foreach($studentIdsByFacultyGroup as $groupId => $groupStudentIds) {
                $groupStudentIdsArray = $groupStudentIds->toArray();
                $intersect = array_intersect($groupStudentIdsArray, $disciplineStudentIds);

                $disciplineGroups[$groupId] = (count($intersect) !== 0);
            }

            $groupsAndTeachers = array();

            $teacherStudentIds = StudentGroup::StudentIdsFromGroupIds(array_column(reset($GroupsByTeacherFio), "groupId"))->toArray();
            sort($teacherStudentIds);
            $teacherStudentIds = array_values(array_unique($teacherStudentIds));

            if (count($GroupsByTeacherFio) === 1 && $teacherStudentIds == $facultyStudentIds) {
                $groupsAndTeachers[] = array_keys($GroupsByTeacherFio)[0];
            } else {
                foreach ($GroupsByTeacherFio as $teacherFio => $groups) {
                    $groupNames = array_column($groups, "groupName");
                    sort($groupNames);

                    if (!((count($groups) === 1) && ($groups[0] === "-1"))) {
                        $groupsAndTeachers[] = implode(" + ", $groupNames) . " - " . $teacherFio;
                    }
                }
            }

            sort($groupsAndTeachers);
            $disciplineNames[$discipline->disciplineName]["GroupsAndTeachers"] = $groupsAndTeachers;
            $disciplineNames[$discipline->disciplineName]["DisciplineGroups"] = $disciplineGroups;
        }

        ksort($disciplineNames);
        return $disciplineNames;
    }

    public function CopyFromGroupToGroup(Request $request) {
        $input = $request->all();

        if (!isset($input['sourceGroupId']) || !isset($input['destinationGroupId']))
        {
            return array("error" => "sourceGroupId и destinationGroupId обязательные параметр");
        }

        $sourceGroupId = $input['sourceGroupId'];
        $destinationGroupId = $input['destinationGroupId'];

        $sourceGroupDisciplines = Discipline::ListFromGroupId($sourceGroupId)->toArray();
        $sourceGroupDisciplineNames =  array_values(array_unique(array_column($sourceGroupDisciplines, 'name')));
        sort($sourceGroupDisciplineNames);

        $result = array();
        foreach ($sourceGroupDisciplineNames as $disciplineName) {
            $sourceDiscipline = null;
            foreach($sourceGroupDisciplines as $discipline) {
                if ($discipline->name == $disciplineName) {
                    $sourceDiscipline = $discipline;
                    break;
                }
            }

            $newDiscipline = new Discipline();
            $newDiscipline->name = $disciplineName;
            $newDiscipline->attestation = $sourceDiscipline->attestation;
            $newDiscipline->student_group_id = $destinationGroupId;
            $newDiscipline->auditorium_hours_per_week = $sourceDiscipline->auditorium_hours_per_week;

            $newDiscipline->auditorium_hours = "0";
            $newDiscipline->lecture_hours = "0";
            $newDiscipline->practical_hours = "0";

            $newDiscipline->save();
            $result[] = $newDiscipline;
        }

        return $result;
    }

    public function facultyDisciplines(Request $request) {
        $input = $request->all();

        if (!isset($input['facultyId']))
        {
            return array("error" => "facultyId обязательный параметр");
        }
        $facultyId = $input['facultyId'];

        $facultyImmediateGroupIds = DB::table('faculty_student_group')
            ->where('faculty_student_group.faculty_id', '=', $facultyId)
            ->select('student_group_id')
            ->get()
            ->map(function($item) { return $item->student_group_id;})
            ->toArray();

        $facultyGroupsConnected = StudentGroup::GetGroupsOfStudentFromGroupsConnected($facultyImmediateGroupIds);

        $facultyGroupsIds = array_keys($facultyGroupsConnected);

        $facultyDisciplines = DB::table('disciplines')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->leftJoin('discipline_teacher', 'disciplines.id', '=', 'discipline_teacher.discipline_id')
            ->leftJoin('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->whereIn('disciplines.student_group_id', $facultyGroupsIds)
            ->select('disciplines.name',
                'student_groups.id as studentGroupsId', 'student_groups.name as studentGroupsName',
                'teachers.fio as teachersFio')
            ->get();

        foreach ($facultyDisciplines as $facultyDiscipline) {
            $facultyDiscipline->facultyGroupIds = $facultyGroupsConnected[$facultyDiscipline->studentGroupsId];
        }

        $facultyDisciplines = $facultyDisciplines->groupBy('name');

        $result = array();
        foreach ($facultyDisciplines as $disciplineName => $disciplines) {
            $result[$disciplineName] = array();

            foreach($disciplines as $discipline) {
                foreach ($discipline->facultyGroupIds as $facultyGroupId) {
                    if (!array_key_exists($facultyGroupId, $result[$disciplineName])) {
                        $result[$disciplineName][$facultyGroupId] = array();
                    }
                    $result[$disciplineName][$facultyGroupId][] = $discipline;
                }
            }
        }

        $facultyGroups = DB::table('faculty_student_group')
            ->where('faculty_student_group.faculty_id', '=', $facultyId)
            ->join('student_groups', 'faculty_student_group.student_group_id', '=', 'student_groups.id')
            ->select('student_group_id', 'student_groups.name as studentGroupsName')
            ->get()
            ->toArray();

        usort($facultyGroups, function($a, $b)
        {
            return strcmp($a->studentGroupsName, $b->studentGroupsName);
        });

        return array (
            'groups' => $facultyGroups,
            'disciplines' => $result
        );

    }
}
