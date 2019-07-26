@extends('layouts.master')

@section('title')
    Звонок
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/rings">Список звонков</a></div>
    <div style="text-align: center">Редактирование звонка</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td style="vertical-align: middle">
                    <form action="/rings/{{$ring->id}}" method="POST">
                        @csrf
                        @method('patch')
                        <select name="hours" id="hrs">
                            @foreach(range(0, 23, 1) as $hour)
                                <option value="{{$hour}}" @if($hour == substr($ring->time, 0, 2)) selected @endif >{{$hour}}</option>
                            @endforeach
                        </select>

                        <select name="minutes" id="mns">
                            @foreach(range(0, 59, 1) as $minute)
                                <option value="{{$minute}}" @if($minute == substr($ring->time, 3, 2)) selected @endif >{{$minute}}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="button is-primary">OK</button>
                    </form>
                </td>

                <td>
                    <a href="/rings" class="button is-danger">Отмена</a>
                </td>
            </tr>
        </table>
    </div>
@endsection
