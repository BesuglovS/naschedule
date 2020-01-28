@extends('layouts.masterNoAuth')

@section('title')
    Список аудиторий 5-11
@endsection

@section('content')
    <div style="text-align: center">Список аудиторий 5-11</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($textResult as $item)
                <tr>
                    <td>{{$item["Класс"]}}</td>

                    <td>{{$item["Дисциплина"]}}</td>

                    <td>{{$item["Учитель"]}}</td>

                    <td>{{$item["Кабинет"]}}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
