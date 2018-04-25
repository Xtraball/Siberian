/*global
 bindForms
 */
$(document).ready(function () {
    bindForms('#wordpress2');
    bindForms('#design');
    bindForms('#settings');

    var toggleNoCache = function () {
        var el = $('#cache_lifetime');
        if (el.val() == '0') {
            $('#note_no_cache').show();
        } else {
            $('#note_no_cache').hide();
        }
    };

    $(document).off('change', '#cache_lifetime');
    $(document).on('change', '#cache_lifetime', function () {
        toggleNoCache();
    });
    toggleNoCache();
});
