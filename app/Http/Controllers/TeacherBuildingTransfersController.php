<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Teacher;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherBuildingTransfersController extends Controller
{
    public function index() {
        $calendars = Calendar::all()->sortBy('date');

        return view('main.teacherBuildingTransfers', compact('calendars'));
    }

    public function DailyTransfers(Request $request) {
        $input = $request->all();

        if(!isset($input['calendarId']))
        {
            return array("error" => "calendarId - обязательный параметр");
        }
        $calendarId = $input["calendarId"];

        $dailyLessons = DB::table('lessons')
            ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
            ->join('rings', 'lessons.ring_id', '=', 'rings.id')
            ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
            ->join('buildings', 'auditoriums.building_id', '=', 'buildings.id')
            ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
            ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
            ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
            ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
            ->where('lessons.state', '=', 1)
            ->where('lessons.calendar_id', '=', $calendarId)
            ->select('lessons.*',
                'teachers.id as teachersId',
                'teachers.fio as teachersFio',
                'disciplines.name as disciplinesName',
                'student_groups.id as studentGroupsId',
                'student_groups.name as studentGroupsName',
                'calendars.date as calendarsDate',
                'rings.time as ringsTime',
                'buildings.id as buildingsId',
                'buildings.name as buildingsName'
            )
            ->get();

        $dailyLessonsByTeacher = array();
        foreach ($dailyLessons as $lesson) {
            if (!array_key_exists($lesson->teachersId, $dailyLessonsByTeacher)) {
                $dailyLessonsByTeacher[$lesson->teachersId] = array();
            }

            $dailyLessonsByTeacher[$lesson->teachersId][] = $lesson;
        }

        $result = array();

        foreach ($dailyLessonsByTeacher as $teacherId => $teacherLessons) {
            if (count($teacherLessons) === 1) continue;

            $teacherLessonsSorted = $teacherLessons;
            usort($teacherLessonsSorted, function($a, $b) {
                $aCarbon = Carbon::createFromFormat('H:i:s', $a->ringsTime);
                $bCarbon = Carbon::createFromFormat('H:i:s', $b->ringsTime);

                $diff = $bCarbon->diffInSeconds($aCarbon, false);

                if ($diff === 0) return 0;
                return ($diff < 0) ? -1 : 1;
            });

            for($i = 0; $i < count($teacherLessonsSorted) - 1; $i++) {
                $aCarbon = Carbon::createFromFormat('H:i:s', $teacherLessonsSorted[$i]->ringsTime);
                $bCarbon = Carbon::createFromFormat('H:i:s', $teacherLessonsSorted[$i+1]->ringsTime);
                $diff = $aCarbon->diffInSeconds($bCarbon, false);

                $aCarbon->addMinutes(40);


                if (($teacherLessonsSorted[$i]->buildingsId !== $teacherLessonsSorted[$i+1]->buildingsId) &&
                    ($diff <= 3600)) {
                    $result[] = array(
                        'teachersFio' => $teacherLessonsSorted[$i]->teachersFio,
                        'timeFrom' => $aCarbon->format('H:i'),
                        'buildingFromId' => $teacherLessonsSorted[$i]->buildingsId,
                        'buildingFrom' => $teacherLessonsSorted[$i]->buildingsName,
                        'groupFrom' => $teacherLessonsSorted[$i]->studentGroupsName,
                        'disciplineFrom' => $teacherLessonsSorted[$i]->disciplinesName,
                        'timeTo' => $bCarbon->format('H:i'),
                        'buildingTo' => $teacherLessonsSorted[$i+1]->buildingsName,
                        'buildingToId' => $teacherLessonsSorted[$i+1]->buildingsId,
                        'groupTo' => $teacherLessonsSorted[$i+1]->studentGroupsName,
                        'disciplineTo' => $teacherLessonsSorted[$i+1]->disciplinesName,
                        'diff' => $diff
                    );
                }
            }
        }

        usort($result, function($a, $b) {
            $aCarbon = Carbon::createFromFormat('H:i', $a['timeFrom']);
            $bCarbon = Carbon::createFromFormat('H:i', $b['timeFrom']);

            $diff = $bCarbon->diffInSeconds($aCarbon, false);

            if ($diff === 0) {
                $aBuldingsStamp = $a['buildingFromId'] . $a['buildingToId'];
                $bBuldingsStamp = $a['buildingFromId'] . $a['buildingToId'];

                if ($aBuldingsStamp === $bBuldingsStamp) {
                    if ($a['teachersFio'] === $b['teachersFio']) return 0;
                    return ($a['teachersFio'] < $b['teachersFio']) ? -1 : 1;
                }

                return ($aBuldingsStamp < $bBuldingsStamp) ? -1 : 1;
            };
            return ($diff < 0) ? -1 : 1;
        });

        return $result;
    }
}
