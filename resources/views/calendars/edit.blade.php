@extends('layouts.master')

@section('title')
    Дата
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/calendars">Список дней семестра</a></div>
    <div style="text-align: center">Редактирование даты</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td style="vertical-align: middle">
                    <form action="/calendars/{{$calendar->id}}" method="POST">
                        @csrf
                        @method('patch')
                        <select name="day" id="day">
                            @foreach(range(1, 31, 1) as $day)
                                <option value="{{$day}}" @if($day == substr($calendar->date, 8, 2)) selected @endif >{{$day}}</option>
                            @endforeach
                        </select>

                        <select name="month" id="month">
                            @foreach(range(1, 12, 1) as $month)
                                <option value="{{$month}}" @if($month == substr($calendar->date, 5, 2)) selected @endif >{{$month}}</option>
                            @endforeach
                        </select>

                        <select name="year" id="year">
                            @foreach(range(1988, 2088, 1) as $year)
                                <option value="{{$year}}" @if($year == substr($calendar->date, 0, 4)) selected @endif >{{$year}}</option>
                            @endforeach
                        </select>

                        <input style="width: 30px;" name="state" type="text" value="{{$calendar->state}}">

                        <button type="submit" class="button is-primary">OK</button>
                    </form>
                </td>

                <td>
                    <a href="/calendars" class="button is-danger">Отмена</a>
                </td>
            </tr>
        </table>
    </div>
@endsection
