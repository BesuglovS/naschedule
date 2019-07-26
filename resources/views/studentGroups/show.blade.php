@extends('layouts.master')

@section('title')
    Класс
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/studentGroups">Список классов</a></div>
    <div style="text-align: center">Класс</div>
    <div class="container" style="flex-direction: column; align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td>{{$studentGroup->name}}</td>

                <td><a href="/studentGroups/{{$studentGroup->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/studentGroups/{{$studentGroup->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>


        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($groupStudents as $groupStudent)
                <tr>
                    <td>{{$groupStudent->f}} {{$groupStudent->i}} {{$groupStudent->o}}</td>

                    <td>
                        <form method="POST" action="/studentStudentGroups/{{$groupStudent->pivot->id}}">
                            @csrf
                            @method('DELETE')
                            <button class="button is-danger">Удалить</button>

                            <input type="hidden" name="student_group_id" value="{{$studentGroup->id}}">
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>

        <form action="/studentStudentGroups/store" method="post">
            @csrf

            <select name="student_id">
                @foreach($students as $student)
                    <option value="{{$student->id}}">{{$student->f}} {{$student->i}} {{$student->o}}</option>
                @endforeach
            </select>

            <input type="hidden" name="student_group_id" value="{{$studentGroup->id}}">

            <button style="margin-left: 20px;" type="submit" class="button is-primary">Добавить ученика</button>
        </form>
    </div>
@endsection
