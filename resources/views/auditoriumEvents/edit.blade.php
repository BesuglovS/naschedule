@extends('layouts.master')

@section('title')
    Редактирование собития аудитории
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/auditoriumEvents">Список событий в аудиториях</a></div>
    <div style="text-align: center">Редактирование собития аудитории</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <form action="/auditoriumEvents/{{$auditoriumEvent->id}}" method="POST">
            @csrf
            @method('patch')

            <input style="margin-top: 5px; margin-bottom: 10px; width: 300px" name="name" type="text" value="{{$auditoriumEvent->name}}">

            <div style="margin-bottom: 10px;">
                <select name="calendar_id">
                    @foreach($calendars as $calendar)
                        <option value="{{$calendar->id}}" @if($calendar->id == $auditoriumEvent->calendar_id) selected @endif>
                            {{\Carbon\Carbon::createFromFormat('Y-m-d', $calendar->date)->format('d.m.Y')}}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 10px;">
                <select name="ring_id">
                    @foreach($rings as $ring)
                        <option value="{{$ring->id}}" @if($ring->id == $auditoriumEvent->ring_id) selected @endif>
                            {{substr($ring->time, 0, 5)}}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 10px;">
                <select name="auditorium_id">
                    @foreach($auditoriums as $auditorium)
                        <option value="{{$auditorium->id}}" @if($auditorium->id == $auditoriumEvent->auditorium_id) selected @endif>
                            {{$auditorium->name}}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="button is-primary">OK</button>
        </form>
    </div>
@endsection
