@extends('layouts.master')

@section('title')
    Экспорт в PDF
@endsection

@section('content')
    <div style="text-align: center; font-size: 2em; font-weight: 700;">
        Экспорт расписания класса <br />
        на одну конкретную неделю в PDF
    </div>

    <div class="container" style="display: flex; flex-direction: row;">
        <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center; margin-top: 2em;">
            <form action="/print-group-pdf" method="get">
                <table style="font-size: 1.5em;">
                    <tr style="padding-bottom: 1em;">
                        <td>Класс</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="groupId">
                                @foreach($studentGroups as $studentGroup)
                                    <option value="{{$studentGroup->id}}">{{$studentGroup->name}}</option>
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
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="week" id="week">
                                @for ($i = 1; $i <= $weekCount ; $i++)
                                    <option value="{{$i}}" @if($i === $currentWeek) selected @endif>{{$i}} ({{$weeks[$i]}})</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                </table>

                <table style="width:100%; margin-top: 1.5em;">
                    <tr style="text-align: center;">
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="bud" class="custom-control-input" id="customSwitch1" checked>
                                <label class="custom-control-label" for="customSwitch1">Бюджет</label>
                            </div>
                        </td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="vne" class="custom-control-input" id="customSwitch2">
                                <label class="custom-control-label" for="customSwitch2">Внеурочка</label>
                            </div>
                        </td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="pla" class="custom-control-input" id="customSwitch3">
                                <label class="custom-control-label" for="customSwitch3">Платные</label>
                            </div>
                        </td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="ele" class="custom-control-input" id="customSwitch4">
                                <label class="custom-control-label" for="customSwitch4">Электив</label>
                            </div>
                        </td>
                    </tr>
                </table>

                <div style="width: 100%; text-align: center;margin-top: 1em;">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="signature" class="custom-control-input" id="customSwitch4">
                        <label class="custom-control-label" for="customSwitch4">Подпись директора</label>
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="exportTimestamp" class="custom-control-input" id="customSwitch5" checked>
                        <label class="custom-control-label" for="customSwitch5">Добавлять время экспорта</label>
                    </div>
                </div>

                <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                    <button type="submit" style="font-size: 2em;" class="button is-primary">Создать PDF-файл</button>
                </div>
            </form>
        </div>

        <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center; margin-top: 2em;">
            <form action="/print-group-pdf-three" method="get">
                <table style="font-size: 1.5em;">
                    <tr style="padding-bottom: 1em;">
                        <td>Класс</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="groupId">
                                @foreach($studentGroups as $studentGroup)
                                    <option value="{{$studentGroup->id}}">{{$studentGroup->name}}</option>
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
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="week" id="week">
                                @for ($i = 1; $i <= $weekCount ; $i++)
                                    <option value="{{$i}}" @if($i === $currentWeek) selected @endif>{{$i}} ({{$weeks[$i]}})</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                </table>

                <div style="width: 100%; text-align: center;margin-top: 1em;">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="signature" class="custom-control-input" id="customSwitch44">
                        <label class="custom-control-label" for="customSwitch44">Подпись директора</label>
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="exportTimestamp" class="custom-control-input" id="customSwitch55" checked>
                        <label class="custom-control-label" for="customSwitch55">Добавлять время экспорта</label>
                    </div>
                </div>

                <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                    <button type="submit" style="font-size: 2em;" class="button is-primary">Создать PDF-файл (все 4 типа)</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center; margin-top: 2em;">
        <form action="/print-faculty-pdf-three" method="get">
            <table style="font-size: 1.5em;">
                <tr style="padding-bottom: 1em;">
                    <td>Параллель</td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>
                        <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="facultyId" id="facultyId">
                            @foreach($faculties as $faculty)
                                <option value="{{$faculty->id}}">{{$faculty->name}}</option>
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
                        <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="week" id="week">
                            @for ($i = 1; $i <= $weekCount ; $i++)
                                <option value="{{$i}}" @if($i === $currentWeek) selected @endif>{{$i}} ({{$weeks[$i]}})</option>
                            @endfor
                        </select>
                    </td>
                </tr>
            </table>

            <div style="width: 100%; text-align: center;margin-top: 1em;">
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="signature" class="custom-control-input" id="customSwitch444">
                    <label class="custom-control-label" for="customSwitch444">Подпись директора</label>
                </div>

                <div class="custom-control custom-switch">
                    <input type="checkbox" name="exportTimestamp" class="custom-control-input" id="customSwitch555" checked>
                    <label class="custom-control-label" for="customSwitch555">Добавлять время экспорта</label>
                </div>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button type="submit" style="font-size: 2em;" class="button is-primary">Создать PDF-файл (все 4 типа)</button>
            </div>
        </form>
    </div>
@endsection
