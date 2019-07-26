@extends('layouts.master')

@section('title')
    Список учителей
@endsection

@section('content')
    <div style="text-align: center">Список учителей</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($teachers as $teacher)
                <tr>
                    <td><a href="/teachers/{{$teacher->id}}">{{$teacher->fio}}</a></td>

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
            @endforeach
        </table>
    </div>
    <div style="text-align: center">
        <a href="/teachers/create" class="button is-primary">Добавить учителя</a>
    </div>
@endsection
