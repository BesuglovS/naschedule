<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ $title }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        td { text-align: center; border: 1px solid black; }
    </style>
</head>
<body>
    <h2 style="text-align: center">{{$title}}</h2>

    <table style="border: 1px solid black; width: 100%; border-collapse: collapse;">
        <tr>
            <td></td>
            @foreach ($schedule as $groupSchedule)
                <td>{{$groupSchedule["groupName"]}}</td>
            @endforeach
        </tr>

        @foreach ($scheduleRings as $scheduleRing)
            <tr>
                <td style="font-size: {{$mainFontSize}}">
                    {{$scheduleRing}}
                </td>
                @foreach ($schedule as $groupSchedule)
                    <td style="font-size: {{$mainFontSize}}">
                        @if(array_key_exists($scheduleRing, $groupSchedule["lessons"][$dow]))
                           @foreach(collect($groupSchedule["lessons"][$dow][$scheduleRing])->sort(
                           function($a, $b) { return strcmp($a["lessons"][0]->groupName, $b["lessons"][0]->groupName); }
                           ) as $tfdLessonsData)
                                <strong>
                                    {{$tfdLessonsData["lessons"][0]->discName}}
                                    @if($tfdLessonsData["lessons"][0]->groupName !== $groupSchedule["groupName"])
                                        {{$tfdLessonsData["lessons"][0]->groupName}}
                                    @endif
                                </strong> <br />
                                {{$tfdLessonsData["lessons"][0]->teacherFIO}} <br />
                                {{$tfdLessonsData["lessons"][0]->auditoriumName}} <br />
                           @endforeach
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</body>
</html>
