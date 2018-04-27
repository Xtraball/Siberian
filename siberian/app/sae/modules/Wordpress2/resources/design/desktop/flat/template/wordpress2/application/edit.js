/*global
 bindForms
 */
$(document).ready(function () {
    bindForms('#wordpress2');
    bindForms('#design');
    bindForms('#settings');

    // Display cache message hint
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

    // Toggle sort fields
    var toggleSortWordPress = function () {
        var isGrouped = $('#group_queries').is(':checked');
        if (isGrouped) {
            $('#form-wordpress2-wordpress #control-group_sort_type').show();
            $('#form-wordpress2-wordpress #control-group_sort_order').show();

            $('#form-wordpress2-query #control-group_sort_type').hide();
            $('#form-wordpress2-query #control-group_sort_order').hide();
        } else {
            $('#form-wordpress2-wordpress #control-group_sort_type').hide();
            $('#form-wordpress2-wordpress #control-group_sort_order').hide();

            $('#form-wordpress2-query #control-group_sort_type').show();
            $('#form-wordpress2-query #control-group_sort_order').show();
        }
    };

    $(document).off('change', '#group_queries');
    $(document).on('change', '#group_queries', function () {
        toggleSortWordPress();
    });
    toggleSortWordPress();
});
