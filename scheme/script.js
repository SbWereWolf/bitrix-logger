let myMap = null;
jQuery(function ($) {
    function init() {
        const x = 61.26;
        const y = 55.03;
        const xx = 61.54;
        const yy = 55.29;
        sCenter = [(yy - y) / 2 + y, (xx - x) / 2 + x];
        myMap = new ymaps.Map("map", {
            // Координаты центра карты: «широта, долгота».
            center: sCenter,
            // Уровень масштабирования: от 0 (весь мир) до 19.
            zoom: 16.0
        });
        const dy = 137;
        const dx = 140;
        let sx = 0;
        let sy = 0;
        let icons = [];
        icons[5] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //1
        sx += dx;
        icons[8] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //2
        sx += dx;
        icons[18] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //3
        sx += dx;
        icons[4] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //4
        sx += dx;
        icons[6] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //5
        sy += dy;
        sx = 0;
        icons[7] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //6
        sx += dx;
        icons[15] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //7
        sx += dx;
        icons[2] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //8
        sx += dx;
        icons[9] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //9
        sx += dx;
        icons[13] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //10
        sy += dy;
        sx = 0;
        icons[17] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //11
        sx += dx;
        icons[16] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //12
        sx += dx;
        icons[19] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //13
        sx += dx;
        icons[14] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //14
        sy += dy;
        sx = 0;
        icons[1] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //15
        sx += dx;
        icons[11] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //16
        sx += dx;
        icons[10] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //17
        sx += dx;
        icons[12] = [[Number(sx), Number(sy)],
            [Number(sx + dx), Number(sy + dy)]]; //18

        const cluster = new ymaps.Clusterer();
        $.each(points, function (index, place) {
            const hasPermit = typeof place.permit !== typeof undefined;
            let header = `РК #${index} (${place.type})`;
            let body = '';
            let footer = place.title;
            if (!hasPermit) {
                header = `${header} *`;
                body = 'Место для вашей рекламы';
            }

            if (hasPermit) {
                header = `${header} -`;

                function getDateString(unixTime) {
                    return (new Date(unixTime * 1000))
                        .toLocaleDateString("ru-RU");
                }

                $.each(place.permit, function (index, permit) {
                    const issuingAt = getDateString(permit.issuingAt);
                    const start = getDateString(permit.start);
                    const finish = getDateString(permit.finish);
                    body = body + `<li> Разрешние #${index} от ${issuingAt}`
                        + ` период действия с ${start} по ${finish}`
                        + ` место размещения: ${permit.address};</li>`
                });
                body = `<ul>${body}</ul>`;
            }
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
                    iconImageHref: '/scheme/icon-legend.jpg',
                    iconImageSize: [40, 40],
                    iconImageOffset: [-20, -20]
                }
            );

            cluster.add(point);
        });

        myMap.geoObjects.add(cluster);
    }

    ymaps.ready(init);
});
