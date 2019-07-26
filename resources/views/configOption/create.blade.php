@extends('layouts.master')

@section('title')
    Корпус
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/configOptions">Список опций</a></div>
    <div style="text-align: center">Новый корпус</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">

        <form action="/configOptions" method="POST">
            @csrf

            <input style="margin-top: 5px; width: 300px" name="key" placeholder="Ключ" type="text" >

            <input style="margin-top: 5px; width: 300px" name="value" placeholder="Значение" type="text" >

            <button type="submit" class="button is-primary">Создать</button>
        </form>

        <span style="margin-left: 20px">
            <a href="/configOptions" class="button is-danger">Отмена</a>
        </span>
    </div>
@endsection
