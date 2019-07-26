@extends('layouts.master')

@section('title')
    Список дисциплин
@endsection

@section('content')
    <div style="text-align: center">Список дисциплин</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($disciplines as $discipline)
                <tr>
                    <td><a href="/disciplines/{{$discipline->id}}">{{$discipline->name}}</a></td>

                    <td>{{$discipline->fio}}</td>

                    <td>{{$discipline->groupName}}</td>

                    <td>{{\App\DomainClasses\Discipline::attestation_string($discipline->attestation)}}</td>

                    <td><a href="/disciplines/{{$discipline->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/disciplines/{{$discipline->id}}">
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
        <a href="/disciplines/create" class="button is-primary">Добавить дисциплину</a>
    </div>
@endsection
