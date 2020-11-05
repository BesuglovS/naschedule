<?php

namespace App\Http\Controllers;

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Discipline;
use App\DomainClasses\Faculty;
use App\DomainClasses\Lesson;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
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

    public function onlineIndex() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $weekCount = Calendar::WeekCount();

        $weeks = Calendar::Weeks();

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);
        $faculties = Faculty::all()->sortBy('sorting_order');

        return view('teacher.online', compact('weekCount', 'weeks', 'currentWeek', 'faculties'));
    }

    public function OnlineAction(Request $request) {
        $input = $request->all();

        $week = $input["week"];
        $facultyId = $input["facultyId"];
        $weekCalendarIds = Calendar::CalendarsFromWeek($week)->pluck('id')->toArray();

        $lessonList = array();

        if ($facultyId == 0) {
            $faculties = Faculty::all();
            foreach($faculties as $faculty) {
                $data = LessonController::GetFacultyLessonsForCalendarIds($faculty->id, $weekCalendarIds)->toArray();
                $lessonList = array_merge($lessonList, $data);
            }
        } else {
            $lessonList = LessonController::GetFacultyLessonsForCalendarIds($facultyId, $weekCalendarIds);
        }

        $byGrade = array();
        $byGroup = array();
        $byTeacherFio = array();

        foreach ($lessonList as $lesson) {
            $lesson->grade = explode(' ', $lesson->group_name)[0];

            $carbonDate = Carbon::createFromFormat('Y-m-d', $lesson->lesson_date);
            $dowRu = array( 1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб", 7 => "Вс");
            $dow = $carbonDate->dayOfWeekIso;
            $lesson->dow = $dowRu[$dow];

            if ((str_contains(mb_strtolower($lesson->description), 'онлайн')) ||
                (str_contains(mb_strtolower($lesson->description), 'он лайн')) ||
                (str_contains(mb_strtolower($lesson->description), 'он-лайн')) ||
                (str_contains(mb_strtolower($lesson->description), 'zoom.us')) ||
                (str_contains(mb_strtolower($lesson->description), 'online'))) {
                if (!array_key_exists($lesson->grade, $byGrade)) {
                    $byGrade[$lesson->grade] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGrade[$lesson->grade]['online']++;
                $byGrade[$lesson->grade]['lessons'][] = $lesson;
                $byGrade[$lesson->grade]['onlineLessons'][] = $lesson;

                if (!array_key_exists($lesson->group_name, $byGroup)) {
                    $byGroup[$lesson->group_name] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGroup[$lesson->group_name]['online']++;
                $byGroup[$lesson->group_name]['lessons'][] = $lesson;
                $byGroup[$lesson->group_name]['onlineLessons'][] = $lesson;

                if (!array_key_exists($lesson->fio, $byTeacherFio)) {
                    $byTeacherFio[$lesson->fio] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'byGroup' => array(), 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->fio]['online']++;
                $byTeacherFio[$lesson->fio]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->fio]['onlineLessons'][] = $lesson;

                if (!array_key_exists($lesson->group_name, $byTeacherFio[$lesson->fio]['byGroup'])) {
                    $byTeacherFio[$lesson->fio]['byGroup'][$lesson->group_name] = array('online' => 0, 'offline' => 0, 'empty' => 0,
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->fio]['byGroup'][$lesson->group_name]['online']++;
                $byTeacherFio[$lesson->fio]['byGroup'][$lesson->group_name]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->fio]['byGroup'][$lesson->group_name]['onlineLessons'][] = $lesson;
            } else {
                $offlineOrEmpty = 'offline';
                if ($lesson->description === '') { $offlineOrEmpty = 'empty'; }

                if (!array_key_exists($lesson->grade, $byGrade)) {
                    $byGrade[$lesson->grade] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGrade[$lesson->grade][$offlineOrEmpty]++;
                $byGrade[$lesson->grade]['lessons'][] = $lesson;
                $byGrade[$lesson->grade][$offlineOrEmpty . 'Lessons'][] = $lesson;

                if (!array_key_exists($lesson->group_name, $byGroup)) {
                    $byGroup[$lesson->group_name] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGroup[$lesson->group_name][$offlineOrEmpty]++;
                $byGroup[$lesson->group_name]['lessons'][] = $lesson;
                $byGroup[$lesson->group_name][$offlineOrEmpty . 'Lessons'][] = $lesson;

                if (!array_key_exists($lesson->fio, $byTeacherFio)) {
                    $byTeacherFio[$lesson->fio] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'byGroup' => array(), 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->fio][$offlineOrEmpty]++;
                $byTeacherFio[$lesson->fio]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->fio][$offlineOrEmpty . 'Lessons'][] = $lesson;

                if (!array_key_exists($lesson->group_name, $byTeacherFio[$lesson->fio]['byGroup'])) {
                    $byTeacherFio[$lesson->fio]['byGroup'][$lesson->group_name] = array('online' => 0, 'offline' => 0, 'empty' => 0,
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->fio]['byGroup'][$lesson->group_name][$offlineOrEmpty]++;
                $byTeacherFio[$lesson->fio]['byGroup'][$lesson->group_name]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->fio]['byGroup'][$lesson->group_name][$offlineOrEmpty . 'Lessons'][] = $lesson;
            }
        }

        foreach ($byTeacherFio as $key => $value) {
            $byTeacherFio[$key]['teacherFio'] = $key;
        }
        foreach ($byGroup as $key => $value) {
            $byGroup[$key]['groupName'] = $key;
        }



        foreach ($byGrade as $grade => $gradeItem) {
            usort($byGrade[$grade]["lessons"], function($a, $b) {
                if ($a->group_name === $b->group_name) {
                    $aDT = $a->lesson_date . " " . $a->rings_time;
                    $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                    $bDT = $b->lesson_date . " " . $b->rings_time;
                    $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->group_name, $b->group_name);
                }
            });

            usort($byGrade[$grade]["onlineLessons"], function($a, $b) {
                if ($a->group_name === $b->group_name) {
                    $aDT = $a->lesson_date . " " . $a->rings_time;
                    $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                    $bDT = $b->lesson_date . " " . $b->rings_time;
                    $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->group_name, $b->group_name);
                }
            });

            usort($byGrade[$grade]["offlineLessons"], function($a, $b) {
                if ($a->group_name === $b->group_name) {
                    $aDT = $a->lesson_date . " " . $a->rings_time;
                    $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                    $bDT = $b->lesson_date . " " . $b->rings_time;
                    $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->group_name, $b->group_name);
                }
            });

            usort($byGrade[$grade]["emptyLessons"], function($a, $b) {
                if ($a->group_name === $b->group_name) {
                    $aDT = $a->lesson_date . " " . $a->rings_time;
                    $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                    $bDT = $b->lesson_date . " " . $b->rings_time;
                    $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->group_name, $b->group_name);
                }
            });
        }

        foreach ($byGroup as $group => $groupItem) {
            usort($byGroup[$group]["lessons"], function($a, $b) {
                if ($a->group_name === $b->group_name) {
                    $aDT = $a->lesson_date . " " . $a->rings_time;
                    $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                    $bDT = $b->lesson_date . " " . $b->rings_time;
                    $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->group_name, $b->group_name);
                }
            });

            usort($byGroup[$group]["onlineLessons"], function($a, $b) {
                if ($a->group_name === $b->group_name) {
                    $aDT = $a->lesson_date . " " . $a->rings_time;
                    $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                    $bDT = $b->lesson_date . " " . $b->rings_time;
                    $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->group_name, $b->group_name);
                }
            });

            usort($byGroup[$group]["offlineLessons"], function($a, $b) {
                if ($a->group_name === $b->group_name) {
                    $aDT = $a->lesson_date . " " . $a->rings_time;
                    $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                    $bDT = $b->lesson_date . " " . $b->rings_time;
                    $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->group_name, $b->group_name);
                }
            });

            usort($byGroup[$group]["emptyLessons"], function($a, $b) {
                if ($a->group_name === $b->group_name) {
                    $aDT = $a->lesson_date . " " . $a->rings_time;
                    $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                    $bDT = $b->lesson_date . " " . $b->rings_time;
                    $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->group_name, $b->group_name);
                }
            });
        }

        foreach ($byTeacherFio as $teacher => $teacherItem) {
            usort($byTeacherFio[$teacher]["lessons"], function($a, $b) {
                $aDT = $a->lesson_date . " " . $a->rings_time;
                $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                $bDT = $b->lesson_date . " " . $b->rings_time;
                $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                if ($aCarbon->eq($bCarbon)) {
                    return 0;
                } else {
                    return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                }
            });

            usort($byTeacherFio[$teacher]["onlineLessons"], function($a, $b) {
                $aDT = $a->lesson_date . " " . $a->rings_time;
                $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                $bDT = $b->lesson_date . " " . $b->rings_time;
                $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                if ($aCarbon->eq($bCarbon)) {
                    return 0;
                } else {
                    return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                }
            });

            usort($byTeacherFio[$teacher]["offlineLessons"], function($a, $b) {
                $aDT = $a->lesson_date . " " . $a->rings_time;
                $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                $bDT = $b->lesson_date . " " . $b->rings_time;
                $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                if ($aCarbon->eq($bCarbon)) {
                    return 0;
                } else {
                    return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                }
            });

            usort($byTeacherFio[$teacher]["emptyLessons"], function($a, $b) {
                $aDT = $a->lesson_date . " " . $a->rings_time;
                $aCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $aDT);
                $bDT = $b->lesson_date . " " . $b->rings_time;
                $bCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $bDT);

                if ($aCarbon->eq($bCarbon)) {
                    return 0;
                } else {
                    return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                }
            });
        }




        $result['byGrade'] = $byGrade;
        $result['byGroup'] = array_values($byGroup);
        $result['byTeacherFio'] = array_values($byTeacherFio);

        return $result;
    }
}
