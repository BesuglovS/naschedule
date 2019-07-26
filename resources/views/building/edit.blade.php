@extends('layouts.master')

@section('title')
    Корпус
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/buildings">Список корпусов</a></div>
    <div style="text-align: center">Редактирование корпуса</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td style="vertical-align: middle">
                    <form action="/buildings/{{$building->id}}" method="POST">
                        @csrf
                        @method('patch')
                        <input style="margin-top: 5px; width: 300px" name="name" type="text" value="{{$building->name}}">

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
