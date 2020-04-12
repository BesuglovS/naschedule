<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600);

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Faculty;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrelloController extends Controller
{
    public static $trelloListIds = array(
        33 => array(
            '1 А' => '5e8494347ea6d63682b9856f', '1 Б' => '5e849437caada40c23264103', '1 В' => '5e849439925ec05d62a2c330', '1 Г' => '5e84943b623f0a1d2806f779', '1 Д' => '5e84943cd732e632dbe27d59', '1 Е' => '5e84943e91e3de4f4548aab6',
            '2 А' => '5e84949a22cae66770b84882', '2 Б' => '5e84949b990ff4583cf09f1b', '2 В' => '5e84949d866ade8ec8e9c9f4', '2 Г' => '5e84949e6654da790f2a52a3',
            '3 А' => '5e8494b3ed4ad6328925af0d', '3 Б' => '5e8494b59151aa7fe836a9b0', '3 В' => '5e8494b67b8ef628f93b7167', '3 Г' => '5e8494b9bb5a6e24969f9a55',
            '4 А' => '5e8494ccd284933384bc315c', '4 Б' => '5e8494ce1dd96384e788a753', '4 В' => '5e8494d0eb1e2c67eabee6f1', '4 Г' => '5e8494d23d86b35f9d885d43', '4 Д' => '5e8494d4c9cecf332ebdb838',
            '5 А' => '5e8494e3ba2b2883ffd257bf', '5 Б' => '5e8494e5f912936697a16f22', '5 В' => '5e8494e6772b6187a28b57b1', '5 Г' => '5e8494e9879cc8852dff38a7', '5 Д' => '5e8494ebb797dc409b7ac21d',
            '6 А' => '5e8494fa1dd96384e788ad4f', '6 Б' => '5e8494fcb2ee4e49b7245d45', '6 В' => '5e8494fd0762c92da9c1b86b', '6 Г' => '5e8494ff928e6f0bff2bb6df',
            '7 А' => '5e8495122ea35746c0074048', '7 Б' => '5e849514a0fb6e78c2eb014b', '7 В' => '5e849516a18fa62d452ae8dd', '7 Г' => '5e849519721d7e7856810587',
            '8 А' => '5e84952a3dd6d3412fcee9f6', '8 Б' => '5e84952c187b817893079b1d', '8 В' => '5e84952e44b74c08d635de11', '8 Г' => '5e8495306a953e412a528c82',
            '9 А' => '5e84953ce8dfd80bf51a18b5', '9 Б' => '5e84953ec77fda17a132e02e', '9 В' => '5e84954001a4be355fa8b17e', '9 Г1' => '5e849542e3c7dc5478fbe1d2', '9 Г2' => '5e8495439f25a11fa1c01e33',
            '10 А' => '5e84955902068635cc94b284', '10 Б' => '5e84955cd8759d8d7e84a4e6', '10 В' => '5e84955eca67751f929568df', '10 Г' => '5e849560c44ee10158aa1df2',
            '11 А' => '5e84956db7237430801bf22f', '11 В' => '5e84956f2627c96c955dec3f', '11 Г' => '5e84957244af2075461efb11',
        ),
        34 => array(
            '1 А' => '5e8f36ef54f0f926f86c4863', '1 Б' => '5e8f36f4df660d5400c68f13', '1 В' => '5e8f36fbb9206f3b984e133a', '1 Г' => '5e8f36ffae6fa13d5d19141a', '1 Д' => '5e8f37048dc66d6d7c4ff566', '1 Е' => '5e8f370893f8454b5b9b3719',
            '2 А' => '5e8f372d504b9d60c0a0d2b3', '2 Б' => '5e8f373064d69069255ce252', '2 В' => '5e8f37334eae773c2960c56e', '2 Г' => '5e8f3735c32eaa78312c13de',
            '3 А' => '5e8f374723af660cfab83974', '3 Б' => '5e8f374bbb50c73be02a48a4', '3 В' => '5e8f374d2791777794605e9b', '3 Г' => '5e8f3750358df36fea9a850a',
            '4 А' => '5e8f37609fd4894342bdea82', '4 Б' => '5e8f3762352b1c2aea81173a', '4 В' => '5e8f37640b70ea87a7e96568', '4 Г' => '5e8f376787066f5446037135', '4 Д' => '5e8f3769ada56351c983d466',
            '5 А' => '5e8f3783c9ac020417f1a567', '5 Б' => '5e8f3785ca60538b259fb7e4', '5 В' => '5e8f37888d76c062f7313b76', '5 Г' => '5e8f378b76dd7d414fd1f1f0', '5 Д' => '5e8f378db9d90b40f1e9b4f3',
            '6 А' => '5e889cfeab97d2742aa3f60b', '6 Б' => '5e8f37aa0afd5328b7397045', '6 В' => '5e8f37add2bab04032d1c2d9', '6 Г' => '5e8f37af2c64aa6153f642e8',
            '7 А' => '5e8f37debd62358b704d5e7f', '7 Б' => '5e8f37e0e1adcf61287403e5', '7 В' => '5e8f37e3c525f440b36e961f', '7 Г' => '5e8f37e54c0e3a61c0eafae3',
            '8 А' => '5e8f37f5c5246f624bf21c74', '8 Б' => '5e8f37f7c22f91191e831750', '8 В' => '5e8f37f9b3bd021cb377d287', '8 Г' => '5e8f37fc0149c76907f69776',
            '9 А' => '5e8f38112a9f9b2b3ff08720', '9 Б' => '5e8f38139a6738622ef8a257', '9 В' => '5e8f3815baa85825f3185ab0', '9 Г1' => '5e8f3819cd8ab25c2ada7c77', '9 Г2' => '5e8f381c4eddd8057a9cadb4',
            '10 А' => '5e8f382b3849ee6240085d75', '10 Б' => '5e8f382d3cc75349ce141535', '10 В' => '5e8f3830f5995619933e0ca7', '10 Г' => '5e8f3833fea104131d27c339',
            '11 А' => '5e8f38425aabb64ed2455c35', '11 В' => '5e8f38451a9a266aca652f97', '11 Г' => '5e8f38472810ef33d728301d',
        )
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

        $trelloListIds = array(); // TODO: Fill Ids

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

    }

    public function checkIndex() {
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

        return view('trello.check', compact('faculties', 'weekCount', 'weeks', 'currentWeek'));
    }

    public function checkAction(Request $request) {
        $input = $request->all();

        $facultyId = $input["facultyId"];
        $week = $input["week"];
        $dows = explode('|', $input['dows']);
        sort($dows);

        $trelloListIds = array(
            33 => array(
                1 => array('5e8494347ea6d63682b9856f', '5e849437caada40c23264103', '5e849439925ec05d62a2c330', '5e84943b623f0a1d2806f779', '5e84943cd732e632dbe27d59', '5e84943e91e3de4f4548aab6'),
                2 => array('5e84949a22cae66770b84882', '5e84949b990ff4583cf09f1b', '5e84949d866ade8ec8e9c9f4', '5e84949e6654da790f2a52a3'),
                3 => array('5e8494b3ed4ad6328925af0d', '5e8494b59151aa7fe836a9b0', '5e8494b67b8ef628f93b7167', '5e8494b9bb5a6e24969f9a55'),
                4 => array('5e8494ccd284933384bc315c', '5e8494ce1dd96384e788a753', '5e8494d0eb1e2c67eabee6f1', '5e8494d23d86b35f9d885d43', '5e8494d4c9cecf332ebdb838'),
                5 => array('5e8494e3ba2b2883ffd257bf', '5e8494e5f912936697a16f22', '5e8494e6772b6187a28b57b1', '5e8494e9879cc8852dff38a7', '5e8494ebb797dc409b7ac21d'),
                6 => array('5e8494fa1dd96384e788ad4f', '5e8494fcb2ee4e49b7245d45', '5e8494fd0762c92da9c1b86b', '5e8494ff928e6f0bff2bb6df'),
                7 => array('5e8495122ea35746c0074048', '5e849514a0fb6e78c2eb014b', '5e849516a18fa62d452ae8dd', '5e849519721d7e7856810587'),
                8 => array('5e84952a3dd6d3412fcee9f6', '5e84952c187b817893079b1d', '5e84952e44b74c08d635de11', '5e8495306a953e412a528c82'),
                9 => array('5e84953ce8dfd80bf51a18b5', '5e84953ec77fda17a132e02e', '5e84954001a4be355fa8b17e', '5e849542e3c7dc5478fbe1d2', '5e8495439f25a11fa1c01e33'),
                10 => array('5e84955902068635cc94b284', '5e84955cd8759d8d7e84a4e6',  '5e84955eca67751f929568df', '5e849560c44ee10158aa1df2'),
                11 => array('5e84956db7237430801bf22f', '5e84956f2627c96c955dec3f', '5e84957244af2075461efb11')
            ),
            34 => array(
                1 => array('5e8f36ef54f0f926f86c4863', '5e8f36f4df660d5400c68f13', '5e8f36fbb9206f3b984e133a', '5e8f36ffae6fa13d5d19141a', '5e8f37048dc66d6d7c4ff566', '5e8f370893f8454b5b9b3719'),
                2 => array('5e8f372d504b9d60c0a0d2b3', '5e8f373064d69069255ce252', '5e8f37334eae773c2960c56e', '5e8f3735c32eaa78312c13de'),
                3 => array('5e8f374723af660cfab83974', '5e8f374bbb50c73be02a48a4', '5e8f374d2791777794605e9b', '5e8f3750358df36fea9a850a'),
                4 => array('5e8f37609fd4894342bdea82', '5e8f3762352b1c2aea81173a', '5e8f37640b70ea87a7e96568', '5e8f376787066f5446037135', '5e8f3769ada56351c983d466'),
                5 => array('5e8f3783c9ac020417f1a567', '5e8f3785ca60538b259fb7e4', '5e8f37888d76c062f7313b76', '5e8f378b76dd7d414fd1f1f0', '5e8f378db9d90b40f1e9b4f3'),
                6 => array('5e889cfeab97d2742aa3f60b', '5e8f37aa0afd5328b7397045', '5e8f37add2bab04032d1c2d9', '5e8f37af2c64aa6153f642e8'),
                7 => array('5e8f37debd62358b704d5e7f', '5e8f37e0e1adcf61287403e5', '5e8f37e3c525f440b36e961f', '5e8f37e54c0e3a61c0eafae3'),
                8 => array('5e8f37f5c5246f624bf21c74', '5e8f37f7c22f91191e831750', '5e8f37f9b3bd021cb377d287', '5e8f37fc0149c76907f69776'),
                9 => array('5e8f38112a9f9b2b3ff08720', '5e8f38139a6738622ef8a257', '5e8f3815baa85825f3185ab0', '5e8f3819cd8ab25c2ada7c77', '5e8f381c4eddd8057a9cadb4'),
                10 => array('5e8f382b3849ee6240085d75', '5e8f382d3cc75349ce141535', '5e8f3830f5995619933e0ca7', '5e8f3833fea104131d27c339'),
                11 => array('5e8f38425aabb64ed2455c35', '5e8f38451a9a266aca652f97', '5e8f38472810ef33d728301d')
            )
        );
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

        $trelloWeekListIds = $trelloListIds[$week];

        foreach ($trelloWeekListIds[$facultyId] as $listId) {
            $res = $client->get('lists/' . $listId .'/cards');
            $data = json_decode($res->getBody());

            //dd($data);

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
                    if ($cardData->desc == "" && (($dow == 8) || (Carbon::now()->gt($descriptionFillDeadlineTime)))) {
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
            array('date' => '18.04.2020', 'week' => 34)
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
            '13.04.2020' => 34, '14.04.2020' => 34, '15.04.2020' => 34, '16.04.2020' => 34, '17.04.2020' => 34, '18.04.2020' => 34
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
}
