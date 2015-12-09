var enable_collapse_icon = function(panel, icon) {
    $(panel).on('hide.bs.collapse', function() {
        $(icon).addClass('glyphicon-collapse-down').removeClass('glyphicon-collapse-up');
    });
    $(panel).on('show.bs.collapse', function() {
        $(icon).removeClass('glyphicon-collapse-down').addClass('glyphicon-collapse-up');
    });
}
