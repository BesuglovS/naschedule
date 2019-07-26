@extends('layouts.master')

@section('title')
    Новая параллель
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/faculties">Список параллелей</a></div>
    <div style="text-align: center">Новая параллель</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">

        <form action="/faculties" method="POST">
            @csrf

            <table style="margin-bottom: 10px">
                <tr>
                    <td style="vertical-align: middle">Название параллели</td>
                    <td style="width: 20px"> </td>
                    <td>
                        <input style="margin-top: 5px; width: 300px" name="name" type="text">
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align: middle">Буква / Сокращение</td>
                    <td style="width: 20px"> </td>
                    <td>
                        <input style="margin-top: 5px; width: 300px" name="letter" type="text">
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align: middle">Порядок сортировки (число)</td>
                    <td style="width: 20px"> </td>
                    <td>
                        <input style="margin-top: 5px; width: 300px" name="sorting_order" type="text">
                    </td>
                </tr>
                <tr style="height: 20px"></tr>

                <tr>
                    <td style="text-align: center"><button type="submit" class="button is-primary">OK</button></td>
                    <td style="width: 20px"> </td>
                    <td style="text-align: center"><button type="submit" class="button is-danger"><a href="/faculties">Отмена</a></button></td>
                </tr>
            </table>
        </form>

    </div>
@endsection
