@extends('layouts.master')

@section('title')
    Экзамен
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/exams">Список экзаменов</a></div>
    <div style="text-align: center">Редактирование экзамена</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td style="vertical-align: middle">
                    <form action="/exams/{{$exam->id}}" method="POST">
                        @csrf
                        @method('patch')

                        <select style="width: 300px;" name="discipline_id">
                            @foreach($disciplines as $discipline)
                                <option value="{{$discipline->id}}" @if($discipline->id == $exam->discipline_id) selected @endif >{{$discipline->groupName}} {{$discipline->name}}</option>
                            @endforeach
                        </select>

                        <input style="width: 160px;" name="consultation_datetime" type="text" value="{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $exam->consultation_datetime)->format('d.m.Y H:i:s')}}">

                        <select name="consultation_auditorium_id">
                            @foreach($auditoriums as $auditorium)
                                <option value="{{$auditorium->id}}" @if($auditorium->id == $exam->consultation_auditorium_id) selected @endif >{{$auditorium->name}}</option>
                            @endforeach
                        </select>

                        <input style="width: 160px;" name="exam_datetime" type="text" value="{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $exam->exam_datetime)->format('d.m.Y H:i:s')}}">

                        <select name="exam_auditorium_id">
                            @foreach($auditoriums as $auditorium)
                                <option value="{{$auditorium->id}}" @if($auditorium->id == $exam->exam_auditorium_id) selected @endif >{{$auditorium->name}}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="button is-primary">OK</button>
                    </form>
                </td>

                <td>
                    <a href="/exams" class="button is-danger">Отмена</a>
                </td>
            </tr>
        </table>
    </div>
@endsection
