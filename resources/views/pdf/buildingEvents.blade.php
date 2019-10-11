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
    <h2 style="text-align: center">
        {{$title1}} <br />
        {{$title2}} <br />
        {{$title3}}
    </h2>

    <table style="margin-top: 2em; border-collapse: collapse; width: 100%;" class="table td-center is-bordered">
        <tr>
            <td></td>
            @foreach($auditoriums as $auditorium)
                <td style="font-size: {{$mainFontSize}}">{{$auditorium['name']}}</td>
            @endforeach
        </tr>
        @foreach($rings as $ring)
            <tr>
                <td style="font-size: {{$mainFontSize}}">{{$ring['time']}}</td>
                @foreach($auditoriums as $auditorium)
                    <td style="font-size: {{$mainFontSize}}">
                    @if(array_key_exists($ring['id'], $events) && array_key_exists($auditorium['id'], $events[$ring['id']]))
                       @foreach($events[$ring['id']][$auditorium['id']] as $tfdId => $tfdEvents)
                           {{$tfdEvents['lessons'][0]->studentGroupName}}
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
