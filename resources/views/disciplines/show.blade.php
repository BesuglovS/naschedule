@extends('layouts.master')

@section('title')
    Дисциплина
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/disciplines?groupId={{$discipline->student_group_id}}">Список дисциплин</a></div>
    <div style="text-align: center">Дисциплина</div>
    <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center;">
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

                <td>
                    <input type="checkbox" style="transform: scale(2);" disabled name="active" id="activeDiscipline" @if($discipline->active) checked @endif />
                </td>

                <td>
                    @if($discipline->type == 1) Бюджет @endif
                    @if($discipline->type == 2) Внеурочные @endif
                    @if($discipline->type == 3) Платные @endif
                    @if($discipline->type == 4) Электив @endif
                </td>

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

        @if(empty($discipline->teacherFio) && count($teachers) !== 0)
            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
                <form method="post" action="/teacherDisciplines/store">
                    @csrf
                    <select  name="teacher_id" style="margin-right: 2em;">
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


        <div>
            <form method="post" action="/addDisciplinesAnotherGroup">
                @csrf
                @method('post')
                <button class="button is-primary" type="submit">
                    Скопировать в класс
                </button>

                <select style="margin-top: 6px;" name="student_group_id" id="sg">
                    @foreach($studentGroups as $studentGroup)
                        <option value="{{$studentGroup->id}}" @if($studentGroup->id == $discipline->student_group_id) selected @endif>{{$studentGroup->name}}</option>
                    @endforeach
                </select>

                <input type="hidden" name="discipline_id" value="{{$discipline->id}}">
            </form>
        </div>
    </div>

@endsection
