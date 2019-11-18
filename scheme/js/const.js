/* myMap = new ymaps.Map("map", {}); */
let myMap = null;
const x = 61.26;
const y = 55.03;
const xx = 61.54;
const yy = 55.29;
/* Объект с метками:
current выбранная для редактирования
new - добавляемая */
const placement = {
    current: {}, rollback: [],
    newMark: {}, type: 0,
};
/* Ссылка для перехода к редактированию РК */
const constructEdit = "/bitrix/admin/iblock_element_edit.php?"
    + "IBLOCK_ID=8&type=permit_list&lang=ru&find_section_section=6"
    + "&WF=Y&ID=";
/* коды иконок для видов РК
* "код иконки": идентификатор вида РК,
* */
const types = {
    "white-long-rectangle": 1,
    "six-rectangle": 2,
    "twice-rectangle": 4,
    "white-rectangle": 5,
    "black-rectangle": 6,
    "crocodile": 7,
    "white-cube": 8,
    "black-cube": 9,
    "white-circle": 10,
    "black-circle": 11,
    "white-circle-with-dot": 12,
    "white-triangle": 13,
    "black-triangle": 14,
    "flag": 15,
    "star": 16,
    "cross": 17,
    "V": 18,
    "arrow": 19,
};
const names = {
    "white-long-rectangle": "щитовая установка арочного типа",
    "six-rectangle": "транспарант-перетяжка на опорах",
    "twice-rectangle": "щитовая установка с большой площадью",
    "white-rectangle": "сити борд",
    "black-rectangle": "щитовая установка",
    "crocodile": "экран",
    "white-cube": "сити формат",
    "black-cube": "панель-кронштейн",
    "white-circle": "афишная тумба",
    "black-circle": "тумба",
    "white-circle-with-dot": "стела",
    "white-triangle": "афишный стенд",
    "black-triangle": "стенд",
    "flag": "флаги",
    "star": "скамья",
    "cross": "нестандартная рекламная конструкция",
    "V": "световой короб",
    "arrow": "указатель",
};
/* подписи для параметров РК */
const captions = {
    title: "Наименование рекламной конструкции",
    construct: "Вид рекламной конструкции",
    location: "Место расположения",
    remark: "Описательный адрес",
    x: "Географические координаты, долгота",
    y: "Географические координаты, широта",
    number_of_sides: "Количество сторон рекламной конструкции",
    construct_area: "Площадь рекламной конструкции",
    field_type: "Тип информационного поля",
    fields_number: "Количество полей рекламной конструкции",
    construct_height: "Размер информационного поля (высота)",
    construct_width: "Размер информационного поля (ширина)",
    fields_area: "Общая площадь информационных полей",
    lightening: "Наличие подсвета",
    permit_number: "Номер разрешения",
    permit_issuing_at: "Дата выдачи разрешения",
    permit_start: "Начало действия разрешения",
    permit_finish: "Окончание действия разрешения",
    permit_distributor: "Рекламораспространитель",
    permit_contract: "Реквизиты договора",
    number: "Порядковый номер в Схеме",
};
