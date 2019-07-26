@extends('layouts.master')

@section('title')
    Список параллелей
@endsection

@section('content')
    <div style="text-align: center">Список параллелей</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($faculties as $faculty)
                <tr>
                    <td><a href="/faculties/{{$faculty->id}}">{{$faculty->name}}</a></td>

                    <td>{{$faculty->letter}}</td>

                    <td>{{$faculty->sorting_order}}</td>

                    <td><a href="/faculties/{{$faculty->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/faculties/{{$faculty->id}}">
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
        <a href="/faculties/create" class="button is-primary">Добавить параллель</a>
    </div>
@endsection
