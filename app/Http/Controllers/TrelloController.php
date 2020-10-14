<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600);

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Faculty;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use Dompdf\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class TrelloController extends Controller
{
    public static $trelloListIds = array(
        7 => array(
            '6 Г' => '5f815f09c9776c1609631271',
            '7 Б' => '5f815f69ba63598a87c2e007',
            '8 В' => '5f815fecaec1d9105c601b1f',
            '9 А' => '5f86d96e7af4386409d86bcc', '9 Г' => '5f8437a91ddfe5319b798912',
            '10 В' => '5f843b42cceac925a103b922',
        ),
    );

    public static $boardIds = array(
        1 => '5e84908288998f4bc0162576',
        2 => '5e8490615ce1dc1f62755847',
        3 => '5e8490a4f7623a3391f25573',
        4 => '5e8490b46e12625566881223',
        5 => '5e84909554059625212bd682',
        6 => '5e84913fea0a03324a67d330',
        7 => '5e849183f40cf6677629c477',
        8 => '5e84919549e82b4b15d0f84e',
        9 => '5e8491a5d05b0136de001dee',
        10 => '5e8491b0d6b2a584af21fbea',
        11 => '5e8491bfd99d19471c0d2388'
    );

    public function index() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();

        $weeks = array();
        for($w = 1; $w <= $weekCount; $w++) {
            $start = $css->clone();
            $end = $start->clone()->addDays(6);
            $weeks[$w] = $start->format("d.m") . " - " . $end->format('d.m');

            $css = $css->addWeek();
        }

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);

        return view('trello.index', compact('faculties', 'weekCount', 'weeks', 'currentWeek'));
    }

    public function upload(Request $request) {
        $input = $request->all();

        $facultyId = $input["facultyId"];
        $week = $input["week"];
        $dows = explode('|', $input['dows']);
        sort($dows);

        $trelloListIds = TrelloController::$trelloListIds[$week];

        $calendarIds = Calendar::IdsFromDowsAndWeek($dows, $week);

        $facultyGroupIds = DB::table('faculty_student_group')
            ->where('faculty_id', '=', $facultyId)
            ->select('student_group_id')
            ->get()
            ->map(function($item) { return $item->student_group_id;})
            ->toArray();

        $result = array();

        foreach ($facultyGroupIds as $facultyGroupId) {
            $groupName = StudentGroup::find($facultyGroupId)->name;
            if (!array_key_exists($groupName, $trelloListIds)) {
                continue;
            }
            $trelloListId = $trelloListIds[$groupName];
            $result[$trelloListId] = array();

            $studentIds = DB::table('student_student_group')
                ->where('student_group_id', '=', $facultyGroupId)
                ->select('student_id')
                ->get()
                ->map(function($item) { return $item->student_id;});

            $groupExtendedIds = DB::table('student_student_group')
                ->whereIn('student_id', $studentIds)
                ->select('student_group_id')
                ->get()
                ->map(function($item) { return $item->student_group_id;})
                ->unique();

            $weekFacultyLessons = DB::table('lessons')
                ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                ->where('lessons.state', '=', 1)
                ->wherein('student_groups.id', $groupExtendedIds)
                ->whereIn('lessons.calendar_id', $calendarIds)
                ->select('lessons.id as lessonsId',
                    'rings.time as ringsTime', 'disciplines.name as disciplinesName',
                    'student_groups.name as studentGroupsName', 'teachers.fio as teachersFio',
                    'calendars.date as calendarsDate'
                )
                ->get();

            foreach ($weekFacultyLessons as $key => $lesson) {
                $lessonItem = array();

                $fioSplit = explode(' ', $lesson->teachersFio);
                $teacherFio = $fioSplit[0] . " " . mb_substr($fioSplit[1], 0, 1) . "." . mb_substr($fioSplit[2], 0, 1) . ".";

                $carbonDate = Carbon::createFromFormat('Y-m-d', $lesson->calendarsDate);
                $dowRu = array( 1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб", 7 => "Вс");
                $dow = $carbonDate->dayOfWeekIso;
                $carbonDateDM = $carbonDate->format("d.m");


                $lessonItem['name'] = $carbonDateDM . " " . $dowRu[$dow] . " " .
                    mb_substr($lesson->ringsTime, 0, 5) . " - " . $lesson->disciplinesName .
                    " (" .$lesson->studentGroupsName . ") - " . $teacherFio;
                $h = intval(mb_substr($lesson->ringsTime, 0, 2));
                $utcH = $h - 4;
                if ($utcH < 10) $utcH = "0" . $utcH;



                $lessonItem['due'] = $lesson->calendarsDate . "T" . $utcH . mb_substr($lesson->ringsTime, 2, 3) . ":00.000Z";

                $result[$trelloListId][] = $lessonItem;
            }
        }

        foreach ($result as $listId => $lessons) {
            usort($lessons, function ($a, $b) {
                $ad = new DateTime($a['due']);
                $bd = new DateTime($b['due']);

                if ($ad == $bd) {
                    return 0;
                }

                return $ad < $bd ? -1 : 1;
            });

            $result[$listId] = $lessons;
        }

        //return $result;

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        foreach ($result as $listId => $lessons) {
            foreach ($lessons as $lesson) {
                $res = $client->post('cards', [
                    'query' => [
                        'idList' => $listId, // 5e8494347ea6d63682b9856f
                        'name' => $lesson['name'], // 06.04 Пн 08:30 - Математика (1 А) - Манурина В.А.
                        'due' => $lesson['due'], // 2020-04-06T04:30:00.000Z
                    ]
                ]);
            }
        }
    }

    public function brb()
    {
//        $user = new User();
//        $user->password = Hash::make('thebest');
//        $user->email = 'Lyudmilaalex.ki@gmail.com';
//        $user->name = 'Кузнецова Людмила';
//        $user->save();
//        return "OK";

//        $file = fopen(storage_path("data.txt"), "r");
//        while(!feof($file)) {
//            $line = fgets($file);
//            $explode = explode('@', $line);
//            if (count($explode)== 2) {
//                $newTeacher = new Teacher();
//                $newTeacher->fio = $explode[0];
//                $newTeacher->phone = $explode[1];
//                $newTeacher->save();
//            }
//        }
//        fclose($file);
//
//        return "OK";
    }

    public function checkIndex() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();

        $weeks = array (33 => "06.04 - 12.04", 34 => "13.04 - 19.04", 35 => "20.04 - 26.04",
            36 => "27.04 - 30.04", 37 => "06.05 - 08.05", 38 => "12.05 - 17.05",
            39 => "18.05 - 24.05", 40 => "25.05 - 31.05");

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);

        return view('trello.check', compact('faculties', 'weekCount', 'weeks', 'currentWeek'));
    }

    public function checkAction(Request $request) {
        $input = $request->all();

        $facultyId = $input["facultyId"];
        $week = $input["week"];
        $dows = explode('|', $input['dows']);
        sort($dows);


        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $calendarIds = Calendar::IdsFromWeek($week);

        $result = array();

        $trelloWeekListIds = TrelloController::$trelloListIds[$week];

        foreach ($trelloWeekListIds as $groupName => $listId) {
            $grade = explode(' ', $groupName)[0];
            if ($grade !== $facultyId) {
                continue;
            }
            $facultyGroupId = StudentGroup::IdFromName($groupName);

            $studentIds = DB::table('student_student_group')
                ->where('student_group_id', '=', $facultyGroupId)
                ->select('student_id')
                ->get()
                ->map(function($item) { return $item->student_id;});

            $groupExtendedIds = DB::table('student_student_group')
                ->whereIn('student_id', $studentIds)
                ->select('student_group_id')
                ->get()
                ->map(function($item) { return $item->student_group_id;})
                ->unique();

            $groupNames = DB::table('student_groups')
                ->whereIn('id', $groupExtendedIds)
                ->get()
                ->pluck('name');

            $weekGroupLessons = DB::table('lessons')
                ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                ->where('lessons.state', '=', 1)
                ->wherein('student_groups.id', $groupExtendedIds)
                ->whereIn('lessons.calendar_id', $calendarIds)
                ->select('lessons.id as lessonsId',
                    'rings.time as ringsTime', 'disciplines.name as disciplinesName',
                    'student_groups.name as studentGroupsName', 'teachers.fio as teachersFio',
                    'calendars.date as calendarsDate'
                )
                ->get();
            $lessonList = array();

            foreach ($weekGroupLessons as $key => $lesson) {
                $lessonItem = array();

                $fioSplit = explode(' ', $lesson->teachersFio);
                $teacherFio = $fioSplit[0] . " " . mb_substr($fioSplit[1], 0, 1) . "." . mb_substr($fioSplit[2], 0, 1) . ".";

                $carbonDate = Carbon::createFromFormat('Y-m-d', $lesson->calendarsDate);
                $dowRu = array( 1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб", 7 => "Вс");
                $dow = $carbonDate->dayOfWeekIso;
                $carbonDateDM = $carbonDate->format("d.m");


                $lessonItem['name'] = $carbonDateDM . " " . $dowRu[$dow] . " " .
                    mb_substr($lesson->ringsTime, 0, 5) . " - " . $lesson->disciplinesName .
                    " (" .$lesson->studentGroupsName . ") - " . $teacherFio;
                $h = intval(mb_substr($lesson->ringsTime, 0, 2));
                $utcH = $h - 4;
                if ($utcH < 10) $utcH = "0" . $utcH;



                $lessonItem['due'] = $lesson->calendarsDate . "T" . $utcH . mb_substr($lesson->ringsTime, 2, 3) . ":00.000Z";

                $lessonList[] = $lessonItem;
            }


            $res = $client->get('lists/' . $listId .'/cards');
            $data = json_decode($res->getBody());

            //dd($lessonList, $data);

            foreach ($lessonList as $lesson) {
                $found = false;
                $sameName = null;
                foreach ($data as $dataLesson) {
                    if ($lesson['name'] === $dataLesson->name && $lesson['due'] === $dataLesson->due) {
                        $found = true;
                        break;
                    }

                    if ($lesson['name'] === $dataLesson->name) {
                        if (is_null($sameName)) $sameName = array();
                        $sameName[] = array(
                            'wiki' => $lesson,
                            'trello' => $dataLesson
                        );
                    }
                }

                if (!$found && is_null($sameName)) {
                    $item = array();
                    $item["name"] = $lesson['name'];
                    $item["due"] = $lesson['due'];
                    $item["description"] = "В Trello нет карточки урока из расписания";
                    $item["url"] = "";
                    $result[] = $item;
                }

                if (!$found && !is_null($sameName)) {
                    $trelloLessonsDue = array_column(array_column($sameName, 'trello'), 'due');
                    $trelloLessonsDueFormat = array_map(function ($dt) {
                        $carbonDate = Carbon::createFromTimestamp(strtotime($dt));
                        return $carbonDate->format('d.m.Y H:i');
                    }, $trelloLessonsDue);

                    $item = array();
                    $item["name"] = $lesson['name'];
                    $item["due"] = $lesson['due'];
                    $item["description"] = "В Trello в карточке другое время: " . implode(' / ', $trelloLessonsDueFormat);
                    $item["url"] = "";
                    $result[] = $item;
                }
            }

            foreach ($data as $dataLesson) {
                $found = false;
                $sameName = null;
                foreach ($lessonList as $lesson) {
                    if ($dataLesson->name === $lesson['name'] && $dataLesson->due === $lesson['due']) {
                        $found = true;
                        break;
                    }

                    if ($dataLesson->name === $lesson['name']) {
                        if (is_null($sameName)) $sameName = array();
                        $sameName[] = array(
                            'trello' => $dataLesson,
                            'wiki' => $lesson
                        );
                    }
                }

                if (!$found && is_null($sameName)) {
                    $item = array();
                    $item["name"] = $dataLesson->name;
                    $item["due"] = $dataLesson->due;
                    $item["description"] = "В Trello есть карточка не соответствующая расписанию";
                    $item["url"] = $dataLesson->url;
                    $result[] = $item;
                }

                if (!$found && !is_null($sameName)) {
                    $wikiLessonsDue = array_column(array_column($sameName, 'wiki'), 'due');
                    $wikiLessonsDueFormat = array_map(function ($dt) {
                        $carbonDate = Carbon::createFromTimestamp(strtotime($dt));
                        return $carbonDate->format('d.m.Y H:i');
                    }, $wikiLessonsDue);
                    $trelloLessonsDue = array_column(array_column($sameName, 'trello'), 'due');
                    $trelloLessonsDueFormat = array_map(function ($dt) {
                        $carbonDate = Carbon::createFromTimestamp(strtotime($dt));
                        return $carbonDate->format('d.m.Y H:i');
                    }, $trelloLessonsDue);
                    $timeString = '';
                    for ($i = 0; $i < count($wikiLessonsDueFormat); $i++) {
                        $timeString .= $wikiLessonsDueFormat[$i] . " (расписание) - " . $trelloLessonsDueFormat[$i];
                        if ($i !== count($wikiLessonsDueFormat) - 1) {
                            $timeString .= ' / ';
                        }
                    }
                    $item = array();
                    $item["name"] = $dataLesson->name;
                    $item["due"] = $dataLesson->due;
                    $item["description"] = "В расписании время отличается от Trello: " . $timeString;
                    $item["url"] = $dataLesson->url;
                    $result[] = $item;
                }
            }

            foreach ($data as $cardData) {
                $cardDate = ""; $carbonDate = ""; $dow = 8;
                if ($cardData->due !== null) {
                    $cardDate = mb_substr($cardData->due, 0, 10) . " " . mb_substr($cardData->due, 11, 8);
                    $carbonDate = Carbon::createFromFormat('Y-m-d H:i:s', $cardDate)->addMinutes(240);
                    $dowRu = array(1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб", 7 => "Вс");
                    $dow = $carbonDate->dayOfWeekIso;
                }

                if (in_array($dow, $dows) || $dow == 8) {
                    if ($cardData->due !== null) {
                        $descriptionFillDeadlineTime = $carbonDate->copy()->subDays(3);
                        $descriptionFillDeadlineTime->setTime(11, 0, 0);
                    }
                    if ($cardData->desc == "" && (true || (($dow == 8) || (Carbon::now()->gt($descriptionFillDeadlineTime))))) {
                        $item = array();
                        $item["name"] = $cardData->name;
                        $item["description"] = "Описание пустое";
                        $item["url"] = $cardData->url;
                        $result[] = $item;
                    }

                    if (($dow !== 8) && (Carbon::now()->gt($carbonDate->copy()->addMinutes(40)))) {
                        if ($cardData->dueComplete == false) {
                            $item = array();
                            $item["name"] = $cardData->name;
                            $item["description"] = "Нет отметки о проведении урока";
                            $item["url"] = $cardData->url;
                            $result[] = $item;
                        }
                    }

                    if (($dow !== 8) && ((Carbon::now()->lt($carbonDate->copy())))) {
                        if ($cardData->dueComplete == true) {
                            $item = array();
                            $item["name"] = $cardData->name;
                            $item["description"] = "Отметка о выполнении проставлена до начала урока";
                            $item["url"] = $cardData->url;
                            $result[] = $item;
                        }
                    }

                    $rightIndex = mb_strrpos($cardData->name, ')');
                    $leftIndex = mb_strrpos($cardData->name, '(');
                    $groupName = mb_substr($cardData->name, $leftIndex + 1, $rightIndex - $leftIndex - 1);

                    if (!$groupNames->contains($groupName)) {
                        $item = array();
                        $item["name"] = $cardData->name;
                        $item["description"] = "Группа в карточке (" . $groupName . ") не соответствует группе списка "
                            . " (" . implode(", ", $groupNames->toArray()) . ").";
                        $item["url"] = $cardData->url;
                        $result[] = $item;
                    }
                }
            }
        }

        return $result;
    }

    public function trelloDayIndex() {
        $dates = array(
            array('date' => '06.04.2020', 'week' => 33),
            array('date' => '07.04.2020', 'week' => 33),
            array('date' => '08.04.2020', 'week' => 33),
            array('date' => '09.04.2020', 'week' => 33),
            array('date' => '10.04.2020', 'week' => 33),
            array('date' => '11.04.2020', 'week' => 33),
            array('date' => '13.04.2020', 'week' => 34),
            array('date' => '14.04.2020', 'week' => 34),
            array('date' => '15.04.2020', 'week' => 34),
            array('date' => '16.04.2020', 'week' => 34),
            array('date' => '17.04.2020', 'week' => 34),
            array('date' => '18.04.2020', 'week' => 34),
            array('date' => '20.04.2020', 'week' => 35),
            array('date' => '21.04.2020', 'week' => 35),
            array('date' => '22.04.2020', 'week' => 35),
            array('date' => '23.04.2020', 'week' => 35),
            array('date' => '24.04.2020', 'week' => 35),
            array('date' => '25.04.2020', 'week' => 35),
            array('date' => '27.04.2020', 'week' => 36),
            array('date' => '28.04.2020', 'week' => 36),
            array('date' => '29.04.2020', 'week' => 36),
            array('date' => '30.04.2020', 'week' => 36),
            array('date' => '06.05.2020', 'week' => 37),
            array('date' => '07.05.2020', 'week' => 37),
            array('date' => '08.05.2020', 'week' => 37),
            array('date' => '12.05.2020', 'week' => 38),
            array('date' => '13.05.2020', 'week' => 38),
            array('date' => '14.05.2020', 'week' => 38),
            array('date' => '15.05.2020', 'week' => 38),
            array('date' => '16.05.2020', 'week' => 38),
            array('date' => '18.05.2020', 'week' => 39),
            array('date' => '19.05.2020', 'week' => 39),
            array('date' => '20.05.2020', 'week' => 39),
            array('date' => '21.05.2020', 'week' => 39),
            array('date' => '22.05.2020', 'week' => 39),
            array('date' => '23.05.2020', 'week' => 39),
            array('date' => '25.05.2020', 'week' => 40),
            array('date' => '26.05.2020', 'week' => 40),
            array('date' => '27.05.2020', 'week' => 40),
            array('date' => '28.05.2020', 'week' => 40),
            array('date' => '29.05.2020', 'week' => 40),
            array('date' => '30.05.2020', 'week' => 40),
        );
        $groups = StudentGroup::FacultiesGroups();

        return view('trello.dayIndex', compact('dates', 'groups'));
    }

    public function  trelloDayLoadGroup(Request $request) {
        $input = $request->all();
        $date = $input['date']; //13.04.2020
        $mysqlDate = mb_substr($date, 6, 4) . '-' . mb_substr($date, 3, 2) . '-' . mb_substr($date, 0, 2);
        $groupId = $input['groupId'];
        $group = StudentGroup::find($groupId);

        $dateWeek = array(
            '06.04.2020' => 33, '07.04.2020' => 33, '08.04.2020' => 33, '09.04.2020' => 33, '10.04.2020' => 33, '11.04.2020' => 33,
            '13.04.2020' => 34, '14.04.2020' => 34, '15.04.2020' => 34, '16.04.2020' => 34, '17.04.2020' => 34, '18.04.2020' => 34,
            '20.04.2020' => 35, '21.04.2020' => 35, '22.04.2020' => 35, '23.04.2020' => 35, '24.04.2020' => 35, '25.04.2020' => 35,
            '27.04.2020' => 36, '28.04.2020' => 36, '29.04.2020' => 36, '30.04.2020' => 36,
            '06.05.2020' => 37, '07.05.2020' => 37, '08.05.2020' => 37,
            '12.05.2020' => 38, '13.05.2020' => 38, '14.05.2020' => 38, '15.05.2020' => 38, '16.05.2020' => 38,
            '18.05.2020' => 39, '19.05.2020' => 39, '20.05.2020' => 39, '21.05.2020' => 39, '22.05.2020' => 39, '23.05.2020' => 39,
            '25.05.2020' => 40, '26.05.2020' => 40, '27.05.2020' => 40, '28.05.2020' => 40, '29.05.2020' => 40, '30.05.2020' => 40
        );
        $week = $dateWeek[$date];

        $listId = TrelloController::$trelloListIds[$week][$group->name];

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $res = $client->get('lists/' . $listId .'/cards');
        $data = json_decode($res->getBody());

        $data = array_filter($data, function($lesson) use ($mysqlDate) {
            return mb_substr($lesson->due, 0, 10) == $mysqlDate;
        });

        foreach ($data as $lesson) {
            $res = $client->get('cards/' . $lesson->id . '/actions');
            $lessonData = json_decode($res->getBody());

            $lessonData = array_filter($lessonData, function($action)  {
                return $action->type == "commentCard";
            });

            $lesson->comments = $lessonData;
        }

        return $data;
    }

    public function trelloTeacherIndex() {
        $dates = array(
            '06.04.2020', '07.04.2020', '08.04.2020', '09.04.2020', '10.04.2020', '11.04.2020',
            '13.04.2020', '14.04.2020', '15.04.2020', '16.04.2020', '17.04.2020', '18.04.2020',
            '20.04.2020', '21.04.2020', '22.04.2020', '23.04.2020', '24.04.2020', '25.04.2020',
            '27.04.2020', '28.04.2020', '29.04.2020', '30.04.2020',
            '06.05.2020', '07.05.2020', '08.05.2020',
            '12.05.2020', '13.05.2020', '14.05.2020', '15.05.2020', '16.05.2020',
            '18.05.2020', '19.05.2020', '20.05.2020', '21.05.2020', '22.05.2020', '23.05.2020',
            '25.05.2020', '26.05.2020', '27.05.2020', '28.05.2020', '29.05.2020', '30.05.2020'
        );
        $teachers = Teacher::all()->sortBy('fio');

        return view('trello.dayTeacherIndex', compact('dates', 'teachers'));
    }

    public function trelloLoadTeacher(Request $request) {
        $input = $request->all();

        $date = $input['date']; //13.04.2020
        $mysqlDate = mb_substr($date, 6, 4) . '-' . mb_substr($date, 3, 2) . '-' . mb_substr($date, 0, 2);
        $teacherId = $input['teacherId'];
        $teacher = Teacher::find($teacherId);
        $fioSplit = explode(' ', $teacher->fio);
        $teacherFio = $fioSplit[0] . " " . mb_substr($fioSplit[1], 0, 1) . "." . mb_substr($fioSplit[2], 0, 1) . ".";

        $dateWeek = array(
            '06.04.2020' => 33, '07.04.2020' => 33, '08.04.2020' => 33, '09.04.2020' => 33, '10.04.2020' => 33, '11.04.2020' => 33,
            '13.04.2020' => 34, '14.04.2020' => 34, '15.04.2020' => 34, '16.04.2020' => 34, '17.04.2020' => 34, '18.04.2020' => 34,
            '20.04.2020' => 35, '21.04.2020' => 35, '22.04.2020' => 35, '23.04.2020' => 35, '24.04.2020' => 35, '25.04.2020' => 35,
            '27.04.2020' => 36, '28.04.2020' => 36, '29.04.2020' => 36, '30.04.2020' => 36,
            '06.05.2020' => 37, '07.05.2020' => 37, '08.05.2020' => 37,
            '12.05.2020' => 38, '13.05.2020' => 38, '14.05.2020' => 38, '15.05.2020' => 38, '16.05.2020' => 38,
            '18.05.2020' => 39, '19.05.2020' => 39, '20.05.2020' => 39, '21.05.2020' => 39, '22.05.2020' => 39, '23.05.2020' => 39,
            '25.05.2020' => 40, '26.05.2020' => 40, '27.05.2020' => 40, '28.05.2020' => 40, '29.05.2020' => 40, '30.05.2020' => 40
        );
        $week = $dateWeek[$date];

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $result = array();

        $trelloBoardIds = array_values(TrelloController::$boardIds);
        foreach ($trelloBoardIds as $boardId) {
            $res = $client->get('boards/' . $boardId .'/cards');
            $data = json_decode($res->getBody());

            $data = array_filter($data, function($lesson) use ($teacherFio, $mysqlDate) {
                return (mb_substr($lesson->due, 0, 10) == $mysqlDate) && (strpos($lesson->name, $teacherFio) !== false);
            });

            foreach ($data as $lesson) {
                $res = $client->get('cards/' . $lesson->id . '/actions');
                $lessonData = json_decode($res->getBody());

                $lessonData = array_filter($lessonData, function($action)  {
                    return $action->type == "commentCard";
                });

                $lesson->comments = $lessonData;
            }

            $result = array_merge($result, $data);
        }

        return $result;
    }

    public function trelloOnline() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $weekCount = Calendar::WeekCount();

        $weeks = array (33 => "06.04 - 12.04", 34 => "13.04 - 19.04", 35 => "20.04 - 26.04",
            36 => "27.04 - 30.04", 37 => "06.05 - 08.05", 38 => "12.05 - 17.05",
            39 => "18.05 - 24.05", 40 => "25.05 - 31.05");

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);
        $faculties = Faculty::all()->sortBy('sorting_order');

        return view('trello.online', compact('weekCount', 'weeks', 'currentWeek', 'faculties'));
    }

    public function trelloOnlineAction(Request $request) {
        $input = $request->all();

        $week = $input["week"];
        $facultyId = $input["facultyId"];
        $weekDates = Calendar::CalendarsFromWeek($week)->pluck('date')->toArray();

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $lessonList = array();
        $result = array();

        if ($facultyId == 0 || !array_key_exists($facultyId, TrelloController::$boardIds)) {
            $trelloBoardIds = array_values(TrelloController::$boardIds);
        } else {
            $trelloBoardIds = array(TrelloController::$boardIds[$facultyId]);
        }

        foreach ($trelloBoardIds as $boardId) {
            $res = $client->get('boards/' . $boardId .'/cards');
            $data = json_decode($res->getBody());

            $data = array_filter($data, function($lesson) use ($weekDates) {
                return in_array(mb_substr($lesson->due, 0, 10), $weekDates);
            });

            $lessonList = array_merge($lessonList, $data);
        }

        $byGrade = array();
        $byGroup = array();
        $byTeacherFio = array();

        foreach ($lessonList as $lesson) {
            $rightIndex = mb_strrpos($lesson->name, ')');
            $leftIndex = mb_strrpos($lesson->name, '(');
            $groupName = mb_substr($lesson->name, $leftIndex + 1, $rightIndex - $leftIndex - 1);
            if (strpos($groupName, ')') !== false) {
                $next_to_last = mb_strrpos($lesson->name, '(',  $leftIndex - mb_strlen($lesson->name) - 1);

                $lesson->groupName = mb_substr($lesson->name, $next_to_last+1, $leftIndex - $next_to_last - 2);
            } else {
                $lesson->groupName = $groupName;
            }

            $groups = StudentGroup::FacultyGroupsFromGroupName($lesson->groupName);

            if (count($groups) == 0) {
                continue;
            }

            if (count($groups) > 1) {
                $res = $client->get('cards/' . $lesson->id . '/list');
                $data = json_decode($res->getBody());
                $listName = $data->name;
                $leftIndex = mb_strpos($listName, '(');
                $lesson->groupName = mb_substr($listName, 0, $leftIndex - 1);
            } else {
                $lesson->groupName = $groups[0]->name;
            }
            $split = explode(' ', $lesson->groupName);
            $lesson->grade = $split[0];
            $lesson->letter = mb_substr($split[1], 0, 1);

            $nameSplit = explode(' - ', $lesson->name);
            $dateSplit = explode(' ', $nameSplit[0]);
            $lesson->date = $dateSplit[0];
            $lesson->dow = $dateSplit[1];
            $lesson->time = $dateSplit[2];
            $leftIndex = mb_strrpos($nameSplit[1], '(');
            $lesson->discName = mb_substr($nameSplit[1], 0, $leftIndex - 1);
            $lesson->teacherFio = $nameSplit[2];

            //dd($lesson);

            if ((strpos(mb_strtolower($lesson->desc), 'онлайн') !== false) ||
                (strpos(mb_strtolower($lesson->desc), 'он лайн') !== false) ||
                (strpos(mb_strtolower($lesson->desc), 'он-лайн') !== false) ||
                (strpos(mb_strtolower($lesson->desc), 'zoom.us') !== false) ||
                (strpos(mb_strtolower($lesson->desc), 'online') !== false)) {
                if (!array_key_exists($lesson->grade, $byGrade)) {
                    $byGrade[$lesson->grade] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGrade[$lesson->grade]['online']++;
                $byGrade[$lesson->grade]['lessons'][] = $lesson;
                $byGrade[$lesson->grade]['onlineLessons'][] = $lesson;

                if (!array_key_exists($lesson->groupName, $byGroup)) {
                    $byGroup[$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGroup[$lesson->groupName]['online']++;
                $byGroup[$lesson->groupName]['lessons'][] = $lesson;
                $byGroup[$lesson->groupName]['onlineLessons'][] = $lesson;

                if (!array_key_exists($lesson->teacherFio, $byTeacherFio)) {
                    $byTeacherFio[$lesson->teacherFio] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'byGroup' => array(), 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->teacherFio]['online']++;
                $byTeacherFio[$lesson->teacherFio]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->teacherFio]['onlineLessons'][] = $lesson;

                if (!array_key_exists($lesson->groupName, $byTeacherFio[$lesson->teacherFio]['byGroup'])) {
                    $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0,
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName]['online']++;
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName]['onlineLessons'][] = $lesson;
            } else {
                $offlineOrEmpty = 'offline';
                if ($lesson->desc === '') { $offlineOrEmpty = 'empty'; }

                if (!array_key_exists($lesson->grade, $byGrade)) {
                    $byGrade[$lesson->grade] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGrade[$lesson->grade][$offlineOrEmpty]++;
                $byGrade[$lesson->grade]['lessons'][] = $lesson;
                $byGrade[$lesson->grade][$offlineOrEmpty . 'Lessons'][] = $lesson;

                if (!array_key_exists($lesson->groupName, $byGroup)) {
                    $byGroup[$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGroup[$lesson->groupName][$offlineOrEmpty]++;
                $byGroup[$lesson->groupName]['lessons'][] = $lesson;
                $byGroup[$lesson->groupName][$offlineOrEmpty . 'Lessons'][] = $lesson;

                if (!array_key_exists($lesson->teacherFio, $byTeacherFio)) {
                    $byTeacherFio[$lesson->teacherFio] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'byGroup' => array(), 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->teacherFio][$offlineOrEmpty]++;
                $byTeacherFio[$lesson->teacherFio]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->teacherFio][$offlineOrEmpty . 'Lessons'][] = $lesson;

                if (!array_key_exists($lesson->groupName, $byTeacherFio[$lesson->teacherFio]['byGroup'])) {
                    $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0,
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName][$offlineOrEmpty]++;
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName][$offlineOrEmpty . 'Lessons'][] = $lesson;
            }
        }

        foreach ($byTeacherFio as $key => $value) {
            $byTeacherFio[$key]['teacherFio'] = $key;
        }
        foreach ($byGroup as $key => $value) {
            $byGroup[$key]['groupName'] = $key;
        }

        $result['byGrade'] = $byGrade;
        $result['byGroup'] = array_values($byGroup);
        $result['byTeacherFio'] = array_values($byTeacherFio);

        return $result;
    }

    public static function GroupTrelloWeekCards($groupId, $week) {
        if (!array_key_exists($week, TrelloController::$trelloListIds)) {
            return array();
        }

        $group = StudentGroup::find($groupId);

        $listId = TrelloController::$trelloListIds[$week][$group->name];

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $res = $client->get('lists/' . $listId .'/cards');
        $data = json_decode($res->getBody());

        return $data;

    }

    public static function GroupTrelloDateCards($groupId, $date) {
        $calendar = DB::table('calendars')
            ->where('date', '=', $date)
            ->first();
        if ($calendar == null) return array();

        $ss = ConfigOption::SemesterStarts();

        $css = Carbon::createFromFormat('Y-m-d', $ss);

        $week = Calendar::WeekFromDate($calendar->date, $css);

        if (!array_key_exists($week, TrelloController::$trelloListIds)) {
            return array();
        }

        $group = StudentGroup::find($groupId);

        $listId = TrelloController::$trelloListIds[$week][$group->name];

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $res = $client->get('lists/' . $listId .'/cards');
        $data = json_decode($res->getBody());

        $data = array_filter($data, function($card) use ($date) {
            return mb_substr($card->due, 0 , 10) === $date;
        });

        return $data;
    }
}
