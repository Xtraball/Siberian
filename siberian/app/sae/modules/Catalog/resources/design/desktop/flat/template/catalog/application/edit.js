/*global
    isInit, folder, options, valueId, placeholderCategory,
    bindForms, swal, words, carouselSearch, carousel, featureSearch,
    maxNestedLevel, formatTemplate, titlePlaceholder, pricePlaceholder
 */
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
                // Category check for nested!
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
            case 'product':
                result = {
                    'product': {
                        productId: rel,
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
            url: '/catalog/application/updatepositions/value_id/' + valueId,
            method: 'POST',
            data: {
                typeName: typeName,
                positions: positions,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                updateProductCount();
            },
            error: function (response) {}
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

    // Always append to root category!
    $(document).off('click', '.create-category');
    $(document).on('click', '.create-category', function () {
        $.ajax({
            url: '/catalog/application/createcategory/value_id/' + valueId,
            method: 'POST',
            data: {
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                // Append subfolder!
                $('.category-sortable[rel="root"] ~ ul')
                    .append(data.placeholder);

                // Allows to rebind the form!
                updateProductCount();

                document.querySelector('ul.nested-categories').scrollTop = 2000000000;
            },
            error: function (response) {}
        });
    });

    // Delete category and childrens category!
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
            case 'product':
                    title = words.deleteProductTitle;
                    categoryName = $('.category-sortable[typeName="product"][rel="' + categoryId + '"] > span > span.category-title').text();
                    text = words.deleteProductText.replace('#PRODUCT_NAME#', '<b>' + categoryName + '</b>');
                    data = {
                        actionName: 'delete',
                        typeName: 'product',
                        productId: categoryId,
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
                url: '/catalog/application/deletecategory/value_id/' + valueId,
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function (data) {
                    // Remove DOM element (and it's childrens)
                    $('.category-sortable[typeName="' + typeName + '"][rel="' + categoryId + '"]').remove();

                    updateProductCount();
                },
                error: function (response) {}
            });
        });
    });

    var toggleNestedFormState = false;
    var toggleNestedForm = function () {
        toggleNestedFormState = !toggleNestedFormState;

        var nested = $('.category-container');
        var productForm = $('.product-container');
        if (toggleNestedFormState) {
            nested.hide();
            productForm.slideDown();
        } else {
            productForm.hide();
            nested.slideDown();
        }
    };

    // Loader template!
    var loaderTemplate = '<div class="feature-loader"><img src="/app/sae/design/desktop/flat/images/customization/ajax/ajax-loader-black.gif"></div>';

    $(document).off('click', '#product-form-container #sbback');
    $(document).on('click', '#product-form-container #sbback', function () {
        toggleNestedForm();

        // Clears the form!
        var formContainer = $('#product-form-container');
        formContainer.html('');
        formContainer.removeData('binded');
    });

    // Create a product!
    $(document).off('click', '.category-add-product');
    $(document).on('click', '.category-add-product', function () {
        toggleNestedForm();
        var formContainer = $('#product-form-container');
        formContainer.html('').append(loaderTemplate);

        var currentButton = $(this);
        var parentId = currentButton.attr('rel');

        $.ajax({
            url: '/catalog/application/loadproductform/value_id/' + valueId,
            method: 'POST',
            data: {
                parentId: parentId,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                // Append form!
                formContainer
                    .html('')
                    .append(data.form);

                // Allows to rebind the form!
                formContainer.removeData('binded');
                formContainer.find('form').data('callback', function (callbackData) {
                    if (callbackData.success !== undefined && callbackData.success) {
                        toggleNestedForm();
                        var categoryId = callbackData.categoryId;
                        if ($('li.category-sortable[typeName="category"][rel="' + categoryId + '"] > ul').length === 0) {
                            $('li.category-sortable[typeName="category"][rel="' + categoryId + '"]')
                                .append('<ul/>');
                        }
                        $('li.category-sortable[typeName="category"][rel="' + categoryId + '"] > ul')
                            .append(callbackData.productLine);

                        var responseProductId = callbackData.productId;
                        $('li.category-sortable[typeName="product"][rel="' + responseProductId + '"]')
                            .effect('highlight', {
                                color: '#0099C7',
                                opacity: 0.5
                            }, 3000);


                        var myElement = document.querySelector('li.category-sortable[typeName="product"][rel="' + responseProductId + '"]');
                        document.querySelector('ul.nested-categories').scrollTop = myElement.offsetTop;
                    }
                    // Otherwise do nothing, form already displayed bad data!
                });
                bindForms('#product-form-container');
                setTimeout(function () {
                    var addFormat = $('button.product-add-format');
                    addFormat.trigger('click');
                    addFormat.trigger('click');
                    addFormat.trigger('click');
                }, 100);
            },
            error: function (response) {}
        });
    });

    // Edit a product!
    $(document).off('click', '.category-edit-product');
    $(document).on('click', '.category-edit-product', function () {
        toggleNestedForm();
        var formContainer = $('#product-form-container');
        formContainer.html('').append(loaderTemplate);

        var currentButton = $(this);
        var productId = currentButton.attr('rel');

        $.ajax({
            url: '/catalog/application/loadproductform/value_id/' + valueId,
            method: 'POST',
            data: {
                productId: productId,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                // Append form!
                formContainer
                    .html('')
                    .append(data.form);

                // Allows to rebind the form!
                formContainer.removeData('binded');
                formContainer.find('form').data('callback', function (callbackData) {
                    // Only required to update the Title, other values are OK!
                    if (callbackData.success !== undefined && callbackData.success) {
                        toggleNestedForm();
                        var responseProductId = callbackData.productId;
                        $('li.category-sortable[typeName="product"][rel="' + responseProductId + '"] span.category-title')
                            .text(callbackData.product.name);

                        $('li.category-sortable[typeName="product"][rel="' + responseProductId + '"]')
                            .effect('highlight', {
                                color: '#0099C7',
                                opacity: 0.5
                            }, 3000);

                        var myElement = document.querySelector('li.category-sortable[typeName="product"][rel="' + responseProductId + '"]');
                        document.querySelector('ul.nested-categories').scrollTop = myElement.offsetTop;
                    }
                });
                bindForms('#product-form-container');
                data.formats.forEach(function (format) {
                    var tmpFormat = formatTemplate
                        .replace(/%UUID%/ig, guid())
                        .replace(/%TITLE_PL%/ig, titlePlaceholder)
                        .replace(/%TITLE_VALUE%/ig, format.title)
                        .replace(/%PRICE_PL%/ig, pricePlaceholder)
                        .replace(/%PRICE_VALUE%/ig, format.price);
                    $('.product-format-container').append(tmpFormat);
                });
                if (data.formats.length >= 1) {
                    $('#enable_format').trigger('click');
                }
            },
            error: function (response) {}
        });
    });

    buildSortable();
    bindForms('#design');
});
