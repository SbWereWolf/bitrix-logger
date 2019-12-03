<?php
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $BITRIX_SM_UIDL, $BITRIX_SM_UIDH;
setcookie("api-login", $BITRIX_SM_UIDL, time() + 3600 * 9);
setcookie("api-hash", $BITRIX_SM_UIDH, time() + 3600 * 9);

/* @var $USER CUser */
global $USER;
$isAdmin = $USER->IsAdmin() ? 'block' : 'none';
?>
<div class="all">
    <div class="container-fluid header">
        <div class="row d-flex bd-highlight mb-3" style="">
            <div class="header-icon float-left">
                <img alt="Управление наружной рекламы и информации Администрации города Челябинска"
                     src="/scheme/assets/gerald.svg"
                     height="67px" width="53px"/>
            </div>
            <div class="header-text float-left">
                <h1 style="padding-top: 3px;">
                    Управление наружной рекламы и информации<br>
                    Администрации города Челябинска
                </h1>
            </div>
            <div class="header-text" style="position: absolute; right:30px; top:30px;">
                <h1 style="padding-top: 3px; padding-left: 100px" class="ch">
                    Черновик
                </h1>
            </div>
        </div>
    </div>
    <div class="container-fluid content">
        <div class="col-3 col-md-4 col-xl-3 align-self-stretch overflow-auto side-left ">
            <div class="tabs">
                <div class="tab">
                    <ul class="nav nav-tabs" id="leftTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#searchtab" role="tab"
                               aria-controls="searchtab" aria-selected="true">Поиск</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                               aria-controls="profile" aria-selected="false">Подробности</a>
                        </li>
                        <li>
                        <li class="nav-item">
                            <a class="nav-link" id="edit-tab" data-toggle="tab" href="#edit" role="tab"
                               aria-controls="edit" aria-selected="false">Редактировать</a>
                        </li>
                    </ul>
                    <section class="tab-content">
                        <div class="tab-pane fade show active" id="searchtab" role="tabpanel">
                            <form action="#" id="show">
                                <div class="form-group row search-form">
                                    <div class="col-9">
                                        <input type="search" id="address"
                                               name="address"
                                               class="form-control" autofocus
                                               placeholder=
                                               "Наименование или адрес объекта">
                                    </div>
                                    <div class="col-3">
                                        <button type="submit" id="searchBtn"
                                                style=""
                                                class="btn btn-primary clicktofind">Найти
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group row sectors-form">
                                    <div class="col-2">
                                        <label>Cектор:</label>
                                    </div>
                                    <div class="col-10 input-group mb-3">
                                        <select class="custom-select" name="sector-1" id="sector-1">
                                        </select>
                                        <select class="custom-select" name="sector-0" id="sector-0">
                                        </select>
                                        <input type="button" onclick="return false" id="goto-sector" value="Перейти"
                                               class="btn btn-warning"/>
                                    </div>
                                    <div class="col-3">
                                    </div>
                                </div>

                                <div class="form-group row rk-type rk-type-1">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9" for="type-1">щитовая установка арочного типа</label>
                                    <div class="col-1"><input class="clicktofind"
                                                              name="white-long-rectangle"
                                                              type="checkbox" id="type-1"></div>


                                </div>
                                <div class="form-group  row rk-type rk-type-2">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">транспарант-перетяжка на
                                        собственных опорах</label>
                                    <div class="col-1"><input class="clicktofind"
                                                              name="six-rectangle"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group row rk-type rk-type-3">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">щитовая установка с площадью
                                        информационного поля более 100 кв.м.</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="twice-rectangle"
                                                              type="checkbox"></div>

                                </div>
                                <div class="form-group row rk-type rk-type-4">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">сити борд</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="white-rectangle"
                                                              type="checkbox"></div>

                                </div>
                                <div class="form-group row  rk-type rk-type-5">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">щитовая установка</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="black-rectangle"
                                                              type="checkbox"></div>

                                </div>
                                <div class="form-group row rk-type rk-type-6">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">экран</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="crocodile"
                                                              type="checkbox"></div>

                                </div>
                                <div class="form-group row  rk-type rk-type-7">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">сити формат</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="white-cube"
                                                              type="checkbox"></div>

                                </div>
                                <div class="form-group row  rk-type rk-type-8">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">панель-кронштейн</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="black-cube"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group  row rk-type rk-type-9">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">афишная тумба</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="white-circle"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group row  rk-type rk-type-10">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">тумба</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="black-circle"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group  row rk-type rk-type-11">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">стела</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="white-circle-with-dot"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group row  rk-type rk-type-12">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">афишный стенд</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="white-triangle"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group  row rk-type rk-type-13">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">стенд</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="black-triangle"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group row  rk-type rk-type-14">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">флаги</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="flag"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group row  rk-type rk-type-15">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">скамья с рекламной информацией</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="star"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group  row rk-type rk-type-16">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">нестандартная рекламная конструкция</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="cross"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group row  rk-type rk-type-17">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">световой короб</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="V"
                                                              type="checkbox"></div>
                                </div>
                                <div class="form-group row  rk-type rk-type-18">
                                    <div class="col-2">
                                        <div class="icon"></div>
                                    </div>
                                    <label class="col-9">указатель</label>
                                    <div class="col-1"><input class=" clicktofind"
                                                              name="arrow"
                                                              type="checkbox"></div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="profile" role="tabpanel">
                        </div>
                        <div class="tab-pane" id="edit" role="tabpanel">
                            <div>
                                <div class="form-group rk-edit-control" id="rk-type">
                                    <label for="construct-types">
                                        Вид рекламной конструкции
                                    </label>
                                    <select class="form-control"
                                            id="construct-types">
                                    </select>
                                </div>
                                <button id="add-new"
                                        class="btn btn-primary rk-edit-control">
                                    Добавить
                                </button>
                                <div style="margin: 5px; display: none" class="rk-edit-control input-group mb-3"
                                     id="new-address-div">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <input type="checkbox" id="new-address-change" value="1" checked=""/>
                                        </div>
                                    </div>
                                    <input type="text" id="new-address" class="form-control" disabled="disabled"/>
                                </div>
                                <button id="accept" disabled
                                        class="btn btn-block btn-success rk-edit-control" style="display: none"
                                        >
                                    Сохранить
                                </button>
                                <button id="decline" disabled
                                        class="btn btn-block btn-danger rk-edit-control" style="display: none"
                                        >
                                    Отменить
                                </button>
                                <button id="publish" disabled
                                        class="btn  btn-warning rk-edit-control">
                                    Опубликовать
                                </button>
                                <button id="flush"
                                        class="btn  btn-danger rk-edit-control">
                                    Обновить
                                </button>
                                <hr>
                                <div style="display:<?= $isAdmin ?>">
                                    <button id="release"
                                            class="btn  btn-warning
                                            rk-edit-control">
                                        Опубликовать ВСЁ
                                    </button>
                                    <button id="recompile"
                                            class="btn  btn-danger
                                            rk-edit-control">
                                        Обновить опубликованные
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <div id="map" class="col-9 col-md-8 col-xl-9 align-self-stretch">
        </div>
    </div>
</div>
<div class="modal fade" id="panoramaModal" tabindex="-1" role="dialog" aria-labelledby="panoramaModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-body" id="panoramaBody">
                <div id="rkPanorama" style="height: 400px">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>

