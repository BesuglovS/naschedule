@extends('layouts.master')

@section('title')
    Список учеников
@endsection

@section('content')
    <div style="text-align: center">Список учеников</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($students as $student)
                <tr>
                    <td><a href="/students/{{$student->id}}">{{$student->f}} {{$student->i}} {{$student->o}}</a></td>

                    <td><a href="/students/{{$student->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/students/{{$student->id}}">
                            @csrf
                            @method('DELETE')
                            <button class="button is-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    <div style="text-align: center">
        <a href="/students/create" class="button is-primary">Добавить ученика</a>
    </div>
@endsection
