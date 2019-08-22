@extends('layouts.master')

@section('title')
    Дисциплина
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/disciplines">Список дисциплин</a></div>
    <div style="text-align: center">Дисциплина</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td>{{$discipline->name}}</td>

                <td>{{$discipline->groupName}}</td>

                <td>{{$discipline->auditorium_hours_per_week}}</td>

                @if(!empty($discipline->teacherFio))
                    <td>
                        {{$discipline->teacherFio}}
                        <form method="post" action="/teacherDisciplines/{{$discipline->discipline_teacher_id}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </form>
                    </td>
                @endif

                <td>{{\App\DomainClasses\Discipline::attestation_string($discipline->attestation)}}</td>

                <td><a href="/disciplines/{{$discipline->id}}/edit" class="button is-primary">Редактировать</a></td>

                <td>
                    <form method="POST" action="/disciplines/{{$discipline->id}}">
                        @csrf
                        @method('DELETE')
                        <button class="button is-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>

    @if(empty($discipline->teacherFio) && count($teachers) !== 0)
    <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
        <form method="post" action="/teacherDisciplines/store">
            @csrf
            <select name="teacher_id" style="margin-right: 2em;">
                @foreach($teachers as $teacher)
                    <option value="{{$teacher->id}}">{{$teacher->fio}}</option>
                @endforeach
            </select>

            <input type="hidden" name="discipline_id" value="{{$discipline->id}}">

            <button type="submit" class="button is-primary">Назначить</button>
        </form>
    </div>
    @endif

    @if(count($teachers) === 0)
        <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 0.5em; font-size: 2em;">
            Список преподавателей пуст
        </div>
    @endif
@endsection
