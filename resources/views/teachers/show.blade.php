@extends('layouts.master')

@section('title')
    Учитель
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/teachers">Список учителей</a></div>
    <div style="text-align: center">Учитель</div>
    <div class="container" style="flex-direction: column; align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td>{{$teacher->fio}}</td>
                <td>{{$teacher->phone}}</td>

                <td><a href="/teachers/{{$teacher->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/teachers/{{$teacher->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>

        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($teacherDisciplines as $teacherDiscipline)
                <tr>
                    <td>{{$teacherDiscipline->groupName}}</td>

                    <td>{{$teacherDiscipline->name}}</td>

                    <td>{{$teacherDiscipline->auditorium_hours_per_week}}</td>

                    <td>
                        <form method="POST" action="/teacherDisciplines/{{$teacherDiscipline->discipline_teacher_id}}">
                            @csrf
                            @method('DELETE')
                            <button class="button is-danger">Удалить</button>

                            <input type="hidden" name="teacher_id" value="{{$teacher->id}}">
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>

        @if(!$disciplines->isEmpty())
            <form action="/teacherDisciplines/store" method="post">
                @csrf

                <select name="discipline_id">
                    @foreach($disciplines as $discipline)
                        <option value="{{$discipline->id}}">{{$discipline->groupName}} {{$discipline->name}} </option>
                    @endforeach
                </select>

                <input type="hidden" name="teacher_id" value="{{$teacher->id}}">

                <button style="margin-left: 20px;" type="submit" class="button is-primary">Добавить дисциплину</button>
            </form>
        @else
            <div>
                <div class="card">
                    <div class="card-body">Нет свободных дисциплин</div>
                </div>
            </div>
        @endif

    </div>
@endsection
