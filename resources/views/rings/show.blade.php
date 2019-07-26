@extends('layouts.master')

@section('title')
    Звонок
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/rings">Список звонков</a></div>
    <div style="text-align: center">Отдельный звонок</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
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
        </table>
    </div>
@endsection
