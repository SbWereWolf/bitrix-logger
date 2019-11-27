let points;
let randStr = function () {

    return '_' + Math.random().toString(36).substr(2, 9);
};
jQuery(function ($) {
    const lowVision = Cookies.get("lwversion");
    if (typeof lowVision !== typeof undefined) {
        switchLWVersion(lowVision);
    }
    $(landmarkFilter.searchId).submit(function (event) {
        event.preventDefault();
        return false;
    });
    iconSetup.compose();
    const waitFor = 500;
    $.get('/scheme/js/points.json?' + randStr(), function(data) {
        points = data;
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
    }, 'json');
});

function switchLWVersion(on) {
    if (on && on != '00') {
        if (on === 1 && Cookies.get("lwversion")) on = "11";
        else if (typeof Cookies.get("lwversion") !== typeof undefined) {
            const oldf = Cookies.get("lwversion").charAt(0) * 1;
            const oldc = Cookies.get("lwversion").charAt(1) * 1;
            const newf = on.charAt(0);
            const newc = on.charAt(1);
            let f = 1;
            let c = 1;
            if (newf * 1 != 0) f = newf;
            else c = oldf ? oldf : 1;
            if (newc * 1 != 0) c = newc;
            else c = oldc ? oldc : 1;
            on = f + '' + c;
        } else on = 11;
        let version = on + '';
        Cookies.set('lwversion', on, {expires: 365});
        const bodyEl = $('body');
        bodyEl.attr('class', '');
        bodyEl.addClass('lversion');
        bodyEl.addClass('lversion lwf-' + on.charAt(0));
        bodyEl.addClass('lversion lwc-' + (on.charAt(1) ? on.charAt(1) : '1'));
    } else {
        Cookies.set('lwversion', '00', {expires: 365});
        $('body').attr('class', '');
    }
}
