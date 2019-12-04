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
            if (!isAddress && subject.name != 'sector-0' && subject.name != 'sector-1') {
                filter.types.push(types[subject.name]);
            }
        });

        return filter;
    },
    run: function () {
        landmark.block();
        const conditions = landmarkFilter.define();
        //console.log(conditions);
        //spreader.place(conditions);
        const address = conditions.address;
        if (address !== "") {
            if(lastsearched && lastsearched.address == address) {
                myMap.geoObjects.removeAll();
                spreader.place(conditions);
                landmark.unblock();
                return;
            }
            if(address.match(/^\d+$/)) {
                if(Places[address]) {
                    //console.log(Places[address]);
                    const p = points[address];
                    console.log([p.y,p.x]);
                    try {
                        myMap.setCenter([p.y,p.x], 17);
                        //console.log(Places[address].balloon);
                        Places[address].balloon.open();
                    } catch (e) {
                      console.log(e);
                    }
                    landmark.unblock();
                    return;
                }
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
                //spreader.place(conditions);
                //landmark.unblock();
            });
        } else {
            myMap.geoObjects.removeAll();
            spreader.place(conditions);
            landmark.unblock();
        }
    }
};
