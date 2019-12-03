const landmark = {
    zoom: 17,
    changeCurrent: function (e) {
        if (Object.keys(placement.current).length !== 0) {
            placement.current.editor.geometry
                .setCoordinates(placement.rollback);
            placement.current.options.set({draggable: false});
        }
        if (typeof e.originalEvent.target === 'undefined') placement.current = e.originalEvent.currentTarget;
        else placement.current = e.originalEvent.target;
        placement.rollback = placement.current.editor.geometry
            .getCoordinates();
    },
    enableMenu: function () {
        $(landmark.moveId).prop("disabled", false);
        $(landmark.publishId).prop("disabled", false);
        $(landmark.addNewId).prop("disabled", true);
    },
    disableMenu: function () {
        $(landmark.moveId).prop("disabled", true);
        $(landmark.publishId).prop("disabled", true);
    },
    startMoving: function () {
        landmark.action = landmark.move;
        landmark.start();
        placement.current.options.set({draggable: true});
    },
    startAddNew: function () {
        landmark.action = landmark.add;

        const place = {};
        const yx = myMap.getCenter();
        place.y = yx[0];
        place.x = yx[1];
        place.name = $("#construct-types option:selected").text();
        place.construct = types[$("#construct-types").val()];
        place.location = "";
        const index = "";
        const header = "";
        const body = "";
        const footer = place.name;

        const point = painter.mark(place, index, header, body,
            footer, false, iconSetup.available, spreader.side);
        placement.type = place.construct;
        placement.newMark = point;
        placement.place = place;
        myMap.geoObjects.removeAll();
        myMap.geoObjects.add(point);
        $('#new-address').val("");
        point.events.add('dragend', function () {
            $('#accept')[0].disabled = false;
            const coord = point.geometry.getCoordinates();
            var myGeocoder = ymaps.geocode(coord, {kind: 'house'});
            myGeocoder.then(
                function (res) {
                    var street = res.geoObjects.get(0);
                    var name = street.properties.get('name');
                    // Будет выведено «улица Большая Молчановка»,
                    // несмотря на то, что обратно геокодируются
                    // координаты дома 10 на ул. Новый Арбат.
                    if (name) {
                        $('#new-address').val(name);
                    }
                }
            );
        });
        $('#edit-tab').tab('show');
        $('.rk-edit-control').hide();
        $('#new-address-div').show();
        $('#accept').show();
        const decline = $('#decline');
        decline.show();
        decline[0].disabled = false;
        decline.off('click');
        decline.one('click', function () {
            if (!this.disabled) {
                myMap.geoObjects.remove(point);
                this.disabled = true;
                $('.rk-edit-control').hide();
                $('#rk-type').show();
                const addNew = $('#add-new');
                addNew.show();
                addNew[0].disabled = false;
                $('#publish').show();
                landmarkFilter.run();
            }
        });
        myMap.setZoom(landmark.zoom);
    },
    move: "move",
    add: "add",
    idle: "idle",
    action: "idle",
    acceptId: "#accept",
    declineId: "#decline",
    addNewId: "#add-new",
    moveId: "#move",
    publishId: "#publish",
    releaseId: "#release",
    constructTypesId: "#construct-types",
    flushId: "#flush",
    recompileId: "#recompile",
    flush: function () {
        $('#address').val("");
        let data = landmark.getCredentials();
        data.call = 'flush';
        landmark.block();
        $.post('/scheme/api.php', {data: JSON.stringify(data)}, function (result) {
            if (result.success) {
                $.get('/scheme/js/points.json?' + randStr(), function (data) {
                    myMap.geoObjects.removeAll();
                    points = data;
                    landmarkFilter.run();
                    landmark.unblock();
                }, 'json');
            } else {
                alert('Произошла ошибка, попробуйте позднее...');
                landmark.unblock();
            }
        }, 'json');
    },
    recompile: function () {
        $('#address').val("");
        let data = landmark.getCredentials();
        data.call = 'recompile';
        landmark.block();
        $.post('/scheme/api.php', {data: JSON.stringify(data)},
            function (result) {
                if (result.success) {
                    landmark.unblock();
                } else {
                    alert('Произошла ошибка, попробуйте позднее...');
                    landmark.unblock();
                }
            }, 'json');
    },
    start: function () {
        $(landmark.acceptId).prop("disabled", false);
        $(landmark.declineId).prop("disabled", false);
        landmark.disableMenu();
        $(landmark.addNewId).prop("disabled", true);
    },
    finish: function () {
        if (landmark.action === landmark.move) {
            myMap.geoObjects.remove(placement.current);

        }
        landmark.action = landmark.idle;
        $('.rk-edit-control').hide();
        $('#rk-type').show();
        $('#add-new').show();
        $('#publish').show();
        $('#flush').show();
        $(landmark.acceptId).prop("disabled", true);
        $(landmark.declineId).prop("disabled", true);
        $(landmark.addNewId).prop("disabled", false);
        $('#address').val("");
        landmarkFilter.run();
    },
    getCredentials: function () {
        return {
            login: Cookies.get("api-login"),
            hash: Cookies.get("api-hash")
        };
    },
    storePlace: function () {
        let data = landmark.getCredentials();
        const coords = placement.current.geometry.getCoordinates();
        data.x = coords[1];
        data.y = coords[0];
        data.number = Number(placement.current.properties
            .get("info").number);
        data.call = 'store';
        let place = points[data.number];
        if (place) {
            place.x = data.x;
            place.y = data.y;
        }
        const newAddress = $('#new-address');
        if ($('#new-address-change')[0].checked && newAddress.val()) {
            data.address = newAddress.val();
            place.location = data.address;
            newAddress.val('');
        }
        landmark.block();
        $.post('/scheme/api.php', {data: JSON.stringify(data)}, function (result) {
            if (result.success) {
                landmark.finish();
                landmark.unblock();
            } else {
                alert('Во время сохранения произошла ошибка: ' + result.message);
                landmark.finish();
                landmark.unblock();
            }
        }, 'json');
    },
    addNew: function () {
        let data = landmark.getCredentials();
        data.call = 'new';

        const coords = placement.newMark.geometry.getCoordinates();
        data.y = coords[0];
        data.x = coords[1];
        data.type = placement.type;
        let place = placement.place;
        place.x = data.x;
        place.y = data.y;
        place.construct_area = "";
        const newAddress = $('#new-address');
        if ($('#new-address-change')[0].checked && newAddress.val()) {
            data.address = newAddress.val();
            place.location = data.address;
            newAddress.val('');
        }
        landmark.block();
        $.post('/scheme/api.php', {data: JSON.stringify(data)}, function (result) {
            if (result.success) {
                place.number = result.id;
                points[result.id] = place;
                landmark.finish();
                landmark.unblock();
            } else {
                alert('Во время сохранения произошла ошибка: ' + result.message);
                landmark.finish();
                landmark.unblock();
            }
        }, 'json');
    },
    publish: function () {
        let data = landmark.getCredentials();
        data.call = 'publish';
        data.number = placement.current.properties.get("info").place_number;

        $.post('/scheme/api.php', {data: JSON.stringify(data)}, function (result) {
            if (result.success) alert('Опулибковано');
            landmark.unblock();
        }, 'json');
    },
    release: function () {
        let data = landmark.getCredentials();
        data.call = 'release';

        $.post('/scheme/api.php', {data: JSON.stringify(data)},
            function (result) {
                if (result.success) {
                    alert('ВСЁ Опулибковано');
                }
                landmark.unblock();
            }, 'json');
    },
    acceptAction: function () {
        if (landmark.action === landmark.add) {
            landmark.addNew();
            placement.newMark.options.set({draggable: false});
            placement.newMark = {};
        }
        if (landmark.action === landmark.move) {
            landmark.storePlace();
            placement.current = {};
        }
    },
    block: function () {
        $('.all').block(
            {
                message: '<div><span class="kt-spinner kt-spinner--sm kt-spinner--brand kt-spinner--left" style="padding-left: 25px;">Загрузка...</span></div>',
                overlayCSS: {
                    backgroundColor: '#ffffff',
                    opacity: 0.5,
                },
                css: {
                    border: 'none',
                    padding: '5px',
                    backgroundColor: 'transparent',
                    '-webkit-border-radius': '5px',
                    '-moz-border-radius': '5px',
                    opacity: 1,
                    color: '#204d74',
                },
                fadeIn: 0,
            }
        );
    },
    unblock: function () {
        $('.all').unblock()
    },
    declineAction: function () {

    },
};
