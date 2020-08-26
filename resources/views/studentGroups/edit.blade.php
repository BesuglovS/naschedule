@extends('layouts.master')

@section('title')
    Редактирование класса
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/studentGroups">Список классов</a></div>
    <div style="text-align: center">Редактирование класса</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <form action="/studentGroups/{{$studentGroup->id}}" method="POST">
            @csrf
            @method('patch')

            <input style="margin-top: 5px; width: 300px" name="name" type="text" value="{{$studentGroup->name}}">

            <input style="margin-top: 5px; width: 300px" name="group_size" type="text" value="{{$studentGroup->group_size}}">

            <button type="submit" class="button is-primary">OK</button>
        </form>
    </div>
@endsection
