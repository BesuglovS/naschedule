@extends('layouts.master')

@section('title')
    Опция
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/configOptions">Список опций</a></div>
    <div style="text-align: center">Опция</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td>{{$configOption->key}}</td>
                <td>{{$configOption->value}}</td>
                <td><a href="/configOptions/{{$configOption->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/configOptions/{{$configOption->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
@endsection
