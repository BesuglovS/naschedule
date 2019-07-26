@extends('layouts.master')

@section('title')
    Новый экзамен
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/exams">Список экзаменов</a></div>
    <div style="text-align: center">Новый экзамен</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">

        <form action="/exams" method="POST">
            @csrf

            <select style="width: 300px;" name="discipline_id">
                @foreach($disciplines as $discipline)
                    <option value="{{$discipline->id}}">{{$discipline->groupName}} {{$discipline->name}}</option>
                @endforeach
            </select>

            <input style="width: 160px;" name="consultation_datetime" type="text">

            <select name="consultation_auditorium_id">
                @foreach($auditoriums as $auditorium)
                    <option value="{{$auditorium->id}}">{{$auditorium->name}}</option>
                @endforeach
            </select>

            <input style="width: 160px;" name="exam_datetime" type="text">

            <select name="exam_auditorium_id">
                @foreach($auditoriums as $auditorium)
                    <option value="{{$auditorium->id}}">{{$auditorium->name}}</option>
                @endforeach
            </select>

            <button type="submit" class="button is-primary">Создать</button>
        </form>

        <span style="margin-left: 20px">
            <a href="/exams" class="button is-danger">Отмена</a>
        </span>
    </div>
@endsection
