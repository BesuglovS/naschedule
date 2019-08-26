@extends('layouts.master')

@section('title')
    Группа учителей
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/teacherGroups">Список групп</a></div>
    <div style="text-align: center">Группа учителей</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td><a href="/teacherGroups/{{$teacherGroup->id}}">{{$teacherGroup->name}}</a></td>
                <td><a href="/teacherGroups/{{$teacherGroup->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/teacherGroups/{{$teacherGroup->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>

    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($groupTeachers as $groupTeacher)
                <tr>
                    <td>{{$groupTeacher->fio}}</td>

                    <td>
                        <form method="POST" action="/teacherTeacherGroups/{{$groupTeacher->pivot->id}}">
                            @csrf
                            @method('DELETE')
                            <button class="button is-danger">Удалить</button>

                            <input type="hidden" name="teacher_group_id" value="{{$teacherGroup->id}}">
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
        <form action="/teacherTeacherGroups/store" method="post">
            @csrf

            <select name="teacher_id">
                @foreach($teachers as $teacher)
                    <option value="{{$teacher->id}}">{{$teacher->fio}}</option>
                @endforeach
            </select>

            <input type="hidden" name="teacher_group_id" value="{{$teacherGroup->id}}">

            <button style="margin-left: 20px;" type="submit" class="button is-primary">Добавить учителя</button>
        </form>
    </div>
@endsection
