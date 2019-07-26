@extends('layouts.master')

@section('title')
    Редактирование ученика
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/students">Список учеников</a></div>
    <div style="text-align: center">Редактирование ученика</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <form action="/students/{{$student->id}}" method="POST">
            @csrf
            @method('patch')

            <table>
                <tr>
                    <td style="vertical-align: middle">Фамилия</td>
                    <td style="width: 20px"> </td>
                    <td>
                        <input style="margin-top: 5px; width: 300px" name="f" type="text" value="{{$student->f}}">
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align: middle">Имя</td>
                    <td style="width: 20px"> </td>
                    <td>
                        <input style="margin-top: 5px; width: 300px" name="i" type="text" value="{{$student->i}}">
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align: middle">Oтчество</td>
                    <td style="width: 20px"> </td>
                    <td>
                        <input style="margin-top: 5px; width: 300px" name="o" type="text" value="{{$student->o}}">
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
