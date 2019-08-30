@extends('layouts.master')

@section('title')
    Редактирование учителя
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/teachers">Список учителей</a></div>
    <div style="text-align: center">Редактирование учителя</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <form action="/teachers/{{$teacher->id}}" method="POST">
            @csrf
            @method('patch')

            <input style="margin-top: 5px; width: 300px" name="fio" type="text" value="{{$teacher->fio}}">

            <input style="margin-top: 5px; width: 300px" name="phone" type="text" value="{{$teacher->phone}}">

            <button type="submit" class="button is-primary">OK</button>
        </form>
    </div>
@endsection
