@extends('layouts.masterNoAuth')

@section('title')
    Список аудиторий 5-11
@endsection

@section('content')
    <div style="text-align: center">Список аудиторий 5-11</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td style="font-weight: 700; background-color: #0ec5c988">Класс</td>
                <td style="font-weight: 700; background-color: #0ec5c988">Дисциплина</td>
                <td style="font-weight: 700; background-color: #0ec5c988">Учитель</td>
                <td style="font-weight: 700; background-color: #0ec5c988">Кабинет</td>
                <td style="font-weight: 700; background-color: #0ec5c988">Тип занятия</td>
            </tr>

            @foreach($textResult as $item)
                <tr>
                    <td>{{$item["Класс"]}}</td>

                    <td>{{$item["Дисциплина"]}}</td>

                    <td>{{$item["Учитель"]}}</td>

                    <td>{{$item["Кабинет"]}}</td>

                    <td @if($item["Тип дисциплины"] == "Платные") style="background-color: #FF7F7F;" @endif >{{$item["Тип дисциплины"]}}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
