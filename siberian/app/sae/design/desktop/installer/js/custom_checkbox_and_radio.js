// Custom checkbox and radios
function setupLabel(element) {

    var input = element.children('input');
    if(input.is(':checked')) element.addClass('checked');
    else element.removeClass('checked');

    if(input.is(':disabled')) element.addClass('disabled');
    else element.removeClass('disabled');

};

function bindLabel(element) {
    element.click(function() {
        var input = $(this).children('input');
        var label = $('input[name="'+input.attr('name')+'"]').parent('label');
        if(label.hasClass('radio')) {
            label.removeClass('checked disabled');
        }
        setupLabel($(this));
    });
}

function checkInput(element) {
    element.children('input').prop('checked', true);
    setupLabel(element);
}
function uncheckInput(element) {
    element.children('input').prop('checked', false);
    setupLabel(element);
}

function createCustomUI() {

    $("html").addClass("has-js");

    // First let's prepend icons (needed for effects)
    $(".checkbox, .radio").each(function() {
        if($(this).find('span.icon').length == 0) {
            $(this).prepend("<span class='icon'></span><span class='icon-to-fade'></span>");
            bindLabel($(this));
            setupLabel($(this));
        }
    });
}

function prepareCustomUI(element) {

    if(element.find('span.icon').length == 0) {
        element.prepend("<span class='icon'></span><span class='icon-to-fade'></span>")
        .click(function() {
            var input = element.children('input');
            var label = $('input[name="'+input.attr('name')+'"]').parent('label');
            if(label.hasClass('radio')) {
                label.removeClass('checked disabled');
            }
            setupLabel(element);
        });

        setupLabel(element);
    }

}

function destroyCustomUI(element) {
    element.unbind('click');
}

$(document).ready(function(){
    createCustomUI();
});

