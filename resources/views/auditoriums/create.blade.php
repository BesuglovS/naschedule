@extends('layouts.master')

@section('title')
    Новая аудитория
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/auditoriums">Список аудиторий</a></div>
    <div style="text-align: center">Новая аудитория</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">

        <form action="/auditoriums" method="POST">
            @csrf

            <input style="margin-top: 5px; width: 300px" name="name" placeholder="Название аудитории" type="text" >

            <select name="building_id" id="b">
                @foreach($buildings as $building)
                    <option value="{{$building->id}}">{{$building->name}}</option>
                @endforeach
            </select>

            <button type="submit" class="button is-primary">Создать</button>
        </form>

        <span style="margin-left: 20px">
            <a href="/auditoriums" class="button is-danger">Отмена</a>
        </span>
    </div>
@endsection
