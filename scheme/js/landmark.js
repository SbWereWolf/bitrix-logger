const landmark = {
    changeCurrent: function (e) {
        if (Object.keys(placement.current).length !== 0) {
            placement.current.geometry._coordinates =
                placement.rollback;
            placement.current.options._options.draggable
                = false;
        }
        placement.current = e.originalEvent.target;
        placement.rollback = placement.current.geometry
            ._coordinates;
    }
};
