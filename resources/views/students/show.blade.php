@extends('layouts.master')

@section('title')
    Ученик
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/students">Список учеников</a></div>
    <div style="text-align: center">Отдельный Ученик</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td>{{$student->f}} {{$student->i}} {{$student->o}}</td>

                <td><a href="/students/{{$student->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/students/{{$student->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
@endsection
