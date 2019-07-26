@extends('layouts.master')

@section('title')
    Параллель
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/faculties">Список параллелей</a></div>
    <div style="text-align: center">Параллель</div>
    <div class="container" style="flex-direction: column; align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td>{{$faculty->name}}</td>

                <td>{{$faculty->letter}}</td>

                <td>{{$faculty->sorting_order}}</td>

                <td><a href="/faculties/{{$faculty->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/faculties/{{$faculty->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>

        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($facultyStudentGroups as $facultyStudentGroup)
                <tr>
                    <td>{{$facultyStudentGroup->name}}</td>

                    <td>
                        <form method="POST" action="/facultyStudentGroups/{{$facultyStudentGroup->pivot->id}}">
                            @csrf
                            @method('DELETE')
                            <button class="button is-danger">Удалить</button>

                            <input type="hidden" name="faculty_id" value="{{$faculty->id}}">
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>


        <form action="/facultyStudentGroups/store" method="post">
            @csrf

            <select name="student_group_id">
                @foreach($studentGroups as $studentGroup)
                    <option value="{{$studentGroup->id}}">{{$studentGroup->name}}</option>
                @endforeach
            </select>

            <input type="hidden" name="faculty_id" value="{{$faculty->id}}">

            <button style="margin-left: 20px;" type="submit" class="button is-primary">Добавить группу</button>
        </form>
    </div>
@endsection
