@extends('layouts.master')

@section('title')
    Экспорт в PDF
@endsection

@section('content')
    <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center;">
        <table style="margin-top: 0.5em;" class="table td-center is-bordered">
            <tr>
                <td colspan="8" style="font-size: 2em; font-weight: bold;">
                    Скачать / показать расписание
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="font-weight:700;">Вся неделя</td>
                <td style="font-weight:700;">Понедельник</td>
                <td style="font-weight:700;">Вторник</td>
                <td style="font-weight:700;">Среда</td>
                <td style="font-weight:700;">Четверг</td>
                <td style="font-weight:700;">Пятница</td>
                <td style="font-weight:700;">Суббота</td>
            </tr>
            @foreach($faculties as $faculty)
                <tr>
                    <td style="vertical-align: middle;">{{$faculty->name}}</td>
                    <td style="padding: 0; vertical-align: middle;">
                        <form action="/bulkDownload-pdf" method="post">
                            @csrf
                            <table style="border: none;">
                                <tr>
                                    <td style="border: none;">
                                        <button type="submit" style="font-size: 1em;" class="button btn-sm is-primary">Скачать</button>
                                    </td>
                                    <td style="border: none; vertical-align: middle;">
                                        <select @change="" style="margin-right: 1em; font-size: 1em; width: 40px;" name="week" id="week2">
                                            @for ($j = 1; $j <= $weekCount ; $j++)
                                                <option value="{{$j}}" @if($j === $currentWeek) selected @endif>{{$j}}</option>
                                            @endfor
                                        </select>
                                    </td>
                                </tr>
                            </table>

                            <input type="hidden" name="facultyId" value="{{$faculty->id}}">
                            <input type="hidden" name="download" value="true">
                        </form>

                        <form action="/bulkDownload-pdf" method="post">
                            @csrf
                            <table style="border: none;">
                                <tr>
                                    <td style="border: none;">
                                        <button type="submit" style="font-size: 1em;" class="button btn-sm is-primary">Показать</button>
                                    </td>
                                    <td style="border: none; vertical-align: middle;">
                                        <select @change="" style="margin-right: 1em; font-size: 1em; width: 40px;" name="week" id="week2">
                                            @for ($j = 1; $j <= $weekCount ; $j++)
                                                <option value="{{$j}}" @if($j === $currentWeek) selected @endif>{{$j}}</option>
                                            @endfor
                                        </select>
                                    </td>
                                </tr>
                            </table>

                            <input type="hidden" name="facultyId" value="{{$faculty->id}}">
                            <input type="hidden" name="download" value="false">
                        </form>
                    </td>
                    @for ($i = 1; $i <= 6; $i++)
                        <td style="padding: 0;">
                            <form action="/download-pdf" method="post">
                                @csrf
                                <table style="border: none;">
                                    <tr>
                                        <td style="border: none;">
                                            <button type="submit" style="font-size: 1em;" class="button btn-sm is-primary">Скачать</button>
                                        </td>
                                        <td style="border: none; vertical-align: middle;">
                                            <select @change="" style="margin-right: 1em; font-size: 1em; width: 40px;" name="week" id="week2">
                                                @for ($j = 1; $j <= $weekCount ; $j++)
                                                    <option value="{{$j}}" @if($j === $currentWeek) selected @endif>{{$j}}</option>
                                                @endfor
                                            </select>
                                        </td>
                                    </tr>
                                </table>

                                <input type="hidden" name="facultyId" value="{{$faculty->id}}">
                                <input type="hidden" name="dow" value="{{$i}}">
                            </form>

                            <form action="/print-pdf" method="get">
                                @csrf
                                <table style="border: none;">
                                    <tr>
                                        <td style="border: none;">
                                            <button type="submit" style="font-size: 1em;" class="button btn-sm is-primary">Показать</button>
                                        </td>
                                        <td style="border: none; vertical-align: middle;">
                                            <select @change="" style="margin-right: 1em; font-size: 1em; width: 40px;" name="week" id="week2">
                                                @for ($j = 1; $j <= $weekCount ; $j++)
                                                    <option value="{{$j}}" @if($j === $currentWeek) selected @endif>{{$j}}</option>
                                                @endfor
                                            </select>
                                        </td>
                                    </tr>
                                </table>

                                <input type="hidden" name="facultyId" value="{{$faculty->id}}">
                                <input type="hidden" name="dow" value="{{$i}}">
                            </form>
                        </td>
                    @endfor
            @endforeach
            <tr>
                <td colspan="2" style="font-size: 1.6em; vertical-align: middle; font-weight:700;">Все параллели</td>
                @for ($i = 1; $i <= 6; $i++)
                    <td style="padding: 0;">
                        <div>
                            <form action="/bulkDowDownload-pdf" method="post">
                                @csrf
                                <table style="border: none;">
                                    <tr>
                                        <td style="border: none;">
                                            <button type="submit" style="font-size: 1em;" class="button btn-sm is-primary">Скачать</button>
                                        </td>
                                        <td style="border: none; vertical-align: middle;">
                                            <select @change="" style="margin-right: 1em; font-size: 1em; width: 40px;" name="week" id="week3">
                                                @for ($j = 1; $j <= $weekCount; $j++)
                                                    <option value="{{$j}}" @if($j === $currentWeek) selected @endif>{{$j}}</option>
                                                @endfor
                                            </select>
                                        </td>
                                    </tr>
                                </table>

                                <input type="hidden" name="dow" value="{{$i}}">
                            </form>
                        </div>

                        <div>
                            <form action="/bulkDowShow-pdf" method="post">
                                @csrf
                                <table style="border: none;">
                                    <tr>
                                        <td style="border: none;">
                                            <button type="submit" style="font-size: 1em;" class="button btn-sm is-primary">Показать</button>
                                        </td>
                                        <td style="border: none; vertical-align: middle;">
                                            <select @change="" style="margin-right: 1em; font-size: 1em; width: 40px;" name="week" id="week3">
                                                @for ($j = 1; $j <= $weekCount; $j++)
                                                    <option value="{{$j}}" @if($j === $currentWeek) selected @endif>{{$j}}</option>
                                                @endfor
                                            </select>
                                        </td>
                                    </tr>
                                </table>

                                <input type="hidden" name="dow" value="{{$i}}">
                            </form>
                        </div>
                    </td>
                @endfor
            </tr>
            </tr>
        </table>
    </div>
@endsection
