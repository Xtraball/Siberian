/*global
    isInit, folder, options, valueId, rootId, placeholderCategory,
    bindForms, swal, words, carouselSearch, carousel, featureSearch,
    maxNestedLevel
 */
$(document).ready(function () {
    // Build refresh sortable!
    var lockedSingleRoot = false;

    function buildSortable() {
        $('#nested-root').nestedSortable({
            handle: 'i.folder-sortable-handle',
            errorClass: 'folder-nested-error',
            listType: 'ul',
            items: 'li',
            tabSize: 26,
            maxLevels: maxNestedLevel,
            placeholder: 'placeholder',
            toleranceElement: '> span',
            opacity: 0.7,
            isAllowed: function (placeholder, placeholderParent, currentItem) {
                try {
                    // Avoid loops!
                    if (willCreateALoop(placeholderParent, currentItem)) {
                        console.error('Aborting a possible loop!');
                        return false;
                    }
                } catch (e) {
                    return false;
                }

                // Prevent moving elements outside the allowed boundaries!
                return !lockedSingleRoot;
            },
            start: function (event, ui) {
                var rootElements = $('#nested-root > li[rel]').length;
                if (ui.item.parent('#nested-root').length && (rootElements <= 1)) {
                    lockedSingleRoot = true;
                }
            },
            stop: function (event, ui) {
                lockedSingleRoot = false;
            },
            update: function (event, ui) {
                lockedSingleRoot = false;
                updatePositions(ui.item);
                updateFeatureCount();
            }
        });
    }

    function loadRootForm() {
        $('#root-folder .folder-edit').trigger('click');
    }

    var willCreateALoop = function (placeholderParent, currentItem) {
        var loopFailsafe = 0;
        var localParent = placeholderParent;
        while (localParent !== undefined) {
            if (localParent.attr('parentId') === currentItem.attr('rel')) {
                return true;
            }
            localParent = localParent.parent('ul').parent('li');
            loopFailsafe = loopFailsafe + 1;
            if (loopFailsafe > maxNestedLevel) {
                break;
            }
        }

        return false;
    };

    function refreshPages() {
        // Update/Refresh the Add features list!
        $.ajax({
            url: '/folder2/application/edit/option_value_id/' + valueId,
            method: 'POST',
            dataType: 'json',
            success: function (dataEdit) {
                var $tmp = $('<div id="tmpFolder2"></div>');
                $tmp.append(dataEdit.html);
                var content = $tmp.find('#add_page_to_folder').html();
                $('#add_page_to_folder').html(content);
                $tmp.remove();
            },
            error: function (response) {}
        });
    }

    $(document).off('click', '.folder-edit');
    $(document).on('click', '.folder-edit', function () {
        // Clear all active elements!
        $('.folder-sortable').removeClass('active');
        var currentCategory = $(this);
        var categoryId = currentCategory.attr('rel');

        // Active current category!
        $('.folder-sortable[rel="' + categoryId + '"]').addClass('active');

        // Loads(display) carousel slider items!
        folder.current_category_id = categoryId;
        folder.id = valueId;

        isInit = true;
        options.loadNewSlider(categoryId);

        // Then load new form/features available to insert!
        var container = $('.folder-form-container');
        $.ajax({
            url: '/folder2/application/loadform/value_id/' + valueId,
            method: 'GET',
            data: {
                actionName: 'edit',
                categoryId: categoryId,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                // Append form!
                container
                    .html('')
                    .append(data.form);

                $('.folder-form-container').removeData('binded');
                bindForms('.folder-form-container');
            },
            error: function (response) {}
        });
    });

    // Always append to root folder!
    $(document).off('click', '.create-folder');
    $(document).on('click', '.create-folder', function () {
        var container = $('.folder-form-container');

        $.ajax({
            url: '/folder2/application/loadform/value_id/' + valueId,
            method: 'POST',
            data: {
                actionName: 'create',
                parentId: rootId,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                // Append form!
                container
                    .html('')
                    .append(data.form);

                // Append subfolder!
                $('.folder-sortable[rel="' + rootId + '"] ~ ul')
                    .append(data.placeholder);

                // Allows to rebind the form!
                $('.folder-form-container').removeData('binded');
                bindForms('.folder-form-container');
                updateFeatureCount();
                $('li.folder-sortable[rel="' + data.categoryId + '"] .folder-edit')
                    .trigger('click');
            },
            error: function (response) {}
        });
    });

    // Always append to root folder!
    $(document).off('click', '.folder-delete');
    $(document).on('click', '.folder-delete', function () {
        var deleteButton = $(this);
        var categoryId = deleteButton.attr('rel');
        var folderName = $('.folder-sortable[rel="' + categoryId + '"] > span > span.folder-title').text();
        swal({
            html: true,
            title: words.deleteTitle,
            text: words.deleteText.replace('#FOLDER_NAME#', '<b>' + folderName + '</b>'),
            showCancelButton: true,
            confirmButtonColor: '#ff3a2e',
            confirmButtonText: words.confirmDelete,
            cancelButtonText: words.cancelDelete,
            buttons: true
        }, function () {
            $.ajax({
                url: '/folder2/application/delete/value_id/' + valueId,
                method: 'POST',
                data: {
                    actionName: 'delete',
                    categoryId: categoryId,
                    valueId: valueId
                },
                dataType: 'json',
                success: function (data) {
                    // Remove DOM element (and it's childrens)
                    $('.folder-sortable[rel="' + categoryId + '"]').remove();

                    updateFeatureCount();
                    loadRootForm();
                    refreshPages();
                },
                error: function (response) {}
            });
        });
    });

    // Listen form change
    $(document).off('keyup', '#form-folder-category input[name="title"]');
    $(document).on('keyup', '#form-folder-category input[name="title"]', function () {
        var title = $(this);
        $('li.folder-sortable[rel="' + title.attr('rel') + '"] > span > span.folder-title')
            .text(title.val());
    });

    // Add feature
    $(document).off('click', '.add_feature_folder2');
    $(document).on('click', '.add_feature_folder2', function () {
        var feature = $(this);
        var featureId = feature.attr('rel');
        var parentId = folder.current_category_id;

        $.ajax({
            url: '/folder2/application/addfeature/value_id/' + valueId,
            method: 'POST',
            data: {
                actionName: 'addfeature',
                featureId: featureId,
                parentId: parentId,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                // Find the item in list, then move it to the end to ensure it's position always ok!
                var carouselItem = $('li#option_value_' + featureId);
                carouselItem.attr('rel', parentId);
                carouselItem.attr('data-folder-id', valueId);
                carouselItem.attr('data-pos', data.position);
                $('#option_values').append(carouselItem);

                // Remove the element from the available options!
                $('li#add_page_' + featureId).remove();

                // Refresh the carousel to sort things out!
                carouselSearch.clearSearch(function () {
                    setTimeout(function () {
                        carousel.update().slideToItem(carouselItem);
                    }, 1000);
                });
                featureSearch.clearSearch();
                updateFeatureCount();
            },
            error: function (response) {}
        });
    });

    var getPositions = function (element) {
        var jElement = $(element);
        var rel = jElement.attr('rel');
        var firstParent = jElement.parent('ul');
        var mappedPositions = $.map($.makeArray(firstParent.find('> li.folder-sortable')), function (val) {
            return $(val).attr('rel');
        });

        var parentId;
        try {
            parentId = $('.folder-sortable[rel="' + rel + '"]').parents('li[rel]')[0].attributes.rel.value;
        } catch (e) {
            parentId = rootId;
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
            url: '/folder2/application/updatepositions/value_id/' + valueId,
            method: 'POST',
            data: {
                actionName: 'update',
                positions: positions,
                valueId: valueId
            },
            dataType: 'json',
            success: function (data) {
                updateFeatureCount();
            },
            error: function (response) {}
        });
    };

    function updateFeatureCount() {
        var categoryIds = $.map($.makeArray($('.folder-sortable[rel]')), function (val, i) {
            return $(val).attr('rel');
        });

        categoryIds.forEach(function (element) {
            var count = $('#option_values li[rel="' + element + '"]').length;
            $('.folder-sortable[rel="' + element + '"] span.folder-feature-count').text('(' + count + ')');
        });

        var l, depth = 0;
        $('#nested-root li:not(:has(ul)):not(:has(ul))').each(function() {
            l = $(this).parents('ul').length;
            if (l > depth) {
                depth = l;
            }
        });
        if (depth > 6) {
            $('ul.nested-folders').addClass('deep');
        } else {
            $('ul.nested-folders').removeClass('deep');
        }
    }

    // Take onload actions!
    updateFeatureCount();
    buildSortable();
    bindForms('#settings');
    bindForms('#design');

    var latestCategory = $('li.folder-sortable[rel="' + folder.current_category_id + '"] > span > i.folder-edit');
    if (latestCategory.length > 0) {
        latestCategory.trigger('click');
    } else {
        loadRootForm();
    }
});
