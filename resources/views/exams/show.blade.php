@extends('layouts.master')

@section('title')
    Экзамен
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/exams">Список экзаменов</a></div>
    <div style="text-align: center">Экзамен</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <table style="margin: 10px" class="table td-center is-bordered">
            <tr>
                <td>
                    <a href="/exams/{{$exam->id}}">{{$exam->disciplineName}}</a>

                        <br />
                        {{$exam->teacherFio}}

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
        </table>
    </div>

    <div class="container" style="align-items: center; flex-direction: column; display: flex; justify-content: center;">
        @foreach($examEvents as $examEvent)
            <div class="card" style="margin-bottom: 1em;">
                <div class="card-title" style="text-align: center; font-weight: bold">
                    Время изменения: {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $examEvent->datetime)->format('d.m.Y H:i:s')}}
                </div>
                <div class="card-body">
                    <table class="table td-center is-bordered">
                        <tr><td colspan="6">Старый вариант</td></tr>
                        <tr>
                            <td>{{$examEvent->old_exam->disciplineName}}</td>

                            <td>{{$examEvent->old_exam->student_group_name}}</td>

                            <td>
                                @if($examEvent->old_exam->consultation_datetime !== "2020-01-01 00:00:00")
                                    {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $examEvent->old_exam->consultation_datetime)->format('d.m.Y H:i:s')}}
                                @endif
                            </td>

                            <td>{{$examEvent->old_exam->consultationAuditoriumName}}</td>

                            <td>
                                @if($examEvent->old_exam->exam_datetime !== "2020-01-01 00:00:00")
                                    {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $examEvent->old_exam->exam_datetime)->format('d.m.Y H:i:s')}}
                                @endif
                            </td>

                            <td>{{$examEvent->old_exam->examAuditoriumName}}</td>
                        </tr>

                        <tr><td colspan="6">Новый вариант</td></tr>
                        <tr>
                            <td>{{$examEvent->new_exam->disciplineName}}</td>

                            <td>{{$examEvent->new_exam->student_group_name}}</td>

                            <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $examEvent->new_exam->consultation_datetime)->format('d.m.Y H:i:s')}}</td>

                            <td>{{$examEvent->new_exam->consultationAuditoriumName}}</td>

                            <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $examEvent->new_exam->exam_datetime)->format('d.m.Y H:i:s')}}</td>

                            <td>{{$examEvent->new_exam->examAuditoriumName}}</td>
                        </tr>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
