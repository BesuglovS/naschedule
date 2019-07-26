@extends('layouts.master')

@section('title')
    Редактирование опции
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/configOptions">Список опций</a></div>
    <div style="text-align: center">Редактирование опции</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <form action="/configOptions/{{$configOption->id}}" method="POST">
            @csrf
            @method('patch')

            <input style="margin-top: 5px; width: 300px" name="key" type="text" value="{{$configOption->key}}">

            <input style="margin-top: 5px; width: 300px" name="value" type="text" value="{{$configOption->value}}">

            <button type="submit" class="button is-primary">OK</button>
        </form>
    </div>
@endsection
