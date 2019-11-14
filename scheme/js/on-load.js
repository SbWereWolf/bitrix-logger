jQuery(function ($) {
    function init() {
        function setupMap() {
            const x = 61.26;
            const y = 55.03;
            const xx = 61.54;
            const yy = 55.29;
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
});
