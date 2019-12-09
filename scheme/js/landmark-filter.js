/*
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 9.12.2019 15:14 Volkhin Nikolay
 */

let lastsearched;
landmarkFilter = {
    searchId: "#show",
    define: function () {
        const filter = {types: [], address: ""};
        const source = $(landmarkFilter.searchId);
        const parameters = source.serializeArray();
        $.each(parameters, function (index, subject) {
            const isAddress = subject.name === "address";
            if (isAddress) {
                filter.address = subject.value;
            }
            if (!isAddress && subject.name != 'sector-0'
                && subject.name != 'sector-1') {
                filter.types.push(types[subject.name]);
            }
        });

        return filter;
    },
    run: function () {
        landmark.block();
        const conditions = landmarkFilter.define();
        const address = conditions.address;
        if (address !== "") {
            if (lastsearched && lastsearched.address === address) {
                myMap.geoObjects.removeAll();
                spreader.place(conditions);
                landmark.unblock();
                return;
            }
            const isNumber = address.match(/^\d+$/);
            if (isNumber) {
                const digits = Number(address);
                let skip = false;
                $.each(points, function (index, point) {
                    let letFocus = false;
                    if (!skip) {
                        letFocus = point.number === digits;
                    }
                    if (letFocus) {
                        try {
                            myMap.setCenter([point.y, point.x], 18);
                            Places[point.id].balloon.open();
                        } catch (e) {
                            console.log(e);
                        }
                        landmark.unblock();
                        skip = true;
                    }
                });
            }
            ymaps.geocode(address, {
                boundedBy: [[y, x], [yy, xx]],
                strictBounds: true,
                results: 1
            }).then(function (res) {
                landmark.unblock();
                const firstGeoObject = res.geoObjects.get(0),
                    coords = firstGeoObject.geometry.getCoordinates(),
                    bounds = firstGeoObject.properties.get('boundedBy');
                myMap.setCenter(coords);
                myMap.setBounds(bounds, {
                    checkZoomRange: true
                });
                lastsearched = {address:conditions.address, coords:coords, bounds:bounds };
            });
        } else {
            myMap.geoObjects.removeAll();
            spreader.place(conditions);
            landmark.unblock();
        }
    }
};
