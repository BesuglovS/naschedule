@extends('layouts.master')

@section('title')
    Аудитория
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/auditoriums">Список аудиторий</a></div>
    <div style="text-align: center">Аудитория</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td>{{$auditorium->name}}</td>

                <td>{{$auditorium->buildingName}}</td>

                <td><a href="/auditoriums/{{$auditorium->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/auditoriums/{{$auditorium->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
@endsection
