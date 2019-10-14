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
        @for ($dow = 1; $dow <= 6; $dow++)
            <td>{{$dowRu[$dow]}}</td>
        @endfor
    </tr>
    @foreach ($scheduleRings as $scheduleRing)
        <tr>
            <td style="font-size: {{$mainFontSize}}">
                {{$scheduleRing}}
            </td>
            @foreach ($schedule as $dow => $dowSchedule)
                <td style="font-size: {{$mainFontSize}}">
                    @if(array_key_exists($scheduleRing, $dowSchedule))
                        @foreach(collect($dowSchedule[$scheduleRing])->sort(
                        function($a, $b) { return strcmp($a["lessons"][0]->groupName, $b["lessons"][0]->groupName); }
                        ) as $tfdLessonsData)
                            <strong>
                                {{$tfdLessonsData["lessons"][0]->discName}}
                                ({{$tfdLessonsData["lessons"][0]->groupName}})
                            </strong> <br />
                            {{$tfdLessonsData["lessons"][0]->auditoriumName}} <br />
                            @if(!$loop->last)
                                <hr />
                            @endif
                        @endforeach
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach
</table>
<div style="text-align: right; font-size: 0.5em;">
    Экспорт произведён: {{$timestamp}}
</div>
</body>
</html>
