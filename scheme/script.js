let myMap = null;
(function ($) {
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
            zoom: 17.0
        });
        const r = 111000;
        $.each(points.permit, function (index, permit) {
            const hasPlace = typeof permit.place !== typeof undefined;
            let hint = '';
            let preset = '';
            let color = '';
            if (!hasPlace) {
                color = 'yellow';
                preset = 'islands#icon';
                hint = `permit #${index} have no place`;
            }
            if (hasPlace) {
                color = 'green';
                preset = 'islands#dotIcon';
                hint = `permit #${index} have places: `;
                $.each(permit.place, function (index, place) {
                    hint = hint + `#${index}`
                        + ` d=${place.distance * r}m`
                        + ` r=${place.allowance * r * 1.44}m;`
                });
            }
            const point = new ymaps.GeoObject({
                    geometry: {
                        type: "Point",
                        coordinates: [permit['y'], permit['x']]
                    },
                    properties: {
                        iconContent: 'permit',
                        hintContent: hint,
                    }
                }, {preset: preset, iconColor: color}
            );

            myMap.geoObjects.add(point);
        });
        $.each(points.place, function (index, place) {
            const hasPlace = typeof place.permit !== typeof undefined;
            let hint = '';
            let preset = '';
            let color = '';
            if (!hasPlace) {
                color = 'red';
                preset = 'islands#icon';
                hint = `permit #${index} have no place`;
            }
            if (hasPlace) {
                color = 'blue';
                preset = 'islands#dotIcon';
                hint = `place #${index} have permits: `;
                $.each(place.permit, function (index, place) {
                    hint = hint + `#${index}`
                        + ` d=${place.distance * r}m`
                        + ` r=${place.allowance * r * 1.44}m;`
                });
            }
            const point = new ymaps.GeoObject({
                    geometry: {
                        type: "Point",
                        coordinates: [place['y'], place['x']]
                    },
                    properties: {
                        iconContent: 'place',
                        hintContent: hint,
                    }
                }, {preset: preset, iconColor: color}
            );

            myMap.geoObjects.add(point);
        });
    }

    ymaps.ready(init);


})(jQuery);
