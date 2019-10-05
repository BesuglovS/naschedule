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

<table style="margin-top: 2em; border-collapse: collapse; width: 100%;" class="table td-center is-bordered">
    <tr>
        <td>&nbsp;</td>
        @for ($dow = 1; $dow <= 6; $dow++)
            <td style="font-size: {{$mainFontSize}}">
                <strong>{{$dowRu[$dow]}}</strong>
            </td>
        @endfor
    </tr>
    @foreach($scheduleRings as $ring)
    <tr>
        <td style="font-size: {{$mainFontSize}}"><strong>{{$ring}}</strong></td>
        @for ($dow = 1; $dow <= 6; $dow++)
            <td style="font-size: {{$mainFontSize}}">
            @if(array_key_exists($ring, $groupSchedule[$dow]))
                @foreach(
                usort($groupSchedule[$dow][$ring], function($a, $b)  {
                    $num1 = explode(" ", $a['lessons'][0]->groupName)[0];
                    $num2 = explode(" ", $b['lessons'][0]->groupName)[0];

                    if ($num1 == $num2)
                    {
                        if ($a['lessons'][0]->groupName == $b['lessons'][0]->groupName) return 0;
                        return $a['lessons'][0]->groupName < $b['lessons'][0]->groupName ? -1 : 1;
                    }
                    else
                    {
                        return ($num1 < $num2) ? -1 : 1;
                    }
                    return 0;
                }) !== null ? $groupSchedule[$dow][$ring] : null
                as $tfd => $tfdLessons)
                    {{$groupSchedule[$dow][$ring][$tfd]['lessons'][0]->discName}}
                    @if($groupSchedule[$dow][$ring][$tfd]['lessons'][0]->groupName !== $groupName)
                        ({{$groupSchedule[$dow][$ring][$tfd]['lessons'][0]->groupName}})
                    @endif
                    <br />
                    {{$groupSchedule[$dow][$ring][$tfd]["lessons"][0]->teacherFIO}} <br />
                    {{$groupSchedule[$dow][$ring][$tfd]["lessons"][0]->auditoriumName}}
                    @if(!$loop->last)
                        <hr />
                    @endif
                @endforeach
            @endif
        @endfor
    </tr>
    @endforeach
</table>


<div style="text-align: right; font-size: 0.5em;">
    Экспорт произведён: {{$timestamp}}
</div>
</body>
</html>
