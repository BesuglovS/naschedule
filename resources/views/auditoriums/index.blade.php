@extends('layouts.master')

@section('title')
    Список аудиторий
@endsection

@section('content')
    <div style="text-align: center">Список аудиторий</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($auditoriums as $auditorium)
                <tr>
                    <td><a href="/auditoriums/{{$auditorium->id}}">{{$auditorium->name}}</a></td>

                    <td>{{$auditorium->buildingName}}</td>

                    <td>{{$auditorium->capacity}}</td>

                    <td><a href="/auditoriums/{{$auditorium->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/auditoriums/{{$auditorium->id}}">
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
        <a href="/auditoriums/create" class="button is-primary">Добавить аудиторию</a>
    </div>
@endsection
