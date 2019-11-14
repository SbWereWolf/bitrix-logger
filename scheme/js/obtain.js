function obtain() {

    function defineFilter() {
        const filter = {types: [], address: ""};
        const source = $("#search");
        const parameters = source.serializeArray();
        $.each(parameters, function (index, subject) {
            const isAddress = subject.name === "address";
            if (isAddress) {
                filter.address = subject.value;
            }
            if (!isAddress) {
                filter.types.push(types[subject.name]);
            }
        });

        return filter;
    }

    const conditions = defineFilter();

    myMap.geoObjects.removeAll();
    adjustCluster(conditions);

    const address = conditions.address;
    if (address !== "") {
        ymaps.geocode(address, {
            boundedBy: [[y, x], [yy, xx]],
            strictBounds: true,
            results: 1
        }).then(function (res) {
            const firstGeoObject = res.geoObjects.get(0),
                coords = firstGeoObject.geometry.getCoordinates(),
                bounds = firstGeoObject.properties.get('boundedBy');

            myMap.setCenter(coords);
            myMap.setBounds(bounds, {
                checkZoomRange: true
            });
        });
    }
}