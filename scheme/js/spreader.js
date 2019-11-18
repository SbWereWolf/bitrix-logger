const spreader = {
    letClusterize: true,
    small: 30,
    big: 50,
    side: 50,
    compose: function (x, y) {
        return `http://yandex.ru/maps/?from=api-maps`
            + `&ll=${x}%2C${y}&panorama%5Bpoint%5D=${x}%2C${y}`;
    }, getDateString: function (unixTime) {
        return (new Date(unixTime * 1000))
            .toLocaleDateString("ru-RU");
    },
    place: function (conditions = {types: [], address: ""}) {
        const doSelecting = conditions.types.length !== 0;
        let cluster;
        if (spreader.letClusterize) {
            spreader.side = spreader.big;
            cluster = new ymaps.Clusterer();
        }

        if (!spreader.letClusterize) {
            spreader.side = spreader.small;
        }
        $.each(points, function (index, place) {
            const allow = !doSelecting
                || conditions.types.includes(place.construct);

            const hasPermit = typeof place.permit !== typeof undefined;

            let header = "";
            let body = "";
            const panorama = spreader.compose(place.x, place.y);

            let footer = "";
            let name = place.name.split('(')[0];
            if (allow) {
                header = `<div class="ballon-header">${name}, ${index}</div>`;
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
                iconSet = iconSetup.available;
            }
            if (allow && hasPermit) {
                iconSet = iconSetup.inLease;
            }
            let permitInfo = {};
            if (allow && typeof place.permit !== typeof undefined) {

                const issuing_at = spreader.getDateString(place.permit.issuing_at);
                const start = spreader.getDateString(place.permit.start);
                const finish = spreader.getDateString(place.permit.finish);

                permitInfo = {
                    permit_number: place.permit.number,
                    permit_issuing_at: issuing_at,
                    permit_start: start,
                    permit_finish: finish,
                    permit_distributor: place.permit.distributor,
                    permit_contract: place.permit.contract,
                };
            }
            let placeInfo = {};
            if (allow) {
                placeInfo = {
                    title: place.title,
                    construct: place.name,
                    location: place.location,
                    remark: place.remark,
                    x: place.x,
                    y: place.y,
                    number_of_sides: place.number_of_sides,
                    construct_area: place.construct_area,
                    field_type: place.field_type,
                    fields_number: place.fields_number,
                    construct_height: place.construct_height,
                    construct_width: place.construct_width,
                    fields_area: place.fields_area,
                    lightening: place.lightening,
                    number: index,
                };
                const details = Object.assign(placeInfo, permitInfo);
                const side = Number(spreader.side);
                const point = painter.mark(place, index, header, body,
                    footer, details, iconSet, side);

                if (spreader.letClusterize) {
                    cluster.add(point);
                }
                if (!spreader.letClusterize) {
                    myMap.geoObjects.add(point);
                }

            }
        });

        if (spreader.letClusterize) {
            myMap.geoObjects.add(cluster);
        }
    },
};