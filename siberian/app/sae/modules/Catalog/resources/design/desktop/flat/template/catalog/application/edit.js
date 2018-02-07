/*global
    isInit, folder, options, valueId, placeholderCategory,
    bindForms, swal, words, carouselSearch, carousel, featureSearch,
    maxNestedLevel
 */
$(document).ready(function () {
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
                // Category check for nested!
                console.log('currentItem.attr(\'typeName\')', currentItem.attr('typeName'));
                console.log('placeholderParent', placeholderParent);
                console.log('placeholderParent.parents', placeholderParent.parents('li.category-sortable'));
                if (currentItem.attr('typeName') === 'category' &&
                    (
                        (placeholderParent !== undefined &&
                        placeholderParent.parents('li.category-sortable') !== undefined &&
                        placeholderParent.parents('li.category-sortable').length <= 0)
                        ||
                        (placeholderParent === undefined)
                    )) {
                    return true;
                }

                // Product check for nested!
                if (currentItem.attr('typeName') === 'product' &&
                    placeholderParent !== undefined &&
                    placeholderParent.attr('typeName') === 'category' &&
                    placeholderParent.parents('li.category-sortable') !== undefined &&
                    placeholderParent.parents('li.category-sortable').length >= 0) {
                    return true;
                }
                return false;
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
                updateProductCount();
            }
        });
    };

    var getPositions = function (element) {
        var jElement = $(element);
        var rel = jElement.attr('rel');
        var firstParent = jElement.parent('ul');
        var mappedPositions = $.map($.makeArray(firstParent.find('> li.category-sortable[typeName="category"]')), function (val) {
            return $(val).attr('rel');
        });

        var parentId;
        try {
            parentId = $('.category-sortable[rel="' + rel + '"]').parents('li[rel]')[0].attributes.rel.value;
        } catch (e) {
            parentId = 'root';
        }

        return {
            'category': {
                categoryId: rel,
                parentId: parentId
            },
            'positions': mappedPositions
        };
    };

    var updatePositions = function (element) {
        var positions = getPositions(element);

        $.ajax({
            url: '/catalog/application/updatepositions/value_id/' + valueId,
            method: 'POST',
            data: {
                actionName: 'update',
                positions: positions,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                updateProductCount();
            },
            error: function (response) {
                console.log(response);
            }
        });
    };

    var updateProductCount = function () {
        var categoryIds = $.map($.makeArray($('.category-sortable[rel]')), function (val, i) {
            return $(val).attr('rel');
        });

        categoryIds.forEach(function (element) {
            var count = 'N.A.';
            $('.category-sortable[rel="' + element + '"] span.category-feature-count').text('(' + count + ')');
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
            url: '/catalog/application/updatecategory/value_id/' + valueId,
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

    // Delete folder and childrens category!
    $(document).off('click', '.category-delete');
    $(document).on('click', '.category-delete', function () {
        var deleteButton = $(this);
        var categoryId = deleteButton.attr('rel');
        var categoryName = $('.category-sortable[rel="' + categoryId + '"] > span > input.category-title').val();
        swal({
            html: true,
            title: words.deleteTitle,
            text: words.deleteText.replace('#CATEGORY_NAME#', '<b>' + categoryName + '</b>'),
            showCancelButton: true,
            confirmButtonColor: '#ff3a2e',
            confirmButtonText: words.confirmDelete,
            cancelButtonText: words.cancelDelete,
            buttons: true
        }, function () {
            $.ajax({
                url: '/catalog/application/deletecategory/value_id/' + valueId,
                method: 'POST',
                data: {
                    actionName: 'delete',
                    categoryId: categoryId,
                    valueId: valueId
                },
                dataType: 'json',
                success: function (data) {
                    // Remove DOM element (and it's childrens)
                    $('.category-sortable[rel="' + categoryId + '"]').remove();

                    updateProductCount();
                },
                error: function (response) {
                    console.log(response);
                }
            });
        });
    });

    buildSortable();
    bindForms('#design');
});
