<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>
<body class="height100">
<div class="container-fluid">
    <div class="row" style="background: darkorange;">
        <div class="col-1">
            <img alt="logo" src="/scheme/assets/chelyabinsk-logo.png"
                 height="89px" width="79px"/>
        </div>
        <div class="col-5">
            <h6>Управление наружной рекламы и информации Администрации
                города Челябинска</h6>
        </div>
        <div class="col-6">
        </div>
    </div>
</div>
<div class="container-fluid height100">
    <div class="row height80">
        <div class="col-3">
            <h3>Поиск</h3>
            <form action="">
                <div class="form-group">
                    <label for="address">Адрес</label>
                    <input type="text" id="address" class="form-control"
                           placeholder="Наименование или адрес объекта">
                    <button type="submit" class="btn btn-primary">Найти
                    </button>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        щитовая установка арочного типа
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        транспарант-перетяжка на собственных опорах
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        щитовая установка с площадью информационного поля более 100 кв.м.
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        сити борд
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        щитовая установка
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        экран
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        сити формат
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        панель-кронштейн
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        афишная тумба
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        тумба
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        стела
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        афишный стенд
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        стенд
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        флаги
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        скамья с рекламной информацией
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        нестандартная рекламная конструкция
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        световой короб
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox">
                        указатель
                    </label>
                </div>
            </form>
        </div>
        <div id="map" class="col-9 padding5">
        </div>
    </div>
</div>
</body>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>

