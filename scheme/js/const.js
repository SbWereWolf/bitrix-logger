/* myMap = new ymaps.Map("map", {}); */
let myMap = null;
/* массив с координатами иконок "В аренде" */
const inLease = [];
/* массив с координатами иконок "Свободно" */
const available = [];
/* Объект с метками:
current выбранная для редактирования
new - добавляемая */
const placement = {current: {}, rollback: [], new: {}};
/* Ссылка для перехода к редактированию РК */
const constructEdit = "/bitrix/admin/iblock_element_edit.php?"
    + "IBLOCK_ID=8&type=permit_list&lang=ru&find_section_section=6"
    + "&WF=Y&ID=";
/* коды иконок для видов РК
* "код иконки": идентификатор вида РК
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
/* подписи для параметров РК */
const captions = {
    place_title: "Наименование рекламной конструкции",
    place_construct: "Вид рекламной конструкции",
    place_location: "Место расположения",
    place_remark: "Описательный адрес",
    place_x: "Географические координаты, долгота",
    place_y: "Географические координаты, широта",
    place_number_of_sides: "Количество сторон рекламной конструкции",
    place_construct_area: "Площадь рекламной конструкции",
    place_field_type: "Тип информационного поля",
    place_fields_number: "Количество полей рекламной конструкции",
    place_construct_height: "Размер информационного поля (высота)",
    place_construct_width: "Размер информационного поля (ширина)",
    place_fields_area: "Общая площадь информационных полей",
    place_lightening: "Наличие подсвета",
    place_permit_number: "Номер разрешения",
    place_permit_issuing_at: "Дата выдачи разрешения",
    place_permit_start: "Начало действия разрешения",
    place_permit_finish: "Окончание действия разрешения",
    place_permit_distributor: "Рекламораспространитель",
    place_permit_contract: "Реквизиты договора",
    place_number: "Порядковый номер в Схеме",
};
