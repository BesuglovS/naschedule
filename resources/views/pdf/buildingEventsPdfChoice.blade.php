@extends('layouts.master')

@section('title')
    Экспорт в PDF
@endsection

@section('content')
    <div style="text-align: center; font-size: 2em; font-weight: 700;">
        Занятость аудиторий корпуса <br />
        на один день в PDF
    </div>

    <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center; margin-top: 2em;">
        <form action="/buildingEventsPdf" method="get">
            <table style="font-size: 1.5em;">
                <tr style="padding-bottom: 1em;">
                    <td>Корпус</td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>
                        <select style="margin-right: 1em; font-size: 1.5em; width: 500px;" name="buildingId">
                            @foreach($buildings as $building)
                                <option value="{{$building['id']}}">{{$building['name']}}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                <tr>
                    <td>Неделя</td>
                    <td>&nbsp;</td>
                    <td>
                        <select style="margin-right: 1em; font-size: 1.5em; width: 500px;" name="dow" id="dow">
                            @for ($i = 1; $i <= 6 ; $i++)
                                <option value="{{$i}}" @if($i === $currentDow) selected @endif>{{$dowRu[$i-1]}}</option>
                            @endfor
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                <tr>
                    <td>Неделя</td>
                    <td>&nbsp;</td>
                    <td>
                        <select style="margin-right: 1em; font-size: 1.5em; width: 500px;" name="week" id="week">
                            @for ($i = 1; $i <= $weekCount ; $i++)
                                <option value="{{$i}}" @if($i === $currentWeek) selected @endif>{{$i}} ({{$weeks[$i]}})</option>
                            @endfor
                        </select>
                    </td>
                </tr>
            </table>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button type="submit" style="font-size: 2em;" class="button is-primary">Создать PDF-файл</button>
            </div>
        </form>
    </div>
@endsection
