/*global
    $, bindForms, search_placeholder
 */
$(document).ready(function () {
    bindForms('#list');

    $('table.sb-pager').sbpager({
        with_search: true,
        search_placeholder: search_placeholder,
        callback_goto_page: function () {
            $('table.sb-pager tr.edit-form[data-id]').hide();
            $('table.sb-pager tr.block-details').hide();
        }
    });

    // Clear opened details/edit forms!
    $('table.sb-pager .delete-action form').on('submit', function () {
        $('table.sb-pager tr.edit-form[data-id]').hide();
        $('table.sb-pager tr.block-details').hide();
    });

    var lastOpened = null;
    $('i.toggle-more').click(function () {
        var el = $(this);
        var promotionid = $(this).data('promotionid');
        var details = $('#details_' + promotionid);

        // Move element right under the bock (in case of a sort/search)
        el.parents('tr').after(details);

        // Hides edit-forms
        $('table.sb-pager .edit-form').hide();

        if ((lastOpened !== null) && (lastOpened !== details)) {
            lastOpened.slideUp();
        }

        if (details.is(':visible')) {
            details.slideUp();
        } else {
            details.slideDown();
            lastOpened = details;
        }
    });
});
