<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>
<body class="height100">
<div class="container-fluid">
    <div class="row" style="background: darkorange;">
        <div class="col-1 header-icon">
            <img alt="logo" src="/scheme/assets/chelyabinsk-logo.png"
                 height="89px" width="79px"/>
        </div>
        <div class="col-5 header-text">
            <h6 style="padding-top: 3px;">
                Управление наружной рекламы и информации<br>
                Администрации города Челябинска
            </h6>
        </div>
        <div class="col-6">
        </div>
    </div>
</div>
<div class="container-fluid height100">
    <div class="row height80">
        <div class="col-4">
            <div class="tabs">
                <div class="tab">
                    <input type="radio" id="tab1" name="tab-group"
                           checked>
                    <label for="tab1" class="tab-title">Поиск
                    </label>
                    <section class="tab-content">
                        <form action="javascript:return false;"
                              id="search">
                            <div class="form-group">
                                <label for="address">Адрес</label>
                                <input type="search" id="address"
                                       name="address"
                                       class="form-control" autofocus
                                       placeholder=
                                       "Наименование или адрес объекта">
                                <button type="submit" id="run"
                                        style="margin-top: 10px;"
                                        class="btn btn-primary">Найти
                                </button>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="white-long-rectangle"
                                           type="checkbox">
                                    щитовая установка арочного типа
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="six-rectangle"
                                           type="checkbox">
                                    транспарант-перетяжка на
                                    собственных опорах
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="twice-rectangle"
                                           type="checkbox">
                                    щитовая установка с площадью
                                    информационного поля более 100 кв.м.
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="white-rectangle"
                                           type="checkbox">
                                    сити борд
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="black-rectangle"
                                           type="checkbox">
                                    щитовая установка
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="crocodile"
                                           type="checkbox">
                                    экран
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="white-cube"
                                           type="checkbox">
                                    сити формат
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="black-cube"
                                           type="checkbox">
                                    панель-кронштейн
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="white-circle"
                                           type="checkbox">
                                    афишная тумба
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="black-circle"
                                           type="checkbox">
                                    тумба
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="white-circle-with-dot"
                                           type="checkbox">
                                    стела
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="white-triangle"
                                           type="checkbox">
                                    афишный стенд
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="black-triangle"
                                           type="checkbox">
                                    стенд
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="flag"
                                           type="checkbox">
                                    флаги
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="star"
                                           type="checkbox">
                                    скамья с рекламной информацией
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="cross"
                                           type="checkbox">
                                    нестандартная рекламная конструкция
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="V"
                                           type="checkbox">
                                    световой короб
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input"
                                           name="arrow"
                                           type="checkbox">
                                    указатель
                                </label>
                            </div>
                        </form>
                    </section>
                </div>
                <div class="tab">
                    <input type="radio" id="tab2" name="tab-group">
                    <label for="tab2" class="tab-title">Найдено
                    </label>
                    <section class="tab-content">
                        <div>
                            Здесь будут перечислены результаты поиска
                        </div>
                    </section>
                </div>
                <div class="tab">
                    <input type="radio" id="tab-for-details"
                           name="tab-group">
                    <label for="tab-for-details" class="tab-title">
                        Подробно
                    </label>
                    <section class="tab-content">
                        <div id="detail">
                        </div>
                    </section>
                </div>
                <div class="tab">
                    <input type="radio" id="tab-for-commands"
                           name="tab-group">
                    <label for="tab-for-commands" class="tab-title">
                        Редактирование
                    </label>
                    <section class="tab-content">
                        <div>
                            <button class="btn btn-block btn-info">
                                Добавить
                            </button>
                            <button class="btn btn-block btn-primary">
                                Переместить
                            </button>
                            <button disabled
                                    class="btn btn-block btn-success">
                                Сохранить
                            </button>
                            <button disabled
                                    class="btn btn-block btn-danger">
                                Отменить
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <div id="map" class="col-8 padding5">
        </div>
    </div>
</div>
</body>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>

