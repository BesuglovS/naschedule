@extends('layouts.master')

@section('title')
    Корпус
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/buildings">Список корпусов</a></div>
    <div style="text-align: center">Отдельный корпус</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td><a href="/buildings/{{$building->id}}">{{$building->name}}</a></td>
                <td><a href="/buildings/{{$building->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/buildings/{{$building->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>

    <div style="text-align: center">Список аудиторий</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($auditoriums as $auditorium)
                <tr>
                    <td><a href="/auditoriums/{{$auditorium->id}}">{{$auditorium->name}}</a></td>

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
@endsection
