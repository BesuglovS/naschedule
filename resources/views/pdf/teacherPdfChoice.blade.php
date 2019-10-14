@extends('layouts.master')

@section('title')
    Экспорт в PDF
@endsection

@section('content')
    <div style="text-align: center; font-size: 2em; font-weight: 700;">
        Расписание преподавателя на неделю в PDF
    </div>

    <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center; margin-top: 2em;">
        <form action="/teacherPdfWeek" method="get">
            <table style="font-size: 1.5em;">
                <tr style="padding-bottom: 1em;">
                    <td>Преподаватель</td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>
                        <select style="margin-right: 1em; font-size: 1.5em; width: 600px;" name="teacherId">
                            @foreach($teachers as $teacher)
                                <option value="{{$teacher['id']}}">{{$teacher['fio']}}</option>
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
                        <select style="margin-right: 1em; font-size: 1.5em; width: 600px;" name="week" id="week">
                            @for ($i = 1; $i <= $weekCount ; $i++)
                                <option value="{{$i}}" @if($i === $currentWeek) selected @endif>{{$i}} ({{$weeks[$i]}})</option>
                            @endfor
                        </select>
                    </td>
                </tr>
            </table>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button type="submit" style="font-size: 2em;" class="button is-primary">Создать PDF-файл</button>
            </div>
        </form>
    </div>
@endsection
