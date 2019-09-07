@extends('layouts.master')

@section('title')
    Экспорт в PDF
@endsection

@section('content')
    <div style="text-align: center; font-size: 2em; font-weight: 700;">
        Экспорт расписания параллели на одну конкретную неделю в PDF
    </div>
    <form action="/print-pdf" method="get">
        <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
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
                                <option value="{{$i}}">{{$i}} ({{$weeks[$i]}})</option>
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






        </div>

        <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
            <button type="submit" style="font-size: 2em;" class="button is-primary">Создать PDF-файл</button>
        </div>
    </form>
@endsection
