const settings = {
    ready: false,
    type: undefined,
    admin: "admin",
    user: "user",
    setup: function () {
        $(landmarkFilter.searchId).on("click", landmarkFilter.run);
        $(landmark.addNewId).on("click", landmark.startAddNew);
        $(landmark.moveId).on("click", landmark.startMoving);
        $(landmark.acceptId).on("click", landmark.acceptAction);
        $(landmark.declineId).on("click", landmark.declineAction);
        $(landmark.publishId).on("click", landmark.publish);

        const options = selectWithTypes.get();
        $(landmark.constructTypesId).html(options);
    },
};
jQuery(function () {
    spreader.letClusterize = false;
    settings.type = settings.admin;
    settings.ready = true;
});
