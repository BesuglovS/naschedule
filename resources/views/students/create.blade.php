@extends('layouts.master')

@section('title')
    Новый ученик
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/students">Список учеников</a></div>
    <div style="text-align: center">Новый ученик</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">

        <form action="/students" method="POST">
            @csrf

            <table>
                <tr>
                    <td style="vertical-align: middle">Фамилия</td>
                    <td style="width: 20px"> </td>
                    <td>
                        <input style="margin-top: 5px; width: 300px" name="f" type="text" placeholder="Фамилия">
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align: middle">Имя</td>
                    <td style="width: 20px"> </td>
                    <td>
                        <input style="margin-top: 5px; width: 300px" name="i" type="text" placeholder="Имя">
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align: middle">Oтчество</td>
                    <td style="width: 20px"> </td>
                    <td>
                        <input style="margin-top: 5px; width: 300px" name="o" type="text" placeholder="Отчество">
                    </td>
                </tr>
                <tr style="height: 20px"></tr>

                <tr>
                    <td><button type="submit" class="button is-primary">OK</button></td>
                    <td style="width: 20px"> </td>
                    <td><button type="submit" class="button is-danger"><a href="/students">Отмена</a></button></td>
                </tr>
            </table>

        </form>

    </div>
@endsection
