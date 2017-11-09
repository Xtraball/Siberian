/**
    If you need custom JS specific for your module:

    append this line at the end of your edit.phtml

    <script type="text/javascript" src="/app/local/modules/Job/resources/design/desktop/flat/template/job/application/edit.js"></script>

    note: don't forget to edit your paths

    and write custom JS right there
*/


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