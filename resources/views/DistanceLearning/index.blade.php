@extends('layouts.master')

@section('title')
    Дистанционное обучение
@endsection

@section('content')
    <div style="text-align: center; font-size: 4em;">Дистанционное обучение</div>

    <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center; margin-bottom: 2em;">
        <ol>
            <li>
                <a href="http://nayanova.edu/documents/distant2020/1-Prepare.pdf">
                    Организационные вопросы по подготовке к онлайн-трансляциям
                </a>
            </li>
            <li>
                <a href="http://nayanova.edu/documents/distant2020/2-Zoom-reg.pdf">
                    Регистрация на платформе Zoom
                </a>
            </li>
            <li>
                <a href="http://nayanova.edu/documents/distant2020/3-Zoom-start.pdf">
                    Первый запуск и тестирование трансляции Zoom (без участников)
                </a>
            </li>
			<li>
                <a href="http://nayanova.edu/documents/distant2020/4-Zoom-tutorial.pdf">
                    Инструкция для проведения трансляции Zoom с участниками
                </a>
            </li>
            <li>
                <a href="https://testmoz.com/build">
                    Сервис для создания тестов 1 (testmoz.com)
                </a>
            </li>
            <li>
                <a href="https://onlinetestpad.com/">
                    Сервис для создания тестов 2 (onlinetestpad.com)
                </a>
            </li>
            <li>
                <a href="http://nayanova.edu/documents/distant2020/OPD-distant.pdf">
                    <strong>ВАЖНО!!!</strong> Памятка по персональным данным.
                </a>
            </li>
            <li>
                <a href="http://nayanova.edu/documents/distant2020/Calendar.pdf">
                    Инструкция по календарю Trello.com
                </a>
            </li>
            <li>
                <a href="http://nayanova.edu/documents/distant2020/5-Zoom-secure.pdf">
                    Настройки безопасности Zoom
                </a>
            </li>
        </ol>
    </div>

    <div style="text-align: center; font-size: 2em; margin-top: 2em; margin-bottom: 2em;">
        Здесь будут размещены дополнительные материалы по организации дистанционного обучения. <br />
        Следите за обновлениями.
    </div>

    <div style="text-align: center; height: 650px;">
        <iframe width="560"
                height="315"
                style="width: 100%; height: 100%;"
                src="https://www.youtube.com/embed/mpDItmFpl_M"
                frameborder="0"
                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
        </iframe>
    </div>
@endsection
