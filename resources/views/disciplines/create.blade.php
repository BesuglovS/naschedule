@extends('layouts.master')

@section('title')
    Новая дисциплина
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/disciplines">Список дисциплин</a></div>
    <div style="text-align: center">Новая дисциплина</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">

        <form action="/disciplines" method="POST">
            @csrf

            <input style="margin-top: 5px; width: 300px" name="name" placeholder="Название дисциплины" type="text" >

            <select name="student_group_id" id="sg">
                @foreach($studentGroups as $studentGroup)
                    <option value="{{$studentGroup->id}}">{{$studentGroup->name}}</option>
                @endforeach
            </select>

            <button type="submit" class="button is-primary">Создать</button>
        </form>

        <span style="margin-left: 20px">
            <a href="/disciplines" class="button is-danger">Отмена</a>
        </span>
    </div>
@endsection
