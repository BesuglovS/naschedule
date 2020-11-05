@extends('layouts.master')

@section('content')
    <div style="text-align: center; font-size: 3em; margin-bottom: 0.5em; font-weight: 700;">
        Описание урока
    </div>
    <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center;">
        <table style="width: 90%;margin-bottom: 2em;" class="is-bordered modalTable">
            <tr>
                <td style="text-align: center;">Дата</td>
                <td style="text-align: center;">Время</td>
                <td style="text-align: center;">Предмет</td>
                <td style="text-align: center;">Класс</td>
                <td style="text-align: center;">ФИО учителя</td>
            </tr>
            <tr>
                <td style="text-align: center;">{{$lesson->lesson_date}}</td>
                <td style="text-align: center;">{{$lesson->rings_time}}</td>
                <td style="text-align: center;">{{$lesson->disc_name}}</td>
                <td style="text-align: center;">{{$lesson->group_name}}</td>
                <td style="text-align: center;">{{$lesson->fio}}</td>
            </tr>
        </table>

        <textarea readonly name="lesdesc" id="lesdesc" cols="140" rows="20">@if (empty($lesson->description))Описание пустое
            @else
                {{$lesson->description}}
            @endif
        </textarea>
    </div>
@endsection
