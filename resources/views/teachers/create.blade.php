@extends('layouts.master')

@section('title')
    Новый учитель
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/teachers">Список учителей</a></div>
    <div style="text-align: center">Новый учитель</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">

        <form action="/teachers" method="POST">
            @csrf

            <input style="margin-top: 5px; width: 300px" name="fio" placeholder="ФИО" type="text" >

            <input style="margin-top: 5px; width: 300px" name="phone" placeholder="Телефон" type="text" >

            <button type="submit" class="button is-primary">Создать</button>
        </form>

        <span style="margin-left: 20px">
            <a href="/teachers" class="button is-danger">Отмена</a>
        </span>
    </div>
@endsection
