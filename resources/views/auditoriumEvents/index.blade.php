@extends('layouts.master')

@section('title')
    Занятость аудиторий
@endsection

@section('content')
    <div style="text-align: center">Занятость аудиторий</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($auditoriumEvents as $auditoriumEvent)
                <tr>
                    <td><a href="/auditoriumEvents/{{$auditoriumEvent->id}}">{{$auditoriumEvent->name}}</a></td>

                    <td>{{\Carbon\Carbon::createFromFormat('Y-m-d', $auditoriumEvent->calendar_date)->format('d.m.Y')}}</td>

                    <td>{{substr($auditoriumEvent->ring_time, 0, 5)}}</td>

                    <td>{{$auditoriumEvent->auditoriumName}}</td>

                    <td><a href="/auditoriumEvents/{{$auditoriumEvent->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/auditoriumEvents/{{$auditoriumEvent->id}}">
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
        <a href="/auditoriumEvents/create" class="button is-primary">Добавить событие аудитории</a>
    </div>
@endsection
