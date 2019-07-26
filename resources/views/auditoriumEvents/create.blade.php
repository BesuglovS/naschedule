@extends('layouts.master')

@section('title')
    Новое событие аудитории
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/auditoriumEvents">Список событий в аудиториях</a></div>
    <div style="text-align: center">Новое событие аудитории</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">

        <form action="/auditoriumEvents" method="POST">
            @csrf

            <input style="margin-top: 5px; margin-bottom: 10px; width: 300px" name="name" type="text">

            <div style="margin-bottom: 10px;">
                <select name="calendar_id">
                    @foreach($calendars as $calendar)
                        <option value="{{$calendar->id}}">
                            {{\Carbon\Carbon::createFromFormat('Y-m-d', $calendar->date)->format('d.m.Y')}}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 10px;">
                <select name="ring_id">
                    @foreach($rings as $ring)
                        <option value="{{$ring->id}}">
                            {{substr($ring->time, 0, 5)}}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 10px;">
                <select name="auditorium_id">
                    @foreach($auditoriums as $auditorium)
                        <option value="{{$auditorium->id}}">
                            {{$auditorium->name}}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="button is-primary">Создать</button>


                <a style="margin-left: 20px;" href="/auditoriumEvents" class="button is-danger">Отмена</a>
            </div>
        </form>


    </div>
@endsection
