@extends('layouts.master')

@section('title')
    Список классов
@endsection

@section('content')
    <div style="text-align: center">Список классов</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($studentGroups as $studentGroup)
                <tr>
                    <td><a href="/studentGroups/{{$studentGroup->id}}">{{$studentGroup->name}}</a></td>

                    <td>{{$studentGroup->group_size}}</td>

                    <td><a href="/studentGroups/{{$studentGroup->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/studentGroups/{{$studentGroup->id}}">
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
        <a href="/studentGroups/create" class="button is-primary">Добавить класс</a>
    </div>
@endsection
