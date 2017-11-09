var toggleHandler = function(toggle) {
    toggle = $(toggle);
    var radio = toggle.find("input");

    var checkToggleState = function() {
        if (radio.eq(0).is(":checked")) {
            toggle.removeClass("toggle-off");
        } else {
            toggle.addClass("toggle-off");
        }
    };

    checkToggleState();

    radio.eq(0).click(function() {
        toggle.toggleClass("toggle-off");
    });

    radio.eq(1).click(function() {
        toggle.toggleClass("toggle-off");
    });
};
