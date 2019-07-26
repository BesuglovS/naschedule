@extends('layouts.master')

@section('title')
    Звонок
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/rings">Список звонков</a></div>
    <div style="text-align: center">Новый звонок</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td style="vertical-align: middle">
                    <form action="/rings" method="POST">
                        @csrf
                        <select name="hours" id="hrs">
                            @foreach(range(0, 23, 1) as $hour)
                                <option value="{{$hour}}">{{$hour}}</option>
                            @endforeach
                        </select>

                        <select name="minutes" id="mns">
                            @foreach(range(0, 59, 1) as $minute)
                                <option value="{{$minute}}">{{$minute}}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="button is-primary">Создать</button>
                    </form>
                </td>

                <td>
                    <a href="/rings" class="button is-danger">Отмена</a>
                </td>
            </tr>
        </table>
    </div>
@endsection
