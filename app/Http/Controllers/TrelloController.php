<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600);

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Faculty;
use App\DomainClasses\StudentGroup;
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

        $trelloListIds = array(
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
        );

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
        $trelloListIds = array(
            //'1 А' => '5e8494347ea6d63682b9856f', '1 Б' => '5e849437caada40c23264103', '1 В' => '5e849439925ec05d62a2c330', '1 Г' => '5e84943b623f0a1d2806f779', '1 Д' => '5e84943cd732e632dbe27d59', '1 Е' => '5e84943e91e3de4f4548aab6',
            //'2 А' => '5e84949a22cae66770b84882', '2 Б' => '5e84949b990ff4583cf09f1b', '2 В' => '5e84949d866ade8ec8e9c9f4', '2 Г' => '5e84949e6654da790f2a52a3',
            //'3 А' => '5e8494b3ed4ad6328925af0d', '3 Б' => '5e8494b59151aa7fe836a9b0', '3 В' => '5e8494b67b8ef628f93b7167', '3 Г' => '5e8494b9bb5a6e24969f9a55',
            //'4 А' => '5e8494ccd284933384bc315c', '4 Б' => '5e8494ce1dd96384e788a753', '4 В' => '5e8494d0eb1e2c67eabee6f1', '4 Г' => '5e8494d23d86b35f9d885d43', '4 Д' => '5e8494d4c9cecf332ebdb838',
            //'5 А' => '5e8494e3ba2b2883ffd257bf', '5 Б' => '5e8494e5f912936697a16f22', '5 В' => '5e8494e6772b6187a28b57b1', '5 Г' => '5e8494e9879cc8852dff38a7', '5 Д' => '5e8494ebb797dc409b7ac21d',
            //'6 А' => '5e8494fa1dd96384e788ad4f', '6 Б' => '5e8494fcb2ee4e49b7245d45', '6 В' => '5e8494fd0762c92da9c1b86b', '6 Г' => '5e8494ff928e6f0bff2bb6df',
            //'7 А' => '5e8495122ea35746c0074048', '7 Б' => '5e849514a0fb6e78c2eb014b', '7 В' => '5e849516a18fa62d452ae8dd', '7 Г' => '5e849519721d7e7856810587',
            //'8 А' => '5e84952a3dd6d3412fcee9f6', '8 Б' => '5e84952c187b817893079b1d', '8 В' => '5e84952e44b74c08d635de11', '8 Г' => '5e8495306a953e412a528c82',
            //'9 А' => '5e84953ce8dfd80bf51a18b5', '9 Б' => '5e84953ec77fda17a132e02e', '9 В' => '5e84954001a4be355fa8b17e', '9 Г1' => '5e849542e3c7dc5478fbe1d2', '9 Г2' => '5e8495439f25a11fa1c01e33',
            //'10 А' => '5e84955902068635cc94b284', '10 Б' => '5e84955cd8759d8d7e84a4e6', '10 В' => '5e84955eca67751f929568df', '10 Г' => '5e849560c44ee10158aa1df2',
            //'11 А' => '5e84956db7237430801bf22f', '11 В' => '5e84956f2627c96c955dec3f', '11 Г' => '5e84957244af2075461efb11',
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

        foreach ($trelloListIds as $groupName => $listId) {
            $res = $client->get('lists/' . $listId .'/cards');
            $data = json_decode($res->getBody());

            foreach ($data as $cardData) {
                $cardDate = mb_substr($cardData->due, 0, 10);
                $carbonDate = Carbon::createFromFormat('Y-m-d', $cardDate);
                $dowRu = array( 1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб", 7 => "Вс");
                $dow = $carbonDate->dayOfWeekIso;
                $carbonDateDM = $carbonDate->format("d.m");

                $newName = $carbonDateDM . " " .  $dowRu[$dow] . " " . $cardData->name; // 06.04 Пн 08:30 - Математика (1 А) - Манурина В.А.

                $res = $client->put('cards/' . $cardData->id , [
                    'query' => [
                        'name' => $newName,
                    ]
                ]);
            }
        }
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

        foreach ($trelloListIds[$facultyId] as $listId) {
            $res = $client->get('lists/' . $listId .'/cards');
            $data = json_decode($res->getBody());

            //dd($data);

            foreach ($data as $cardData) {
                $cardDate = mb_substr($cardData->due, 0, 10);
                $carbonDate = Carbon::createFromFormat('Y-m-d', $cardDate);
                $dowRu = array( 1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб", 7 => "Вс");
                $dow = $carbonDate->dayOfWeekIso;

                if (in_array($dow, $dows)) {
                    if ($cardData->desc == "" ) {
                        $item = array();
                        $item["name"] = $cardData->name;
                        $item["description"] = "Описание пустое";
                        $result[] = $item;
                    }
                }
            }

            return $result;
        }
    }
}
