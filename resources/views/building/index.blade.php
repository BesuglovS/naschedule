@extends('layouts.master')

@section('title')
    Список корпусов
@endsection

@section('content')
    <div style="text-align: center">Список корпусов</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($buildings as $building)
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
            @endforeach
        </table>
    </div>
    <div style="text-align: center">
        <a href="/buildings/create" class="button is-primary">Добавить корпус</a>
    </div>
@endsection
