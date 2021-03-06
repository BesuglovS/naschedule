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

            <div>
                <p>Название дисциплины</p>
                <input style="margin-top: 5px; width: 300px" name="name" type="text" required>
            </div>

            <div>
                <p>Класс</p>
                <select name="student_group_id" id="sg">
                    @foreach($studentGroups as $studentGroup)
                        <option @if(request('studentGroupId', '') == $studentGroup->id) selected @endif value="{{$studentGroup->id}}">{{$studentGroup->name}}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <p>Количество часов в неделю</p>
                <select style="margin-top: 5px; width: 300px" name="auditorium_hours_per_week">
                    @foreach(range(1, 36) as $hours_count)
                        <option value="{{$hours_count/2}}">{{$hours_count/2}}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <p>Форма отчётности</p>
                <select style="margin-top: 5px; width: 300px" name="attestation">
                    @foreach(\App\DomainClasses\Discipline::all_attestation() as $attestation)
                        <option value="{{$attestation->id}}">{{$attestation->name}}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-top:1em;">
                <p>
                    <span style="margin-right: 1em;">Активная дисциплина</span>
                    <input type="checkbox" style="transform: scale(2);" name="active" id="activeDiscipline" checked />
                </p>
            </div>

            <div>
                <p>Тип дисциплины</p>
                <select style="margin-top: 5px; width: 300px" name="type">
                    <option value="1">Бюджет</option>
                    <option value="2">Внеурочные занятия</option>
                    <option value="3">Платные занятия</option>
                    <option value="4">Электив</option>
                </select>
            </div>

            <div style="margin-top: 1em;">
                <button type="submit" class="button is-primary">Создать</button>

                <span style="margin-left: 20px">
                <a href="/disciplines" class="button is-danger">Отмена</a>
                </span>
            </div>
        </form>
    </div>
@endsection
