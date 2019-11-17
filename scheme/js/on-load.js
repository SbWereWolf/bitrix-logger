const x = 61.26;
const y = 55.03;
const xx = 61.54;
const yy = 55.29;
$(document).ready(function ($) {
    if($.cookie('lwversion')) switchLWVersion($.cookie('lwversion'));
    function init() {
        function setupMap() {
            sCenter = [(yy - y) / 2 + y, (xx - x) / 2 + x];

            myMap = new ymaps.Map("map", {
                // Координаты центра карты: «широта, долгота».
                center: sCenter,
                // Уровень масштабирования: от 0 (весь мир) до 19.
                zoom: 16.0
            });
        }

        iconSetup.compose();
        setupMap();
        spreader.place();
    }
    ymaps.ready(init);

    $("#search").on("click", landmarkFilter.run);
    $("#add-new").on("click", landmark.startAddNew);
    $("#move").on("click", landmark.startMoving);
    $("#accept").on("click", landmark.acceptAction);
    $("#decline").on("click", landmark.declineAction);

    const options = selectWithTypes.get();
    $("#construct-types").html(options);

    $("#enable").on("click", function () {
        spreader.letClusterize = true;
        landmarkFilter.run();
    });
    $("#disable").on("click", function () {
        spreader.letClusterize = false;
        landmarkFilter.run();
    });
});
function switchLWVersion(on) {
    if(on && on != '00') {
        if(on === 1 && $.cookie('lwversion')) on = "11";
        else if($.cookie('lwversion')) {
            const oldf = $.cookie('lwversion').charAt(0) * 1;
            const oldc = $.cookie('lwversion').charAt(1) * 1;
            const newf = on.charAt(0);
            const newc = on.charAt(1);
            let f = 1;
            let c = 1;
            if(newf * 1 != 0) f = newf;
            else c = oldf ? oldf : 1;
            if(newc * 1 != 0) c = newc;
            else c = oldc ? oldc : 1;
            on = f + '' + c;
        }
        else on = 11;
        let version = on + '';
        $.cookie('lwversion', on, { expires: 365 });
        $('body').attr('class','');
        $('body').addClass('lversion');
        $('body').addClass('lversion lwf-' + on.charAt(0));
        $('body').addClass('lversion lwc-' + (on.charAt(1) ? on.charAt(1) : '1'));
    } else {
        $.cookie('lwversion', '00', { expires: 365 });
        $('body').attr('class','');
    }
}
