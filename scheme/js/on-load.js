jQuery(function ($) {
    $(landmarkFilter.searchId).submit(function (event) {
        event.preventDefault();
    });
    iconSetup.compose();
    const waitFor = 500;
    let waitSettings = setTimeout(function makeMapReady() {
        let isReady = settings.ready;
        if (isReady) {
            settings.setup();

            function init() {
                function setupMap() {
                    sCenter = [(yy - y) / 2 + y, (xx - x) / 2 + x];

                    myMap = new ymaps.Map("map", {
                        // Координаты центра карты: «широта, долгота».
                        center: sCenter,
                        // Уровень масштабирования: от 0 (весь мир) до 19.
                        zoom: 16.0
                    });
                    if (settings.admin) {
                        myMap.events
                            .add('balloonopen', function () {
                                landmark.enableMenu();
                            })
                            .add('balloonclose', function () {
                                landmark.disableMenu();
                                $(landmark.addNewId).prop("disabled",
                                    false);
                            });
                    }
                }

                setupMap();
                spreader.place();
            }

            ymaps.ready(init);
        }
        if (!isReady) {
            waitSettings = setTimeout(makeMapReady, waitFor);
        }
    }, waitFor);
});
