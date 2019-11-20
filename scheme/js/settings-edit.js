const settings = {
    ready: false,
    type: undefined,
    admin: "admin",
    user: "user",
    setup: function () {
        $(landmarkFilter.searchId + ' .clicktofind').on("click", landmarkFilter.run);
        $(landmark.addNewId).on("click", landmark.startAddNew);
        //$(landmark.moveId).on("click", landmark.startMoving);
        $(landmark.acceptId).on("click", landmark.acceptAction);
        $(landmark.declineId).on("click", landmark.declineAction);
        $(landmark.publishId).on("click", landmark.publish);

        const options = selectWithTypes.get();
        $(landmark.constructTypesId).html(options);
    },
};
jQuery(function () {
    spreader.letClusterize = true;
    settings.type = settings.admin;
    settings.ready = true;
});
$(document).ready(function(){
    if($('#sector-0')[0]) {
        const sectors0 = ['Выбрать', 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','S'];
        const sectors1 = 37;
        let s1 = '';
        let s2 = '';
        for(let i = 0; i < sectors0.length; i++) {
            s1 += '<option value="' + i + '">' + sectors0[i] + '</option>';
        }
        for(let i = 0; i <= sectors1; i++) {
            s2 += '<option value="' + i + '">' + (i == 0 ? 'Выбрать'  : i) + '</option>';
        }
        $('#sector-0').html(s1);
        $('#sector-1').html(s2);
        $('#goto-sector').off('click');
        $('#goto-sector').click(function() {
            if( $('#sector-0').val() * 1 &&  $('#sector-1').val() * 1) {
                const x = 61.24001013;
                const y = 55.30158314;
                const xx = 0.01797686549305;
                const yy = 0.00716260875954;
                const cords = [
                    y - yy * ($('#sector-1').val() * 1) + yy / 2,
                    x + xx * ($('#sector-0').val() * 1) - xx / 2,
                ]
                myMap.setCenter(cords, 16);
            }
        });

    }
})
