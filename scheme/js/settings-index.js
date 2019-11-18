const settings = {
    ready: false,
    type: undefined,
    admin: "admin",
    user: "user",
    setup: function () {
        $('.clicktofind').on("click", landmarkFilter.run);
    },
};
jQuery(function () {
    spreader.letClusterize = true;
    settings.type = settings.user;
    settings.ready = true;
});
