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
        ),
        35 => array(
            '1 А' => '5e9802e0436faf7e74fd6a95', '1 Б' => '5e9802e3625bd96aa58532ae', '1 В' => '5e9802e6c0c6b74fb711581d', '1 Г' => '5e9802e8bd11646352d9fb8e', '1 Д' => '5e9802eb3884b10beac816ae', '1 Е' => '5e9802edd3ff283e763fb9ba',
            '2 А' => '5e98031ca2107f589b7e3276', '2 Б' => '5e9803206129ea107f572487', '2 В' => '5e98032701d2b17d8d71d6f2', '2 Г' => '5e98032a8a81b9590fea01a8',
            '3 А' => '5e980343b850ef7eb955e08e', '3 Б' => '5e980347213dc776b0ed7c1a', '3 В' => '5e98034a57d65c6db5677b6d', '3 Г' => '5e98034d25767d2cd4a347e4',
            '4 А' => '5e980364a578ef24f37d31fe', '4 Б' => '5e980366bdfa68813a64744c', '4 В' => '5e980369f3f3be211357645e', '4 Г' => '5e98036bbc1bd303f8f64f96', '4 Д' => '5e98036d315dbd2573dd67f0',
            '5 А' => '5e980406a2d7217dbd7a952a', '5 Б' => '5e980409043ce1249c4f21aa', '5 В' => '5e98040b70f28010a9b7038e', '5 Г' => '5e98040ddf38fd8e24268d8f', '5 Д' => '5e98040fe411115b9f7ed10a',
            '6 А' => '5e9804acdb80d63ed296b31c', '6 Б' => '5e9804ae49ae261f71819bfc', '6 В' => '5e9804b0ca5fc24a712ca627', '6 Г' => '5e9804b2126b2c1f99447583',
            '7 А' => '5e9804d83dd9587de63ca426', '7 Б' => '5e9804daf5aa0e041995c27e', '7 В' => '5e9804de378a074cc3a31069', '7 Г' => '5e9804e054fad840ddf7fb34',
            '8 А' => '5e9805372918573b6ff2223e', '8 Б' => '5e98053909c76b413eb2b406', '8 В' => '5e98053b005d862210f20d21', '8 Г' => '5e98053effe84125e0464b12',
            '9 А' => '5e9805674e8467594cdee082', '9 Б' => '5e9805696573d1492d5383b9', '9 В' => '5e98056c0c7f210ba8193f7d', '9 Г1' => '5e98056e2fc76031229a793b', '9 Г2' => '5e9805707b438d2330b83dde',
            '10 А' => '5e98059fe933da5246f605e8', '10 Б' => '5e9805a19828318ec0812942', '10 В' => '5e9805a30a44395aabe4ff07', '10 Г' => '5e9805a65e35180add006b63',
            '11 А' => '5e98061adf956723e47bb45d', '11 В' => '5e98061cb2f97b6a44bf939c', '11 Г' => '5e98061fd540ce246f517299'
        ),
        36 => array(
            '1 А' => '5ea1a1f20022205a0b0d1ace', '1 Б' => '5ea1a1f6f62b3a5975485754', '1 В' => '5ea1a1f9e4a3507ab65b766d', '1 Г' => '5ea1a1fbc5e45b576dada0cc', '1 Д' => '5ea1a1fd38776432b1a3f794', '1 Е' => '5ea1a2004608ea3c4acc0d02',
            '2 А' => '5ea1a249aa75f96716f72ea5', '2 Б' => '5ea1a24d551d11715053c85d', '2 В' => '5ea1a24fa2180881024d560f', '2 Г' => '5ea1a251452e6542ac411285',
            '3 А' => '5ea1a3a9ea47830994092abf', '3 Б' => '5ea1a3ad24e6514b521dab2d', '3 В' => '5ea1a3b0fe5620811c31902e', '3 Г' => '5ea1a3b240c3cf1e509f3b90',
            '4 А' => '5ea1a3e7b68ae65a24043e7a', '4 Б' => '5ea1a3e9780c032a402cdf63', '4 В' => '5ea1a3eb0b6de871ec66499b', '4 Г' => '5ea1a3ee50365a31e6a6cc36', '4 Д' => '5ea1a3f021a76c712bea8573',
            '5 А' => '5ea1a42d2b5e1b49dad98446', '5 Б' => '5ea1a430375b683b8391e108', '5 В' => '5ea1a4334355d21d3a8eff4d', '5 Г' => '5ea1a435a67e392b1fac9007', '5 Д' => '5ea1a438cf665088aeb94a75',
            '6 А' => '5ea1a4aca88f640c85662b35', '6 Б' => '5ea1a4ae1950086d08c72491', '6 В' => '5ea1a4b1f2356735e381ec2d', '6 Г' => '5ea1a4b34911d02524505565',
            '7 А' => '5ea1a4e8b4925272e55b6d8d', '7 Б' => '5ea1a4ea854c535a810e864d', '7 В' => '5ea1a4ed4a23618625c1a67e', '7 Г' => '5ea1a4f0bfcbdc395ea0e19a',
            '8 А' => '5ea1a51a62f7f9168d6a7d62', '8 Б' => '5ea1a51e6bec217b85780898', '8 В' => '5ea1a521eddbfa327f5cd3e1', '8 Г' => '5ea1a523868ac7342b8df2cf',
            '9 А' => '5ea1a54dd131971e4f48c6ce', '9 Б' => '5ea1a54f5e00e121728faabb', '9 В' => '5ea1a551da33520c603aa470', '9 Г1' => '5ea1a553711669761fad0430', '9 Г2' => '5ea1a5565de5c0187480204e',
            '10 А' => '5ea1a5e11e9a3971361fea61', '10 Б' => '5ea1a5e34f2a3d76940155ba', '10 В' => '5ea1a5e5d244d8606f2fec94', '10 Г' => '5ea1a5e8b39c614b7e62e213',
            '11 А' => '5ea1a618213f765aff6c602e', '11 В' => '5ea1a61aedfda54f657d25f4', '11 Г' => '5ea1a61c6149fb76d0217b2b'
        )
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

        $trelloBoardIds = array_values(TrelloController::$boardIds);
        //$trelloBoardIds = array($trelloBoardIds[9]);
        foreach ($trelloBoardIds as $boardId) {
            $res = $client->get('boards/' . $boardId .'/cards');
            $data = json_decode($res->getBody());

            $lessonList = array_merge($lessonList, $data);
        }

        return collect($lessonList)->pluck('desc');
    }

    public function checkIndex() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();

        $weeks = array (33 => "06.04 - 12.04", 34 => "13.04 - 19.04", 35 => "20.04 - 26.04", 36 => "27.04 - 30.04");

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
            ),
            35 => array(
                1 => array('5e9802e0436faf7e74fd6a95', '5e9802e3625bd96aa58532ae', '5e9802e6c0c6b74fb711581d', '5e9802e8bd11646352d9fb8e', '5e9802eb3884b10beac816ae', '5e9802edd3ff283e763fb9ba'),
                2 => array('5e98031ca2107f589b7e3276', '5e9803206129ea107f572487', '5e98032701d2b17d8d71d6f2', '5e98032a8a81b9590fea01a8'),
                3 => array('5e980343b850ef7eb955e08e', '5e980347213dc776b0ed7c1a', '5e98034a57d65c6db5677b6d', '5e98034d25767d2cd4a347e4'),
                4 => array('5e980364a578ef24f37d31fe', '5e980366bdfa68813a64744c', '5e980369f3f3be211357645e', '5e98036bbc1bd303f8f64f96', '5e98036d315dbd2573dd67f0'),
                5 => array('5e980406a2d7217dbd7a952a', '5e980409043ce1249c4f21aa', '5e98040b70f28010a9b7038e', '5e98040ddf38fd8e24268d8f', '5e98040fe411115b9f7ed10a'),
                6 => array('5e9804acdb80d63ed296b31c', '5e9804ae49ae261f71819bfc', '5e9804b0ca5fc24a712ca627', '5e9804b2126b2c1f99447583'),
                7 => array('5e9804d83dd9587de63ca426', '5e9804daf5aa0e041995c27e', '5e9804de378a074cc3a31069', '5e9804e054fad840ddf7fb34'),
                8 => array('5e9805372918573b6ff2223e', '5e98053909c76b413eb2b406', '5e98053b005d862210f20d21', '5e98053effe84125e0464b12'),
                9 => array('5e9805674e8467594cdee082', '5e9805696573d1492d5383b9', '5e98056c0c7f210ba8193f7d', '5e98056e2fc76031229a793b', '5e9805707b438d2330b83dde'),
                10 => array('5e98059fe933da5246f605e8', '5e9805a19828318ec0812942', '5e9805a30a44395aabe4ff07', '5e9805a65e35180add006b63'),
                11 => array('5e98061adf956723e47bb45d', '5e98061cb2f97b6a44bf939c', '5e98061fd540ce246f517299')
            ),
            36 => array(
                1 => array('5ea1a1f20022205a0b0d1ace', '5ea1a1f6f62b3a5975485754', '5ea1a1f9e4a3507ab65b766d', '5ea1a1fbc5e45b576dada0cc', '5ea1a1fd38776432b1a3f794', '5ea1a2004608ea3c4acc0d02'),
                2 => array('5ea1a249aa75f96716f72ea5', '5ea1a24d551d11715053c85d', '5ea1a24fa2180881024d560f', '5ea1a251452e6542ac411285'),
                3 => array('5ea1a3a9ea47830994092abf', '5ea1a3ad24e6514b521dab2d', '5ea1a3b0fe5620811c31902e', '5ea1a3b240c3cf1e509f3b90'),
                4 => array('5ea1a3e7b68ae65a24043e7a', '5ea1a3e9780c032a402cdf63', '5ea1a3eb0b6de871ec66499b', '5ea1a3ee50365a31e6a6cc36', '5ea1a3f021a76c712bea8573'),
                5 => array('5ea1a42d2b5e1b49dad98446', '5ea1a430375b683b8391e108', '5ea1a4334355d21d3a8eff4d', '5ea1a435a67e392b1fac9007', '5ea1a438cf665088aeb94a75'),
                6 => array('5ea1a4aca88f640c85662b35', '5ea1a4ae1950086d08c72491', '5ea1a4b1f2356735e381ec2d', '5ea1a4b34911d02524505565'),
                7 => array('5ea1a4e8b4925272e55b6d8d', '5ea1a4ea854c535a810e864d', '5ea1a4ed4a23618625c1a67e', '5ea1a4f0bfcbdc395ea0e19a'),
                8 => array('5ea1a51a62f7f9168d6a7d62', '5ea1a51e6bec217b85780898', '5ea1a521eddbfa327f5cd3e1', '5ea1a523868ac7342b8df2cf'),
                9 => array('5ea1a54dd131971e4f48c6ce', '5ea1a54f5e00e121728faabb', '5ea1a551da33520c603aa470', '5ea1a553711669761fad0430', '5ea1a5565de5c0187480204e'),
                10 => array('5ea1a5e11e9a3971361fea61', '5ea1a5e34f2a3d76940155ba', '5ea1a5e5d244d8606f2fec94', '5ea1a5e8b39c614b7e62e213'),
                11 => array('5ea1a618213f765aff6c602e', '5ea1a61aedfda54f657d25f4', '5ea1a61c6149fb76d0217b2b')
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
            '27.04.2020', '28.04.2020', '29.04.2020', '30.04.2020'
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
            '27.04.2020' => 36, '28.04.2020' => 36, '29.04.2020' => 36, '30.04.2020' => 36
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

        $weeks = array (33 => "06.04 - 12.04", 34 => "13.04 - 19.04", 35 => "20.04 - 26.04", 36 => "27.04 - 30.04");

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
                    $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0);
                }
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName]['online']++;
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
                    $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0);
                }
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName][$offlineOrEmpty]++;
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
}
