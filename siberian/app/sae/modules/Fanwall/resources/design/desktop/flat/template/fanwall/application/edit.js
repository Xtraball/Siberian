$(document).ready(function () {
    bindForms('#list');

    $('table.sb-pager').sbpager({
        with_search: true,
        search_placeholder: search_placeholder,
        callback_goto_page: function () {
            $('table.sb-pager tr.edit-form[data-id]').hide();
        }
    });
});