@extends('layouts.master')

@section('title')
    Редактирование аудитории
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/auditoriums">Список аудиторий</a></div>
    <div style="text-align: center">Редактирование аудитории</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <form action="/auditoriums/{{$auditorium->id}}" method="POST">
            @csrf
            @method('patch')

            <input style="margin-top: 5px; width: 300px" name="name" type="text" value="{{$auditorium->name}}">

            <input style="margin-top: 5px; width: 300px" name="capacity" type="text" value="{{$auditorium->capacity}}">

            <select name="building_id" id="b">
                @foreach($buildings as $building)
                    <option value="{{$building->id}}" @if($building->id == $auditorium->building_id) selected @endif >{{$building->name}}</option>
                @endforeach
            </select>

            <button type="submit" class="button is-primary">OK</button>
        </form>
    </div>
@endsection
