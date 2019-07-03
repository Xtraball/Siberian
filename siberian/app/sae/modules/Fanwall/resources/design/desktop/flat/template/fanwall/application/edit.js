ckeditor_config.social_wall = {
    language: ckeditor_language,
    toolbar: [
        {
            name: 'basicstyles',
            groups: ['basicstyles', 'cleanup'],
            items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']
        },
        {
            name: 'paragraph',
            groups: ['align'],
            items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
        },
        { name: 'styles', items: ['TextColor'] },
        { name: 'other', items: ['featurelink'] }
    ],
    extraPlugins: 'featurelink',
    extraAllowedContent: 'a[*];img[*];iframe[*]'
};

$(document).ready(function () {
    bindForms('#list');

    $('table.sb-pager').sbpager({
        with_search: true,
        search_placeholder: search_placeholder,
        callback_goto_page: function () {
            $('table.sb-pager tr.edit-form[data-id]').hide();
        }
    });

    let processDates = function () {
        $(".moment_format").each(function () {
            let jThis = $(this);
            let tmp = moment(jThis.text() * 1000).format('LLL');
            jThis.text(tmp);
            // Removes the class to prevent multiple processing.
            jThis.removeClass("moment_format");
        });
    };

    processDates();
});