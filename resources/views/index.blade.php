@extends('layouts.masterNoAuth')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card-deck mb-2">
                    <div class="card tac">
                        <div class="card-body">
                            <a href="http://wiki.nayanova.edu/md/">Сайт расписания</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="https://chrome.google.com/webstore/detail/%D1%80%D0%B0%D1%81%D0%BF%D0%B8%D1%81%D0%B0%D0%BD%D0%B8%D0%B5-%D0%B7%D0%B0%D0%BD%D1%8F%D1%82%D0%B8%D0%B9-%D1%81%D0%B3%D0%BE%D0%B0%D0%BD/koojfkionmllcclginfnncdijaoigckn?hl=ru">
                                Расширение Google Chrome для просмотра расписания
                            </a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="https://play.google.com/store/apps/details?id=ru.besuglovs.nu.timetable&hl=ru">Приложение Google Play для просмотра расписания</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/admin">Изменение расписания</a>
                        </div>
                    </div>

                </div>

                <div class="card-deck mb-2">
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/pdf-export">Расписание параллели на день определённой недели (PDF экспорт)</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/lleTeacher">
                                Изменения расписания преподавателя на конкретный день
                            </a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
