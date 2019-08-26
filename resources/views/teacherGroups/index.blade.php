@extends('layouts.master')

@section('title')
    Список групп учителей
@endsection

@section('content')
    <div style="text-align: center">Список групп учителей</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($teacherGroups as $teacherGroup)
                <tr>
                    <td><a href="/teacherGroups/{{$teacherGroup->id}}">{{$teacherGroup->name}}</a></td>

                    <td><a href="/teacherGroups/{{$teacherGroup->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/teacherGroups/{{$teacherGroup->id}}">
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
        <a href="/teacherGroups/create" class="button is-primary">Добавить группу</a>
    </div>
@endsection
