const painter = {
    mark: function (place, index, header, body,
                    footer, details, iconSet, side) {
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
                iconImageHref: './assets/rk_icons.png',
                iconImageSize: [side, side],
                iconImageOffset: [-0.5 * side, -1 * side]
            }
        );
        point.events.add('click', function (e) {

            landmark.changeCurrent(e);

            const info = e.originalEvent.target.properties.get("info");
            let content = '';
            if (settings.type === settings.admin) {
                content = painter.forAdmin(info);
            }
            if (settings.type === settings.user) {
                content = painter.forUser(info);
            }

            $("#profile").html(content);
            $("#detail").html(content);

            if (settings.type === settings.user) {
                $("#tab-for-details").click();
            }
        });
        return point;
    },
    forAdmin: function (info) {
        let content =
            `<dl>`
            + `<dt>${captions.number}</dt>`
            + `<dd>${info.number}</dd>`
            + `<dt>${captions.title}</dt>`
            + `<dd>${info.title}</dd>`
            + `<dt>${captions.construct}</dt>`
            + `<dd>${info.construct}</dd>`
            + `<dt>${captions.location}</dt>`
            + `<dd>${info.location}</dd>`
            + `<dt>${captions.remark}</dt>`
            + `<dd>${info.remark}</dd>`
            + `<dt>${captions.x}</dt>`
            + `<dd>${info.x}</dd>`
            + `<dt>${captions.y}</dt>`
            + `<dd>${info.y}</dd>`
            + `<dt>${captions.number_of_sides}</dt>`
            + `<dd>${info.number_of_sides}</dd>`
            + `<dt>${captions.construct_area}</dt>`
            + `<dd>${info.construct_area}</dd>`
            + `<dt>${captions.field_type}</dt>`
            + `<dd>${info.field_type}</dd>`
            + `<dt>${captions.fields_number}</dt>`
            + `<dd>${info.fields_number}</dd>`
            + `<dt>${captions.construct_height}</dt>`
            + `<dd>${info.construct_height}</dd>`
            + `<dt>${captions.construct_width}</dt>`
            + `<dd>${info.construct_width}</dd>`
            + `<dt>${captions.fields_area}</dt>`
            + `<dd>${info.fields_area}</dd>`
            + `<dt>${captions.lightening}</dt>`
            + `<dd>${info.lightening}</dd>`
        ;

        if (typeof info.permit_number !== typeof undefined
        ) {
            content = content
                + `<dt>${captions.permit_number}</dt>`
                + `<dd>${info.permit_number}</dd>`
                + `<dt>${captions.permit_issuing_at}</dt>`
                + `<dd>${info.permit_issuing_at}</dd>`
                + `<dt>${captions.permit_start}</dt>`
                + `<dd>${info.permit_start}</dd>`
                + `<dt>${captions.permit_finish}</dt>`
                + `<dd>${info.permit_finish}</dd>`
                + `<dt>${captions.permit_distributor}</dt>`
                + `<dd>${info.permit_distributor}</dd>`
                + `<dt>${captions.permit_contract}</dt>`
                + `<dd>${info.permit_contract}</dd>`
            ;
        }
        content = `${content}</dl>`;

        return content;
    },
    forUser: function (info) {
        let content =
            `<dl>`
            + `<dt>${captions.number}</dt>`
            + `<dd>${info.number}</dd>`
            + `<dt>${captions.title}</dt>`
            + `<dd>${info.title}</dd>`
            + `<dt>${captions.construct}</dt>`
            + `<dd>${info.construct}</dd>`
            + `<dt>${captions.location}</dt>`
            + `<dd>${info.location}</dd>`
            + `<dt>${captions.remark}</dt>`
            + `<dd>${info.remark}</dd>`
            + `<dt>${captions.x}</dt>`
            + `<dd>${info.x}</dd>`
            + `<dt>${captions.y}</dt>`
            + `<dd>${info.y}</dd>`
            + `<dt>${captions.number_of_sides}</dt>`
            + `<dd>${info.number_of_sides}</dd>`
            + `<dt>${captions.construct_area}</dt>`
            + `<dd>${info.construct_area}</dd>`
            + `<dt>${captions.field_type}</dt>`
            + `<dd>${info.field_type}</dd>`
            + `<dt>${captions.fields_number}</dt>`
            + `<dd>${info.fields_number}</dd>`
            + `<dt>${captions.construct_height}</dt>`
            + `<dd>${info.construct_height}</dd>`
            + `<dt>${captions.construct_width}</dt>`
            + `<dd>${info.construct_width}</dd>`
            + `<dt>${captions.fields_area}</dt>`
            + `<dd>${info.fields_area}</dd>`
            + `<dt>${captions.lightening}</dt>`
            + `<dd>${info.lightening}</dd>`
        ;

        if (typeof info.permit_number !== typeof undefined
        ) {
            content = content
                + `<dt>${captions.permit_finish}</dt>`
                + `<dd>${info.permit_finish}</dd>`
                + `<dt>${captions.permit_distributor}</dt>`
                + `<dd>${info.permit_distributor}</dd>`
            ;
        }
        content = `${content}</dl>`;

        return content;
    }
};