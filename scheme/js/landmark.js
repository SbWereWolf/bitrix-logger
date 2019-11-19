const landmark = {
    zoom: 17,
    changeCurrent: function (e) {
        if (Object.keys(placement.current).length !== 0) {
            placement.current.editor.geometry
                .setCoordinates(placement.rollback);
            placement.current.options.set({draggable: false});
        }
        if(typeof e.originalEvent.target  === 'undefined') placement.current = e.originalEvent.currentTarget;
        else placement.current =  e.originalEvent.target;
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
        //landmark.start();

        const place = {};
        const yx = myMap.getCenter();
        place.y = yx[0];
        place.x = yx[1];
       // const panorama = spreader.compose(place.x, place.y);
        place.name = $("#construct-types option:selected").text();
        place.construct = types[$("#construct-types").val()];
        place.location = "";
        const index = "";
        const header = "";
        const body = "";
        const footer = place.name;

        const point = painter.mark(place, index, header, body,
            footer, false, iconSetup.available, spreader.side);
        //placement.current  = point;
        placement.type = place.construct;
        placement.newMark = point;
        placement.place = place;
        myMap.geoObjects.removeAll();
        myMap.geoObjects.add(point);
        $('#new-address').val("");
        point.events.add('dragend', function(event) {
            $('#accept')[0].disabled = false;
            const coord = point.geometry.getCoordinates();
            var myGeocoder = ymaps.geocode(coord, {kind: 'house' });
            myGeocoder.then (
                function(res) {
                    //window.console.log(res.geoObjects);
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
        })
        $('#edit-tab').tab('show');
        $('.rk-edit-control').hide();
        $('#new-address-div').show();
        $('#accept').show();
        $('#decline').show();
        $('#decline')[0].disabled = false;
        $('#decline').off('click');
        $('#decline').one('click', function () {
            if(!this.disabled) {
                myMap.geoObjects.remove(point);
                //point.geometry.setCoordinates([place.y, place.x]);
                this.disabled = true;
                $('.rk-edit-control').hide();
                $('#rk-type').show();
                $('#add-new').show();
                $('#add-new')[0].disabled = false;
                $('#publish').show();
                landmarkFilter.run();
            }
        })
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
    constructTypesId: "#construct-types",
    start: function () {
        $(landmark.acceptId).prop("disabled", false);
        $(landmark.declineId).prop("disabled", false);
        landmark.disableMenu();
        $(landmark.addNewId).prop("disabled", true);
    },
    finish: function () {
        if(landmark.action === landmark.move) {
            myMap.geoObjects.remove(placement.current);

        }
        landmark.action = landmark.idle;
        $('.rk-edit-control').hide();
        $('#rk-type').show();
        $('#add-new').show();
        $('#publish').show();
        $(landmark.acceptId).prop("disabled", true);
        $(landmark.declineId).prop("disabled", true);
        $(landmark.addNewId).prop("disabled", false);
        landmarkFilter.run();
    },
    getCredentials: function () {
        return {
            login: Cookies.get("api-login"),
            hash: Cookies.get("api-hash")
        };
    },
    storePlace:  function () {
        let data = landmark.getCredentials();
        //window.console.log( placement.current);
        const coords = placement.current.geometry.getCoordinates();
        data.x = coords[1];
        data.y = coords[0];
        data.number = Number(placement.current.properties
            .get("info").number);
        data.call = 'store';
        let place = points[data.number];
        if(place) {
            place.x = data.x;
            place.y = data.y;
        }
        if($('#new-address-change')[0].checked && $('#new-address').val()) {
            data.address = $('#new-address').val();
            place.location = data.address;
            $('#new-address').val('');
        }
        landmark.block();
        $.post('/scheme/api.php', {data:JSON.stringify(data)}, function(result) {
            landmark.finish();
            landmark.unblock();
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
        if($('#new-address-change')[0].checked && $('#new-address').val()) {
            data.address = $('#new-address').val();
            place.location = data.address;
            $('#new-address').val('');
        }
        landmark.block();
        $.post('/scheme/api.php', {data:JSON.stringify(data)}, function(result) {
            if(result.success) {
                place.number = result.id;
                points[result.id] = place;
                landmark.finish();
                landmark.unblock();
            }
        }, 'json');
    },
    publish:  function () {
        let data = landmark.getCredentials();
        data.call = 'publish';

        const number = placement.current.properties.get("info").number;

        $.post('/scheme/api.php', {data:JSON.stringify(data)}, function(result) {
            console.log(result);
        }, 'json');
    },
    acceptAction: function () {
        if (landmark.action === landmark.add) {
            landmark.addNew();
            placement.newMark.options.set({draggable: false});
            placement.newMark = {};
            //landmark.finish();
        }
        if (landmark.action === landmark.move) {
            landmark.storePlace();
            //placement.current.options.set({draggable: false});
            placement.current = {};
            //landmark.finish();
        }
    },
    block: function() {
        $('.all').block(
            { message:'<div><span class="kt-spinner kt-spinner--sm kt-spinner--brand kt-spinner--left" style="padding-left: 25px;">Загрузка...</span></div>',
        overlayCSS: {
            backgroundColor: '#ffffff',
                opacity:         0.5,
        },
        css: {
            border: 'none',
                padding: '5px',
                backgroundColor: 'transparent',
                '-webkit-border-radius': '5px',
                '-moz-border-radius': '5px',
                opacity: 1,
                color: '#204d74',
        } }
    );
    },
    unblock: function() {
        $('.all').unblock()
    },
    declineAction: function () {

    },
};
