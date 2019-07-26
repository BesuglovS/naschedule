@extends('layouts.master')

@section('title')
    Список звонков
@endsection

@section('content')
    <div style="text-align: center">Список звонков</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($rings as $ring)
                <tr>
                    <td><a href="/rings/{{$ring->id}}">{{substr($ring->time, 0, 5)}}</a></td>

                    <td><a href="/rings/{{$ring->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/rings/{{$ring->id}}">
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
        <a href="/rings/create" class="button is-primary">Добавить звонок</a>
    </div>
@endsection
