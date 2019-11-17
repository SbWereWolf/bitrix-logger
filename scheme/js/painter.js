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
        //alert(side);
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
            $("#profile").html(content);
            //$("#tab-for-details").click();

        });
        return point;
    }
};