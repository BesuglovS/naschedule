@extends('layouts.master')

@section('title')
    Группа учителей
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/teacherGroups">Список групп</a></div>
    <div style="text-align: center">Редактирование группы</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td style="vertical-align: middle">
                    <form action="/teacherGroups/{{$teacherGroup->id}}" method="POST">
                        @csrf
                        @method('patch')
                        <input style="margin-top: 5px; width: 300px" name="name" type="text" value="{{$teacherGroup->name}}">

                        <button type="submit" class="button is-primary">OK</button>
                    </form>
                </td>

                <td>
                    <a href="/buildings" class="button is-danger">Отмена</a>
                </td>
            </tr>
        </table>
    </div>
@endsection
