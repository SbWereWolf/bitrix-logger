let Places = [];
const spreader = {
    letClusterize: true,
    small: 20,
    big: 30,
    side: 20,
    compose: function (x, y) {
        return 'http://yandex.ru/maps/?from=api-maps'
            + '&ll=' + x + '%2C' + y + '&panorama%5Bpoint%5D=' + x + '%2C' + y;
    }, getDateString: function (unixTime) {
        return (new Date(unixTime * 1000))
            .toLocaleDateString("ru-RU");
    },
    moveByIndex: function(index) {
        myMap.geoObjects.removeAll();
        const place  = points[index];
        const hasPermit = typeof place.permit !== typeof undefined;
        if (!hasPermit) {
            iconSet = iconSetup.available;
        }
        if (hasPermit) {
            iconSet = iconSetup.inLease;
        }
        let point = painter.mark(place, index, "", "",
            "", false, iconSet, this.side);
        placement.current = point;
        point.properties.set('info', {number: index});
        myMap.geoObjects.add(point);
        const newAddress = $('#new-address');
        newAddress.val("");
        if (place.address) newAddress.val(place.address);
        point.events.add('dragend', function () {
            landmark.action = landmark.move;
            const coord = point.geometry.getCoordinates();
            var myGeocoder = ymaps.geocode(coord, {kind: 'house' });
            myGeocoder.then (
                function(res) {
                    var street = res.geoObjects.get(0);
                    var name = street.properties.get('name');
                    // Будет выведено «улица Большая Молчановка»,
                    // несмотря на то, что обратно геокодируются
                    // координаты дома 10 на ул. Новый Арбат.
                    if(name) {
                        $('#new-address').val(name);
                    }
                }
            );
            $('#accept')[0].disabled = false;
        });
        $('#edit-tab').tab('show');
        $('.rk-edit-control').hide();
        $('#accept').show();
        $('#new-address-div').show();
        const decline = $('#decline');
        decline.show();
        decline[0].disabled = false;
        decline.off('click');
        decline.one('click', function () {
            if(!this.disabled) {
                myMap.geoObjects.remove(point);
                //point.geometry.setCoordinates([place.y, place.x]);
                this.disabled = true;
                $('.rk-edit-control').hide();
                $('#rk-type').show();
                $('#add-new').show();
                $('#publish').show();
                $('#flush').show();
                $('#address').val("");
                landmarkFilter.run();
                placement.current = {};
            }
        })



    },
    place: function (conditions) {
        Places = [];
        if(!conditions) conditions = conditions = {types: [], address: "" };
        const doSelecting = conditions.types.length !== 0;
        let cluster;
        if (spreader.letClusterize) {
            spreader.side = spreader.big;
            cluster = new ymaps.Clusterer({maxZoom:17});
            cluster.balloon.events.add('open', function(e) {
            })
        }
        if (!spreader.letClusterize) {
            spreader.side = spreader.small;
        }
        $.each(points, function (index, place) {
            const allow = !doSelecting
                || (conditions.types.indexOf(place.construct) != -1);

            const hasPermit = typeof place.permit !== typeof undefined;

            let header = "";
            let body = "";

            let footer = "";
            if(typeof place.name != "undefined") place.name += "";
            else {
                place.name = "";
            }
            let name = place.name.split('(')[0];
            name = name.charAt(0).toUpperCase() + name.slice(1);
            let image = "";
            if(typeof place.images !== 'undefined' && place.images[0]) {
                image = place.images[0];
            } else {
                place.images = ["/scheme/assets/layout.png"];
                image = "/scheme/assets/layout.png";
            }
            if (allow) {
                header = '<div class="ballon-header">' + name+ ', ' + index+ '</div>';
                body = '<div class="ballon-content row" style="margin-right:-30px; margin-left:0">'
                    + '<div class="col-6 image" style="padding-right:0;">'
                    + '<img src="' + image + '" alt="construct photo" ' +
                    'class="img-thumbnail rounded-0 "/>'
                    + '</div><div class="col-6">'
                    + '<div class="rowdiv"><span class="address-label">Адрес:</span><br /> <strong>' + place.location+ '</strong></div>'
                    + '<div class="rowdiv"><span class="address-label">Категория:</span><br /> <strong>' + place.name+ '</strong></div></div></div>';
                    footer = ''
                        + '<div class="row" style="margin-right:-15px; margin-left:0""><div class="col-3" style="padding-right:0;">'
                    + '<a class="btn btn-outline-primary btn-sm btn-block"'
                    + ' href="#" onclick="painter.setPanorama(' + index+ '); return false;">'
                        + 'Панорама</a></div><div class="col-3" style="margin-left:0;padding-right:0;">'
                    + '<a class="btn btn-primary btn-sm btn-block" '
                    + ' target="_blank"'
                    + ' href="#" onclick="painter.setProfileByIndex(' + index+ ');return false">'
                    + 'Подробно</a></div>' +
                        '<div class="col-3" style="margin-left:0;padding-right:0;">' +
                        '<a class="btn btn-primary btn-sm btn-block" ' +
                        ' target="_blank" href="' + constructEdit + index + '">' +
                        'Редактoр</a></div>' +
                        '<div class="col-3" style="margin-left:0;">' +
                        '<a class="btn btn-primary btn-sm btn-block" ' +
                        ' target="_blank" href="#" onclick="spreader.moveByIndex(' + index+ ');return false">' +
                        'Двигать</a></div>' +
                        '</div>';

            }
            let iconSet = [];
            if (allow && !hasPermit) {
                iconSet = iconSetup.available;
            }
            if (allow && hasPermit) {
                iconSet = iconSetup.inLease;
            }

            let placeInfo = {};
            if (allow) {
                placeInfo = {
                    place_images:place.images,
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
                if (allow && typeof place.permit !== typeof undefined) {

                    const issuing_at = spreader.getDateString(place.permit.issuing_at);
                    const start = spreader.getDateString(place.permit.start);
                    const finish = spreader.getDateString(place.permit.finish);

                    placeInfo.place_permit_number =  place.permit.number;
                    placeInfo.place_permit_issuing_at = issuing_at;
                    placeInfo.place_permit_start = start;
                    placeInfo.place_permit_finish = finish;
                    placeInfo.place_permit_distributor = place.permit.distributor;
                    placeInfo.place_permit_contract = place.permit.contract;
                }
                details = placeInfo;
                const side = Number(spreader.side);

                const point = painter.mark(place, index, header, body,
                    footer, details, iconSet, side);
                Places[index] = point;
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