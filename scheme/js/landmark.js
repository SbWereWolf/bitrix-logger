const landmark = {
    zoom: 19,
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
    start: function () {
        $("#accept").prop("disabled", false);
        $("#decline").prop("disabled", false);
    },
    finish: function () {
        landmark.action = landmark.idle;
        $("#accept").prop("disabled", true);
        $("#decline").prop("disabled", true);
    },
    storeCoordinates: function () {
    },
    addNew: function () {
    },
    acceptAction: function () {
        if (landmark.action === landmark.add) {
            placement.newMark.options.set({draggable: false});
            placement.newMark = {};
            landmark.finish();
            landmark.addNew();
        }
        if (landmark.action === landmark.move) {
            placement.current.options.set({draggable: false});
            placement.current = {};
            landmark.finish();
            landmark.storeCoordinates();
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
