@extends('layouts.master')

@section('title')
    Список экзаменов
@endsection

@section('content')
    <div style="text-align: center">Список экзаменов</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            @foreach($exams as $exam)
                <tr>
                    <td>
                        <a href="/exams/{{$exam->id}}">{{$exam->disciplineName}}</a>
                        @if(!empty($exam->teacherFio))
                            <br />
                            {{$exam->teacherFio}}
                        @endif
                    </td>

                    <td>{{$exam->student_group_name}}</td>

                    <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $exam->consultation_datetime)->format('d.m.Y H:i:s')}}</td>

                    <td>{{$exam->consultationAuditoriumName}}</td>

                    <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $exam->exam_datetime)->format('d.m.Y H:i:s')}}</td>

                    <td>{{$exam->examAuditoriumName}}</td>

                    <td><a href="/exams/{{$exam->id}}/edit" class="button is-primary">Редактировать</a></td>

                    <td>
                        <form method="POST" action="/exams/{{$exam->id}}">
                            @csrf
                            @method('DELETE')
                            <button class="button is-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    <div style="text-align: center">
        <a href="/exams/create" class="button is-primary">Добавить экзамен</a>
    </div>
@endsection
