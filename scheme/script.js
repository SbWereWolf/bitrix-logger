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
const icons = [];

function adjustCluster(conditions = {types: [], address: ""}) {
    const doSelecting = conditions.types.length !== 0;
    const cluster = new ymaps.Clusterer();
    $.each(points, function (index, place) {

        const allow = !doSelecting
            || conditions.types.includes(place.type);

        const hasPermit = typeof place.permit !== typeof undefined;

        let header = "";
        let body = "";
        let footer = "";
        if (allow) {
            header = `РК №${index} (${place.type})`;
            body =
                `GPS координаты: долгота <b>${place['x']}</b><br>`
                + `GPS координаты: широта <b>${place['y']}</b>`;
            body = `<p>${body}</p>`;
            footer = place.title;
        }
        if (allow && !hasPermit) {
            header = `${header} #`;
        }
        if (allow && hasPermit) {
            header = `${header} +`;
            body = `${body}<p><ul>`;

            function getDateString(unixTime) {
                return (new Date(unixTime * 1000))
                    .toLocaleDateString("ru-RU");
            }

            $.each(place.permit, function (index, permit) {
                const issuingAt = getDateString(permit.issuingAt);
                const start = getDateString(permit.start);
                const finish = getDateString(permit.finish);
                body = body
                    + `<li>`
                    + `Разрешение <b>№${index} от ${issuingAt}</b><br>`
                    + `Период действия с <b>${start}</b>`
                    + ` по <b>${finish}</b><br>`
                    + `Место размещения: <b>${permit.address}</b><br>`
                    + `</li>`
            });
            body = `${body}</ul></p>`;
        }
        if (allow) {
            const point = new ymaps.Placemark(
                [place['y'], place['x']],
                {
                    iconCaption: index,
                    balloonContentHeader: header,
                    balloonContentBody: body,
                    balloonContentFooter: footer
                },
                {
                    iconLayout: 'default#image',
                    iconImageClipRect: icons[place.type],
                    iconImageHref: '/scheme/assets/icon-legend.jpg',
                    iconImageSize: [40, 40],
                    iconImageOffset: [-20, -20]
                }
            );

            cluster.add(point);
        }
    });

    myMap.geoObjects.add(cluster);
}

jQuery(function ($) {

    function composeIcons() {
        const dy = 137;
        const dx = 140;
        let sx = 0;
        let sy = 0;
        icons[types["white-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //1
        sx += dx;
        icons[types["white-cube"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //2
        sx += dx;
        icons[types["V"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //3
        sx += dx;
        icons[types["twice-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //4
        sx += dx;
        icons[types["black-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //5
        sy += dy;
        sx = 0;
        icons[types["crocodile"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //6
        sx += dx;
        icons[types["flag"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //7
        sx += dx;
        icons[types["six-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //8
        sx += dx;
        icons[types["black-cube"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //9
        sx += dx;
        icons[types["white-triangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //10
        sy += dy;
        sx = 0;
        icons[types["cross"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //11
        sx += dx;
        icons[types["star"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //12
        sx += dx;
        icons[types["arrow"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //13
        sx += dx;
        icons[types["black-triangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //14
        sy += dy;
        sx = 0;
        icons[types["white-long-rectangle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //15
        sx += dx;
        icons[types["black-circle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //16
        sx += dx;
        icons[types["white-circle"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //17
        sx += dx;
        icons[types["white-circle-with-dot"]] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //18
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
