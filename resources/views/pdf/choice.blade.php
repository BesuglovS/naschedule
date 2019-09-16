@extends('layouts.master')

@section('title')
    Экспорт в PDF
@endsection

@section('content')
    <div style="text-align: center; font-size: 2em; font-weight: 700;">
        Экспорт расписания параллели <br />
        на один день одной конкретной недели в PDF
    </div>

    <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center; margin-top: 2em;">
        <form action="/print-pdf" method="get">
        <table style="font-size: 1.5em;">
            <tr style="padding-bottom: 1em;">
                <td>Параллель</td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td>
                    <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="facultyId" id="facultyId">
                        @foreach($faculties as $faculty)
                            <option value="{{$faculty->id}}">{{$faculty->name}}</option>
                        @endforeach
                    </select>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            <tr>
                <td>Неделя</td>
                <td>&nbsp;</td>
                <td>
                    <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="week" id="week">
                        @for ($i = 1; $i <= $weekCount ; $i++)
                            <option value="{{$i}}" @if($i === $currentWeek) selected @endif>{{$i}} ({{$weeks[$i]}})</option>
                        @endfor
                    </select>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            <tr>
            <tr>
                <td>День недели</td>
                <td>&nbsp;</td>
                <td>
                    <select style="font-size: 1.5em; width: 310px;" name="dow" id="dow">
                        <option value="1">Понедельник</option>
                        <option value="2">Вторник</option>
                        <option value="3">Среда</option>
                        <option value="4">Четверг</option>
                        <option value="5">Пятница</option>
                        <option value="6">Суббота</option>
                    </select>
                </td>
            </tr>
        </table>

        <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
            <button type="submit" style="font-size: 2em;" class="button is-primary">Создать PDF-файл</button>
        </div>
        </form>

        <table style="margin-top: 1.5em;" class="table td-center is-bordered">
            <tr>
                <td colspan="8" style="font-size: 2em; font-weight: bold;">
                    Скачать расписание
                </td>
            </tr>
            @foreach($faculties as $faculty)
                <tr>
                    <td>{{$faculty->name}}</td>
                    <td style="padding: 0;">
                        <form action="/bulkDownload-pdf" method="post">
                            @csrf
                            <table style="border: none;">
                                <tr>
                                    <td style="border: none;">
                                        <button type="submit" style="font-size: 1em;" class="button btn-sm is-primary">Вся неделя</button>
                                    </td>
                                    <td style="border: none; vertical-align: middle;">
                                        <select @change="" style="margin-right: 1em; font-size: 1em; width: 40px;" name="week" id="week2">
                                            @for ($j = 1; $j <= $weekCount ; $j++)
                                                <option value="{{$j}}" @if($j === $currentWeek) selected @endif>{{$j}}</option>
                                            @endfor
                                        </select>
                                    </td>
                                </tr>
                            </table>

                            <input type="hidden" name="facultyId" value="{{$faculty->id}}">
                            <input type="hidden" name="dow" value="{{$i}}">
                        </form>
                    </td>
                    @for ($i = 1; $i <= 6; $i++)
                        <td style="padding: 0;">
                            <form action="/download-pdf" method="post">
                                @csrf
                                <table style="border: none;">
                                    <tr>
                                        <td style="border: none;">
                                            <button type="submit" style="font-size: 1em;" class="button btn-sm is-primary">{{$dowRu[$i-1]}}</button>
                                        </td>
                                        <td style="border: none; vertical-align: middle;">
                                            <select @change="" style="margin-right: 1em; font-size: 1em; width: 40px;" name="week" id="week2">
                                                @for ($j = 1; $j <= $weekCount ; $j++)
                                                    <option value="{{$j}}" @if($j === $currentWeek) selected @endif>{{$j}}</option>
                                                @endfor
                                            </select>
                                        </td>
                                    </tr>
                                </table>

                                <input type="hidden" name="facultyId" value="{{$faculty->id}}">
                                <input type="hidden" name="dow" value="{{$i}}">
                            </form>
                        </td>
                    @endfor
                </tr>
            @endforeach
        </table>
    </div>
@endsection
