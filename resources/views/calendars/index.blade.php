@extends('layouts.master')

@section('title')
    Список дат
@endsection

@section('content')
    <div style="text-align: center">Список дат</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($calendars as $calendar)
                <tr>
                    <td><a href="/calendars/{{$calendar->id}}">{{\Carbon\Carbon::createFromFormat('Y-m-d', $calendar->date)->format('d.m.Y')}}</a></td>

                    <td>{{$calendar->state}}</td>

                    <td><a href="/calendars/{{$calendar->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/calendars/{{$calendar->id}}">
                            @csrf
                            @method('DELETE')
                            <button class="button is-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="container">
            <form action="/calendars/range" method="POST">
                @csrf
                <div class="container" style="margin: 10px;">
                    <p>Дата начала</p>
                    <select name="day1" id="day1">
                        @foreach(range(1, 31, 1) as $day)
                            <option value="{{$day}}">{{$day}}</option>
                        @endforeach
                    </select>

                    <select name="month1" id="month1">
                        @foreach(range(1, 12, 1) as $month)
                            <option value="{{$month}}">{{$month}}</option>
                        @endforeach
                    </select>

                    <select name="year1" id="year1">
                        @foreach(range(1988, 2088, 1) as $year)
                            <option value="{{$year}}">{{$year}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="container" style="margin: 10px;">
                    <p>Дата окончания</p>
                    <select name="day2" id="day2">
                        @foreach(range(1, 31, 1) as $day)
                            <option value="{{$day}}">{{$day}}</option>
                        @endforeach
                    </select>

                    <select name="month2" id="month2">
                        @foreach(range(1, 12, 1) as $month)
                            <option value="{{$month}}">{{$month}}</option>
                        @endforeach
                    </select>

                    <select name="year2" id="year2">
                        @foreach(range(1988, 2088, 1) as $year)
                            <option value="{{$year}}">{{$year}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="container" style="margin: 10px;">
                    <input style="width: 30px;" name="state" type="text" value="1">
                </div>

                <button type="submit" class="button is-primary">Создать даты в диапазоне</button>
            </form>
        </div>
    </div>
    <div style="text-align: center">
        <a href="/calendars/create" class="button is-primary">Добавить дату</a>
    </div>
@endsection
