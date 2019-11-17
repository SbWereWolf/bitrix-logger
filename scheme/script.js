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
/* myMap = new ymaps.Map("map", {}); */
let myMap = null;
const inLease = [];
const available = [];

const constructEdit = "/bitrix/admin/iblock_element_edit.php?"
    + "IBLOCK_ID=8&type=permit_list&lang=ru&find_section_section=6"
    + "&WF=Y&ID=";

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
    place_construct_height: "Размер информационного пола (высота)",
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

function composePanorama(x, y) {
    return `http://yandex.ru/maps/?from=api-maps`
        + `&ll=${x}%2C${y}&panorama%5Bpoint%5D=${x}%2C${y}`;
}

function getDateString(unixTime) {
    return (new Date(unixTime * 1000))
        .toLocaleDateString("ru-RU");
}

function adjustCluster(conditions = {types: [], address: ""}) {
    const doSelecting = conditions.types.length !== 0;
    const cluster = new ymaps.Clusterer();
    $.each(points, function (index, place) {

        const allow = !doSelecting
            || conditions.types.includes(place.construct);

        const hasPermit = typeof place.permit !== typeof undefined;

        let header = "";
        let body = "";
        const panorama = composePanorama(place.x, place.y);

        let footer = "";
        if (allow) {
            header = `place.name №${index}`;
            body = `<p><ul><li>`
                + `Адрес: <b>${place.location}</b>`
                + `</li></ul></p>`
                + `<a class="btn btn-block btn-success"`
                + ` target="_blank"`
                + ` href="${panorama}">`
                + `Открыть панораму</a>`
                + `<a class="btn btn-block btn-primary"`
                + ` target="_blank"`
                + ` href="${constructEdit}${index}">`
                + `Редактировать</a>`;
            footer = place.name;
        }
        let iconSet = [];
        if (allow && !hasPermit) {
            iconSet = available;
        }
        if (allow && hasPermit) {
            iconSet = inLease;
        }
        let permitInfo = {};
        if (allow && typeof place.permit !== typeof undefined) {

            const issuing_at = getDateString(place.permit.issuing_at);
            const start = getDateString(place.permit.start);
            const finish = getDateString(place.permit.finish);

            permitInfo = {
                place_permit_number: place.permit.number,
                place_permit_issuing_at: issuing_at,
                place_permit_start: start,
                place_permit_finish: finish,
                place_permit_distributor: place.permit.distributor,
                place_permit_contract: place.permit.contract,
            };
        }
        let placeInfo = {};
        if (allow) {
            placeInfo = {
                place_title: place.title,
                place_construct: place.name,
                place_location: place.location,
                place_remark: place.remark,
                place_x: place.x,
                place_y: place.y,
                place_number_of_sides: place.number_of_sides,
                place_construct_area: place.construct_area,
                place_field_type: place.field_type,
                place_fields_number: place.fields_number,
                place_construct_height: place.construct_height,
                place_construct_width: place.construct_width,
                place_fields_area: place.fields_area,
                place_lightening: place.lightening,
                place_number: index,
            };
            const details = Object.assign(placeInfo, permitInfo);
            const point = new ymaps.Placemark(
                [place.y, place.x],
                {
                    iconCaption: index,
                    balloonContentHeader: header,
                    balloonContentBody: body,
                    balloonContentFooter: footer,
                    info: details
                },
                {
                    iconLayout: 'default#image',
                    iconImageClipRect: iconSet[place.construct],
                    iconImageHref: './assets/icons.webp',
                    iconImageSize: [50, 50],
                    iconImageOffset: [-20, -20]
                }
            );
            point.events.add('click', function (e) {

                const info = e.originalEvent.target.properties._data
                    .info;
                let content =
                    `<dl>`
                    + `<dt>${captions.place_number}</dt>`
                    + `<dd>${info.place_number}</dd>`
                    + `<dt>${captions.place_title}</dt>`
                    + `<dd>${info.place_title}</dd>`
                    + `<dt>${captions.place_construct}</dt>`
                    + `<dd>${info.place_construct}</dd>`
                    + `<dt>${captions.place_location}</dt>`
                    + `<dd>${info.place_location}</dd>`
                    + `<dt>${captions.place_remark}</dt>`
                    + `<dd>${info.place_remark}</dd>`
                    + `<dt>${captions.place_x}</dt>`
                    + `<dd>${info.place_x}</dd>`
                    + `<dt>${captions.place_y}</dt>`
                    + `<dd>${info.place_y}</dd>`
                    + `<dt>${captions.place_number_of_sides}</dt>`
                    + `<dd>${info.place_number_of_sides}</dd>`
                    + `<dt>${captions.place_construct_area}</dt>`
                    + `<dd>${info.place_construct_area}</dd>`
                    + `<dt>${captions.place_field_type}</dt>`
                    + `<dd>${info.place_field_type}</dd>`
                    + `<dt>${captions.place_fields_number}</dt>`
                    + `<dd>${info.place_fields_number}</dd>`
                    + `<dt>${captions.place_construct_height}</dt>`
                    + `<dd>${info.place_construct_height}</dd>`
                    + `<dt>${captions.place_construct_width}</dt>`
                    + `<dd>${info.place_construct_width}</dd>`
                    + `<dt>${captions.place_fields_area}</dt>`
                    + `<dd>${info.place_fields_area}</dd>`
                    + `<dt>${captions.place_lightening}</dt>`
                    + `<dd>${info.place_lightening}</dd>`
                ;

                if (typeof info.place_permit_number
                    !== typeof undefined
                ) {
                    content = content
                        + `<dt>${captions.place_permit_number}</dt>`
                        + `<dd>${info.place_permit_number}</dd>`
                        + `<dt>${captions.place_permit_issuing_at}</dt>`
                        + `<dd>${info.place_permit_issuing_at}</dd>`
                        + `<dt>${captions.place_permit_start}</dt>`
                        + `<dd>${info.place_permit_start}</dd>`
                        + `<dt>${captions.place_permit_finish}</dt>`
                        + `<dd>${info.place_permit_finish}</dd>`
                        + `<dt>${captions.place_permit_distributor}</dt>`
                        + `<dd>${info.place_permit_distributor}</dd>`
                        + `<dt>${captions.place_permit_contract}</dt>`
                        + `<dd>${info.place_permit_contract}</dd>`
                    ;
                }
                content = `${content}</dl>`;

                $("#detail").html(content);
                $("#tab-for-details").click();

            });

            cluster.add(point);
        }
    });

    myMap.geoObjects.add(cluster);
}

