@extends('layouts.master')

@section('title')
    Список опций
@endsection

@section('content')
    <div style="text-align: center">Список опций</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($configOptions as $configOption)
                <tr>
                    <td><a href="/configOptions/{{$configOption->id}}">{{$configOption->key}}</a></td>

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
            @endforeach
        </table>
    </div>
    <div style="text-align: center">
        <a href="/configOptions/create" class="button is-primary">Добавить опцию</a>
    </div>
@endsection
