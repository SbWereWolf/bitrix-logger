const painter = {
    setProfileByIndex: function (index) {
        const place = points[index];
        let name = place.name.split('(')[0];
        name = name.charAt(0).toUpperCase() + name.slice(1);

        let info = {
            place_images: place.images,
            place_title: place.title ? place.title : name,
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
        if (place.permit) {
            const issuing_at = spreader.getDateString(place.permit.issuing_at);
            const start = spreader.getDateString(place.permit.start);
            const finish = spreader.getDateString(place.permit.finish);
            info['place_permit_number'] = place.permit.number;
            info['place_permit_issuing_at'] = issuing_at;
            info['place_permit_start'] = start;
            info['place_permit_finish'] = finish;
            info['place_permit_distributor'] = place.permit.distributor;
            info['place_permit_contract'] = place.permit.contract;
        }
        let image = "";
        info.place_images.forEach(function (val) {
            image += "<div class=\"row\"><div class=\"col-12\"><img src=\"" + val + "\" class=\"img-thumbnail\" alt=\"photo\"/></div></div>";
        });
        let address = info.place_location
            ? info.place_location : info.place_remark;
        let content =
            "<h4>" + info.place_title + "</h4>"
            + image
            + "<dl class=\"rk-profile\">"
            + "<dt>" + captions.place_location + "</dt>"
            + "<dd>" + address + "</dd>"
            + "<dt>" + captions.place_remark + "</dt>"
            + "<dd>" + info.place_remark + "</dd>"
            + "<dt>" + captions.place_x + "</dt>"
            + "<dd>" + info.place_x + "</dd>"
            + "<dt>" + captions.place_y + "</dt>"
            + "<dd>" + info.place_y + "</dd>"
            + '<dt>' + captions.place_number_of_sides + '</dt>'
            + '<dd>' + info.place_number_of_sides + '</dd>'
            + '<dt>' + captions.place_construct_area+ '</dt>'
            + '<dd>' + info.place_construct_area + '</dd>'
            + '<dt>' + captions.place_field_type+ '</dt>'
            + '<dd>' + info.place_field_type + '</dd>'
            + '<dt>' + captions.place_fields_number+ '</dt>'
            + '<dd>' + info.place_fields_number + '</dd>'
            + '<dt>' + captions.place_construct_height+ '</dt>'
            + '<dd>' + info.place_construct_height + '</dd>'
            + '<dt>' + captions.place_construct_width+ '</dt>'
            + '<dd>' + info.place_construct_width + '</dd>'
            + '<dt>' + captions.place_fields_area+ '</dt>'
            + '<dd>' + info.place_fields_area + '</dd>'
            + '<dt>' + captions.place_lightening+ '</dt>'
            + '<dd>' + info.place_lightening + '</dd>'
        ;

        if (typeof info.place_permit_number
            !== typeof undefined
        ) {
            content = content
                + "<dt>" + captions.place_permit_number + "</dt>"
                + "<dd>" + info.place_permit_number + "</dd>"
                + "<dt>" + captions.place_permit_issuing_at + "</dt>"
                + "<dd>" + info.place_permit_issuing_at+ "</dd>"
                + "<dt>" + captions.place_permit_start+ "</dt>"
                + "<dd>" + info.place_permit_start+ "</dd>"
                + "<dt>" + captions.place_permit_finish+ "</dt>"
                + "<dd>" + info.place_permit_finish+ "</dd>"
                + "<dt>" + captions.place_permit_distributor+ "</dt>"
                + "<dd>" + info.place_permit_distributor + "</dd>"
            ;
        }
        content = content + "</dl>";
        $("#profile").html(content);
        $('#profile-tab').tab('show');
    },
    setProfile: function (e) {
        landmark.changeCurrent(e);
    },
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
                iconImageHref: '/scheme/assets/rk_icons.png',
                iconImageSize: [side, side],
                iconImageOffset: [-0.5 * side, -1 * side]
            }
        );
        point.events.add('balloonclose', function () {
            $('#home-tab').tab('show');
        });

        point.events.add('balloonopen', this.setProfile);
        return point;
    },
    setPanorama: function (index) {
        const place = points[index];
        const cootdinates = [place.y, place.x];
        const panoramaModal = "#panoramaModal";
        $(panoramaModal).modal('show');
        let player;
        ymaps.panorama.locate(cootdinates).done(
            function (panoramas) {
                if (panoramas.length > 0) {
                    player = new ymaps.panorama.Player(
                        'rkPanorama',
                        panoramas[0],
                        {
                            controls: "panoramaName"
                        }
                    );
                }
                player.events.add('destroy', function () {
                    $(panoramaModal).modal('hide');
                });
            },
            function (error) {
                // Если что-то пошло не так, сообщим об этом пользователю.
                alert(error.message);
            }
        );
        $(panoramaModal).on('hidden.bs.modal', function () {
            if (player._engine) player.destroy();
        })
    }
};