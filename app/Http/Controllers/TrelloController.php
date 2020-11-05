<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600);

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Discipline;
use App\DomainClasses\Faculty;
use App\DomainClasses\Lesson;
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
use Illuminate\Support\Facades\Hash;

class TrelloController extends Controller
{
    public static $trelloListIds = array(
        1 => array(
            '5 Д' => '5f4fceccea96c8742e6a982a',
            '6 Г' => '5f4e6d41082a154cba2a6d68',
            '8 В' => '5f4e6e30320f9d41cc091fce',
        ),
        2 => array(
            '5 А' => '5f54bee8f648501a602b6756', '5 Б' => '5f54beebcbc8c324fd17709d', '5 В' => '5f54beed2067cc829b8dc882', '5 Г' => '5f54bef088cf8b2f033144cb', '5 Д' => '5f54bef2992f4452f0c555de',
            '6 Б' => '5f57b42b9f9bad4c5b063821', '6 Г' => '5f54bf8a8d152044977245b0',
            '8 В' => '5f54bfeac6bd18284d3c88f8",
',
        ),
        3 => array(
            '5 А' => '5f5c863ae04692770b346c44', '5 Б' => '5f5c863fadb0385bbd0d61af', '5 В' => '5f5c8642d357784f507e4010', '5 Г' => '5f5c864565dad27c60041b84', '5 Д' => '5f5c8648b2495c5fcbae15cf',
            '6 Б' => '5f5c87d587aca316ca356785', '6 Г' => '5f5c87d7b189e85749fdca94',
            '8 В' => '5f5c88a0a1c1af4f5f18ba16',
        ),
        4 => array(
            '5 Д' => '5f67331a863e431b829cdf01',
            '6 Б' => '5f6733a156209f5b4a0efe6f', '6 Г' => '5f6733a453a8ab8a0d3705f3',
            '8 В' => '5f67354d7f86144dfba0f486',
        ),
        5 => array(
            '6 Г' => '5f7322aeead27073e6e3dc47',
            '8 Б' => '5f72c9ef27711f6f0c2717bb', '8 В' => '5f732362f0dfa3606717b930',
        ),
        6 => array(
            '6 Г' => '5f782e6132089152aa865244',
            '7 Б' => '5f7b5e113b82873c8dd21b90',
            '8 В' => '5f782ec7a74ee319e16f57f8', '8 Г' => '5f782ecb5f6c5160f72bee00',
        ),
        7 => array(
            '6 Г' => '5f815f09c9776c1609631271',
            '7 Б' => '5f815f69ba63598a87c2e007', '7 Г' => '5f86f7d0bea8f8238f59e7fd',
            '8 В' => '5f815fecaec1d9105c601b1f',
            '9 А' => '5f86d96e7af4386409d86bcc', '9 Г' => '5f8437a91ddfe5319b798912',
            '10 В' => '5f843b42cceac925a103b922',
        ),
        8 => array(
            '6 Г' => '5f8ad2c44bc50e849cb0aca6',
            '7 Б' => '5f8ad2d3884bcc53da701263', '7 Г' => '5f8ad2d787979a0e2dbf4a0a',
            '8 В' => '5f8ad3067dd22077bf469cb0',
            '9 А' => '5f8ad322452ce006859aaae1',
            '10 В' => '5f8ad334836b01624b591cba',
        ),
        10 => array(
            '1 А' => '5f9eabe3097f7303eda982f6', '1 Б' => '5f9eabe42fa0de0347333bce', '1 В' => '5f9eabf54ec59d63ee835c1c', '1 Г' => '5f9eabf7b276b860d4cff260', '1 Д' => '5f9eabf95235ee62248f6fcb',
            '2 А' => '5f9bf2e8796d5252c04e64c1', '2 Б' => '5f9bf2eb5c195b8d0df8a6ee', '2 В' => '5f9bf2ed085d6a20c591aef5', '2 Г' => '5f9bf2efad36900d18d8c664', '2 Д' => '5f9bf2f144ec3473f0a55db8', '2 Е' => '5f9bf2f34f7b39151b1ad09b',
            '3 А' => '5f9bf3af4cefac39c14d888d', '3 Б' => '5f9bf3b1fcc22c39dc5d1165', '3 В' => '5f9bf3b3bf7ca08d7ebb4e05', '3 Г' => '5f9bf3b5eac98466d6da208e',
            '4 А' => '5f9bf4289acb5905789e419b', '4 Б' => '5f9bf42afbdda1828a78942c', '4 В' => '5f9bf42c5727da2746e68698', '4 Г' => '5f9bf42ec888ee8d0c2f0b49',
            '5 А' => '5f9bf48832576338faf503e4', '5 Б' => '5f9bf48a4a8d3833492baad4', '5 В' => '5f9bf48c83fc557ae2cf6c90', '5 Г' => '5f9bf48e10f16a8235d97777', '5 Д' => '5f9d3f8eeb4b7e312056adcf',
            '6 А' => '5f9bf53bc487400e209247d2', '6 Б' => '5f9bf53e118d3b78d72685eb', '6 В' => '5f9bf54097b9c1344f7ab17b', '6 Г' => '5f9bf5427429295f1acaeaff', '6 Д' => '5f9bf54482e090524b0f3024',
            '7 А' => '5f9bf5a6c224c00575ec4bba', '7 Б' => '5f9bf5a8520a2d621b762f17', '7 В' => '5f9bf5aa941fe03d346daec6', '7 Г' => '5f9bf5ac2464844fc65c5823',
            '8 А' => '5f9bf5f51ceec92594ed344e', '8 Б' => '5f9bf5f69aa656596e65084e', '8 В' => '5f9bf5fa432b236ecca25604', '8 Г' => '5f9bf5fc80a93121a93e2447',
            '9 А' => '5f9bf75b69f34731277d662e', '9 Б' => '5f9bf75d3a86d5050ff977bd', '9 В' => '5f9bf75fd3226a789bcaeeec', '9 Г' => '5f9bf761e03f966dd3abb819',
            '10 А' => '5f9bf7fc6cf35033cca8abd4', '10 Б' => '5f9bf7fe893e2e5fe2fad481', '10 В' => '5f9bf80044761c6eb08ea1f5', '10 Г' => '5f9bf802c40ca338dd59a769',
            '11 А' => '5f9bf9d8d359b9218de73a2e', '11 Б' => '5f9bf9da234bb55025482d28', '11 В' => '5f9bf9dcbb1e1974652a68f3', '11 Г' => '5f9bf9de5ca5ec05a301204a'
        ),
        11 => array(
            '5 А' => '5fa3f01af360e636bf189857', '5 Б' => '5fa3f01f03dab036cba9c768', '5 В' => '5fa3f0238ec10f29598f9407', '5 Г' => '5fa3f02590632659c63b1017', '5 Д' => '5fa3f0295fa39880028eb947',
            '6 А' => '5fa3dfca99e3d52d387e97df', '6 Б' => '5fa3dfcc97ccb68a79b84295', '6 В' => '5fa3dfcf633f9e7c6ae55b0e', '6 Г' => '5fa3dfd3864e7e8b45521113', '6 Д' => '5fa3e01182a3313a8b029195',
            '7 А' => '5fa3e02b4ced45558107c9d2', '7 Б' => '5fa3e032c06f6b2c233b1b62', '7 В' => '5fa3e034ba2d542886d83b80', '7 Г' => '5fa3e0360f86020994be0cc1',
            '8 А' => '5fa3e0635cdbc0483b3338df', '8 Б' => '5fa3e0650d148979e73877b0', '8 В' => '5fa3e06795f27403342b824d', '8 Г' => '5fa3e069be9e4a02791bec01',
            '9 А' => '5fa3e09340874e8350f45b48', '9 Б' => '5fa3e095d91b3c18d902fd08', '9 В' => '5fa3e097dfa5bf7659802151', '9 Г' => '5fa3e099a8d04263c33b1aea',
            '10 А' => '5fa3e0c227f4d40e42ffa4cb', '10 Б' => '5fa3e0c444b7030e3727ec13', '10 В' => '5fa3e0c6438145020401a6f5', '10 Г' => '5fa3e0c8dc46d60758cd297f',
            '11 А' => '5fa3e12faa3d518af9ae8e7a', '11 Б' => '5fa3e131e6f0687e3fa3edfb', '11 В' => '5fa3e1338291c66aea2fe980', '11 Г' => '5fa3e135f5e8db747c8403b4'
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
        $teachers = Teacher::all()->sortBy('fio');
        foreach ($teachers as $teacher) {
            if ($teacher->user_id == null) {
                $pass = "";
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 12; $i++) {
                    $pass .= $characters[rand(0, $charactersLength - 1)];
                }

                $cyr = [
                    'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
                    'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
                    'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
                    'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
                ];
                $lat = [
                    'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
                    'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
                    'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
                    'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
                ];

                $fiowospaces = str_replace(" ", "", $teacher->fio);
                $name_lat = str_replace($cyr, $lat, $fiowospaces);
                $email = $name_lat . "@nayanova.edu";

                $teacher->pass = $pass;
                $teacher->email = $email;

                $user = new User();
                $user->password = Hash::make($pass);
                $user->email = $email;
                $user->name = $teacher->fio;
                $user->save();

                $t = Teacher::find($teacher->id);
                $t->user_id = $user->id;
                $t->save();
            }
        }
        return $teachers;


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

    }

    public function checkIndex() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();

        $weeks = array (1 => "31.08 - 06.09", 2 => "07.09 - 13.09", 3 => "14.09 - 20.09",
            4 => "21.09 - 27.09", 5 => "28.09 - 04.10", 6 => "05.10 - 11.10",
            7 => "12.10 - 18.10", 8 => "19.10 - 25.10", 9 => "26.10 - 01.11",
            10 => "02.11 - 08.11", 11 => "09.11 - 15.11",
            );

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
            array('date' => '05.11.2020', 'week' => 10),
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
            '05.11.2020' => 10
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

        $weeks = array (1 => "31.08 - 06.09", 2 => "07.09 - 13.09", 3 => "14.09 - 20.09",
            4 => "21.09 - 27.09", 5 => "28.09 - 04.10", 6 => "05.10 - 11.10",
            7 => "12.10 - 18.10", 8 => "19.10 - 25.10", 9 => "26.10 - 01.11",
            10 => "02.11 - 08.11", 11 => "09.11 - 15.11",
        );

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
            if (count($nameSplit) < 3) {
                dd($lesson);
            }
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