jQuery(function ($) {
    function composeIcons() {
        const dy = 138;
        const dx = 138;

        function defineIconsSet(icons, sx, sy) {
            const initialX = sx;
            icons[types["white-rectangle"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //1
            sx += dx;
            icons[types["white-cube"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //2
            sx += dx;
            icons[types["V"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //3
            sy += dy;
            sx = initialX;
            icons[types["twice-rectangle"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //4
            sx += dx;
            icons[types["black-rectangle"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //5
            sx += dx;
            icons[types["crocodile"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //6
            sy += dy;
            sx = initialX;
            icons[types["flag"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //7
            sx += dx;
            icons[types["six-rectangle"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //8
            sx += dx;
            icons[types["black-cube"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //9
            sy += dy;
            sx = initialX;
            icons[types["white-triangle"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //10
            sx += dx;
            icons[types["cross"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //11
            sx += dx;
            icons[types["star"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //12
            sy += dy;
            sx = initialX;
            icons[types["arrow"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //13
            sx += dx;
            icons[types["black-triangle"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //14
            sx += dx;
            icons[types["white-long-rectangle"]] =
                [[Number(sx), Number(sy)],
                    [Number(sx + dx), Number(sy + dy)]]; //15
            sy += dy;
            sx = initialX;
            icons[types["black-circle"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //16
            sx += dx;
            icons[types["white-circle"]] = [[Number(sx), Number(sy)],
                [Number(sx + dx), Number(sy + dy)]]; //17
            sx += dx;
            icons[types["white-circle-with-dot"]] =
                [[Number(sx), Number(sy)],
                    [Number(sx + dx), Number(sy + dy)]]; //18

            return icons;
        }

        defineIconsSet(inLease, 0, 0);
        defineIconsSet(available, dx * 3, 0);
    }

    composeIcons();

    const x = 61.26;
    const y = 55.03;
    const xx = 61.54;
    const yy = 55.29;

    function init() {

        function setupMap() {
            sCenter = [(yy - y) / 2 + y, (xx - x) / 2 + x];
            myMap = new ymaps.Map("map", {
                // Координаты центра карты: «широта, долгота».
                center: sCenter,
                // Уровень масштабирования: от 0 (весь мир) до 19.
                zoom: 16.0
            });
        }

        setupMap();
        adjustCluster();
    }

    ymaps.ready(init);

    function obtain() {

        function defineFilter() {
            const filter = {types: [], address: ""};
            const source = $("#search");
            const parameters = source.serializeArray();
            $.each(parameters, function (index, subject) {
                const isAddress = subject.name === "address";
                if (isAddress) {
                    filter.address = subject.value;
                }
                if (!isAddress) {
                    filter.types.push(types[subject.name]);
                }
            });

            return filter;
        }

        const conditions = defineFilter();

        myMap.geoObjects.removeAll();
        adjustCluster(conditions);

        const address = conditions.address;
        if (address !== "") {
            ymaps.geocode(address, {
                boundedBy: [[y, x], [yy, xx]],
                strictBounds: true,
                results: 1
            }).then(function (res) {
                const firstGeoObject = res.geoObjects.get(0),
                    coords = firstGeoObject.geometry.getCoordinates(),
                    bounds = firstGeoObject.properties.get('boundedBy');

                myMap.setCenter(coords);
                myMap.setBounds(bounds, {
                    checkZoomRange: true
                });
            });
        }
    }

    $("#run").on("click", obtain);
});
