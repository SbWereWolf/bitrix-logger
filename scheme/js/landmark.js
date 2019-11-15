const landmark = {
    zoom: 19,
    changeCurrent: function (e) {
        if (Object.keys(placement.current).length !== 0) {
            placement.current.editor.geometry
                .setCoordinates(placement.rollback);
            placement.current.options.set({draggable: false});
        }
        placement.current = e.originalEvent.target;
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
        landmark.start();

        const place = {};
        const yx = myMap.getCenter();
        place.y = yx[0];
        place.x = yx[1];
        const panorama = spreader.compose(place.x, place.y);
        place.name = $("#construct-types option:selected").text();
        place.construct = types[$("#construct-types").val()];
        place.location = "";
        const index = "б/н";
        const header = `РК №${index} (${place.construct})`;
        const body = `<p><ul><li>`
            + `Адрес: <b>${place.location}</b>`
            + `</li></ul></p>`
            + `<a class="btn btn-block btn-success"`
            + ` target="_blank"`
            + ` href="${panorama}">`
            + `Открыть панораму</a>`
        ;
        const footer = place.name;

        const point = painter.mark(place, index, header, body,
            footer, {}, iconSetup.available, spreader.side);

        point.options.set({draggable: true});
        placement.type = place.construct;
        placement.newMark = point;

        myMap.geoObjects.add(point);

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
        landmark.action = landmark.idle;
        $(landmark.acceptId).prop("disabled", true);
        $(landmark.declineId).prop("disabled", true);
        $(landmark.addNewId).prop("disabled", false);
    },
    storePlace: function () {
        const coords = placement.current.geometry.getCoordinates();
        const y = coords[0];
        const x = coords[1];
        const number = placement.current.properties.get("info").number;
    },
    addNew: function () {
        const coords = placement.newMark.geometry.getCoordinates();
        const y = coords[0];
        const x = coords[1];
        const type = placement.type;
    },
    publish: function () {
        const number = placement.current.properties.get("info").number;
    },
    acceptAction: function () {
        if (landmark.action === landmark.add) {
            landmark.addNew();
            placement.newMark.options.set({draggable: false});
            placement.newMark = {};
            landmark.finish();
        }
        if (landmark.action === landmark.move) {
            landmark.storePlace();
            placement.current.options.set({draggable: false});
            placement.current = {};
            landmark.finish();
        }
    },
    declineAction: function () {
        if (landmark.action === landmark.add) {
            myMap.geoObjects.remove(placement.newMark);
            placement.newMark = {};
            landmark.finish();
        }
        if (landmark.action === landmark.move) {
            placement.current.editor.geometry
                .setCoordinates(placement.rollback);
            placement.current.options.set({draggable: false});
            placement.current = {};
            landmark.finish();
        }
    },
};
