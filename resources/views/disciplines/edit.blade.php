@extends('layouts.master')

@section('title')
    Редактирование дисциплины
@endsection

@section('content')
    <div class="container alert alert-info alert-block"><a href="/disciplines">Список дисциплин</a></div>
    <div style="text-align: center">Редактирование дисциплины</div>
    <div class="container" style="align-items: center; display: flex; justify-content: center;">
        <form action="/disciplines/{{$discipline->id}}" method="POST">
            @csrf
            @method('patch')

            <input style="margin-top: 5px; width: 300px" name="name" type="text" value="{{$discipline->name}}">

            <select name="student_group_id" id="sg">
                @foreach($studentGroups as $studentGroup)
                    <option value="{{$studentGroup->id}}" @if($studentGroup->id == $discipline->student_group_id) selected @endif >{{$studentGroup->name}}</option>
                @endforeach
            </select>

            <select name="attestation">
                @foreach(\App\DomainClasses\Discipline::all_attestation() as $attestation)
                    <option value="{{$attestation->id}}" @if($attestation->id == $discipline->attestation) selected @endif >{{$attestation->name}}</option>
                @endforeach
            </select>

            <button type="submit" class="button is-primary">OK</button>
        </form>
    </div>
@endsection
