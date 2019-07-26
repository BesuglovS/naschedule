@extends('layouts.master')

@section('title')
    Дата
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/calendars">Список дней семестра</a></div>
    <div style="text-align: center">Отдельная дата</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td><a href="/calendars/{{$calendar->id}}">{{\Carbon\Carbon::createFromFormat('Y-m-d', $calendar->date)->format('d.m.Y')}}</a></td>

                <td>{{$calendar->state}}</td>

                <td><a href="/calendars/{{$calendar->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/calendars/{{$calendar->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
@endsection
