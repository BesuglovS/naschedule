@extends('layouts.masterNoAuth')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card-deck mb-2">
                    <div class="card tac">
                        <div class="card-body">
                            <div class="card-body">
                                <a href="http://wiki.nayanova.edu/md/">Сайт расписания</a>
                            </div>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <div class="card-body">
                                <a href="/teacherPdf">
                                    Расписание преподавателя на неделю в PDF
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <div class="card-body">
                                <a href="/admin">Изменение расписания</a>
                            </div>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <div class="card-body">
                                <a href="/DistanceLearning">Дистанционное обучение</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
