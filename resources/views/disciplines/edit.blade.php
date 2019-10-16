@extends('layouts.master')

@section('title')
    Редактирование дисциплины
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/disciplines">Список дисциплин</a></div>
    <div style="text-align: center">Редактирование дисциплины</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <form action="/disciplines/{{$discipline->id}}" method="POST">
            @csrf
            @method('patch')

            <div>
                <p>Название дисциплины</p>
                <input style="margin-top: 5px; width: 300px" name="name" type="text" value="{{$discipline->name}}" required>
            </div>

            <div>
                <p>Класс</p>
                <select name="student_group_id" id="sg">
                    @foreach($studentGroups as $studentGroup)
                        <option value="{{$studentGroup->id}}" @if($studentGroup->id == $discipline->student_group_id) selected @endif>{{$studentGroup->name}}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <p>Количество часов в неделю</p>
                <select style="margin-top: 5px; width: 300px" name="auditorium_hours_per_week">
                    @foreach(range(1, 36) as $hours_count)
                        <option value="{{$hours_count/2}}" @if($hours_count/2 == $discipline->auditorium_hours_per_week) selected @endif>{{$hours_count/2}}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <p>Форма отчётности</p>
                <select style="margin-top: 5px; width: 300px" name="attestation">
                    @foreach(\App\DomainClasses\Discipline::all_attestation() as $attestation)
                        <option value="{{$attestation->id}}" @if($attestation->id == $discipline->attestation) selected @endif >{{$attestation->name}}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-top:1em;">
                <p>
                    <span style="margin-right: 1em;">Активная дисциплина</span>
                    <input type="checkbox" style="transform: scale(2);" name="active" id="activeDiscipline" @if($discipline->active) checked @endif />
                </p>
            </div>

            <div>
                <p>Тип дисциплины</p>
                <select style="margin-top: 5px; width: 300px" name="type">
                    <option value="1" @if($discipline->type == 1) selected @endif>Бюджет</option>
                    <option value="2" @if($discipline->type == 2) selected @endif>Внеурочные занятия</option>
                    <option value="3" @if($discipline->type == 3) selected @endif>Платные занятия</option>
                </select>
            </div>

            <div style="margin-top: 1em;">
            <button type="submit" class="button is-primary">OK</button>
            </div>
        </form>
    </div>
@endsection
