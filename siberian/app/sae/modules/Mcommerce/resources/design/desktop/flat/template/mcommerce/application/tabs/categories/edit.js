$(document).ready(function () {
    var guid = function () {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        return s4() + s4() + s4() + s4() + s4();
    };

    var buildSortable = function () {
        $('#nested-root').nestedSortable({
            handle: 'i.category-sortable-handle',
            errorClass: 'category-nested-error',
            listType: 'ul',
            items: 'li',
            tabSize: 26,
            maxLevels: maxNestedLevel,
            placeholder: 'placeholder',
            toleranceElement: '> span',
            opacity: 0.7,
            isAllowed: function (placeholder, placeholderParent, currentItem) {
                return true;
            },
            start: function (event, ui) {
                // TBD!
            },
            stop: function (event, ui) {
                // TBD!
            },
            update: function (event, ui) {
                // TBD!
                updatePositions(ui.item);
            }
        });
    };

    var getPositions = function (element) {
        var jElement = $(element);
        var rel = jElement.attr('rel');
        var firstParent = jElement.parent('ul');
        var typeName = jElement.attr('typeName');
        var mappedPositions = $.map($.makeArray(firstParent.find('> li.category-sortable[typeName="' + typeName + '"]')), function (val) {
            return $(val).attr('rel');
        });

        var parentId;
        try {
            parentId = $('.category-sortable[typeName="' + typeName + '"][rel="' + rel + '"]')
                .parents('li[rel]')[0].attributes.rel.value;
        } catch (e) {
            parentId = 'root';
        }

        var result = {};
        switch (typeName) {
            case 'category':
                result = {
                    'category': {
                        categoryId: rel,
                        parentId: parentId
                    },
                    'positions': mappedPositions
                };
                break;
        }

        return result;
    };

    var updatePositions = function (element) {
        var positions = getPositions(element);
        var typeName = element.attr('typeName');

        $.ajax({
            url: '/mcommerce/application_category/update-positions/value_id/' + valueId,
            method: 'POST',
            data: {
                typeName: typeName,
                positions: positions,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {},
            error: function (response) {}
        });
    };

    // Adds active class for blur/focus!
    $(document).off('blur', 'input.category-title');
    $(document).on('blur', 'input.category-title', function () {
        $('li.category-sortable').removeClass('active');
    });

    $(document).off('focus', 'input.category-title');
    $(document).on('focus', 'input.category-title', function () {
        var current = $(this);
        setTimeout(function () {
            $('li.category-sortable[rel="' + current.attr('rel') + '"]').addClass('active');
        }, 100);
    });

    // Save titles!
    $(document).off('change', 'input.category-title');
    $(document).on('change', 'input.category-title', function () {
        var element = $(this);
        var title = element.val();
        var categoryId = element.attr('rel');

        $.ajax({
            url: '/mcommerce/application_category/update-category/value_id/' + valueId,
            method: 'POST',
            data: {
                actionName: 'update',
                categoryId: categoryId,
                title: title,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                // TBD!
            },
            error: function (response) {
                // TBD!
            }
        });
    });

    // Always append to root category!
    $(document).off('click', '.create-category');
    $(document).on('click', '.create-category', function () {
        $.ajax({
            url: '/mcommerce/application_category/create-category/value_id/' + valueId,
            method: 'POST',
            data: {
                parentId: categoryParentId,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                // Append subfolder!
                $('ul#nested-root > li > ul').append(data.placeholder);

                document.querySelector('ul.nested-categories').scrollTop = 2000000000;
            },
            error: function (response) {}
        });
    });

    // Delete category and children category!
    $(document).off('click', '.category-delete');
    $(document).on('click', '.category-delete', function () {
        var deleteButton = $(this);
        var categoryId = deleteButton.attr('rel');
        var typeName = deleteButton.attr('typeName');

        var categoryName;
        var title;
        var text;
        var data;
        switch (typeName) {
            case 'category':
                    title = words.deleteTitle;
                    categoryName = $('.category-sortable[typeName="category"][rel="' + categoryId + '"] > span > input.category-title').val();
                    text = words.deleteText.replace('#CATEGORY_NAME#', '<b>' + categoryName + '</b>');
                    data = {
                        actionName: 'delete',
                        typeName: 'category',
                        categoryId: categoryId,
                        valueId: valueId
                    };
                break;
        }

        swal({
            html: true,
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonColor: '#ff3a2e',
            confirmButtonText: words.confirmDelete,
            cancelButtonText: words.cancelDelete,
            buttons: true
        }, function () {
            $.ajax({
                url: '/mcommerce/application_category/delete-category/value_id/' + valueId,
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function (data) {
                    // Remove DOM element (and it's childrens)
                    $('.category-sortable[typeName="' + typeName + '"][rel="' + categoryId + '"]').remove();
                },
                error: function (response) {}
            });
        });
    });

    buildSortable();
    bindForms('#design');
});
