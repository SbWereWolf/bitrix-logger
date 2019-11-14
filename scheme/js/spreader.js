const spreader = {
    compose: function (x, y) {
        return `http://yandex.ru/maps/?from=api-maps`
            + `&ll=${x}%2C${y}&panorama%5Bpoint%5D=${x}%2C${y}`;
    }, getDateString: function (unixTime) {
        return (new Date(unixTime * 1000))
            .toLocaleDateString("ru-RU");
    },
    place: function (conditions = {types: [], address: ""}) {
        const doSelecting = conditions.types.length !== 0;
        const cluster = new ymaps.Clusterer();
        $.each(points, function (index, place) {

            const allow = !doSelecting
                || conditions.types.includes(place.construct);

            const hasPermit = typeof place.permit !== typeof undefined;

            let header = "";
            let body = "";
            const panorama = spreader.compose(place.x, place.y);

            let footer = "";
            if (allow) {
                header = `РК №${index} (${place.construct})`;
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
                        iconImageHref: '/scheme/assets/icons.webp',
                        iconImageSize: [50, 50],
                        iconImageOffset: [-20, -20]
                    }
                );
                point.events.add('click', function (e) {

                    landmark.changeCurrent(e);

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
    },
};