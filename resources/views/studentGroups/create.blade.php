@extends('layouts.master')

@section('title')
    Новый класс
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/studentGroups">Список классов</a></div>
    <div style="text-align: center">Новый класс</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">

        <form action="/studentGroups" method="POST">
            @csrf

            <input style="margin-top: 5px; width: 300px" name="name" placeholder="Имя класса" type="text" >

            <input style="margin-top: 5px; width: 300px" name="group_size" placeholder="Размер группы" type="text" >

            <button type="submit" class="button is-primary">Создать</button>
        </form>

        <span style="margin-left: 20px">
            <a href="/studentGroups" class="button is-danger">Отмена</a>
        </span>
    </div>
@endsection
