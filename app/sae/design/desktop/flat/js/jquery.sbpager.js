/**
 * Minimalist pager
 */
(function ($) {
    $.fn.sbpager = function (options) {
        var settings = $.extend({}, $.fn.sbpager.defaults, options);

        return this.each(function () {
            var table = $(this),
                tbody = null,
                pager = null,
                search = null,
                searchInput = null,
                searchEmpty = null,
                searchClear = null,
                pagerItems = [],
                pagerItemsOriginal = [],
                pagerInitialized = false,
                map = [],
                noResult = null,
                sortOrders = [],
                currentSortOrder = 0,
                currentSortIndex = 0,
                sortableInitialized = false,
                sortableHeaders = null,
                currentPage = 1;


            function initPager() {
                pagerItemsOriginal = pagerItems = table.find(settings.row_selector+':not(.edit-form)').toArray();
                noResult = table.find('tfoot');
                tbody = table.find('tbody');

                if (settings.with_search) {
                    initSearch();
                }

                buildSortableHeaders();
                buildPager();
                gotoPage(1);

                table.data('sbpager', this);
            }

            function initSearch() {
                search = $(settings.searchTemplate);
                searchInput = search.find('input');
                searchInput.attr('placeholder', settings.search_placeholder);
                searchInput.on('keyup', function () {
                    doSearch($(this).val().trim().toLowerCase());
                });
                searchEmpty = search.find('i.empty');
                searchClear = search.find('i.clear');
                searchClear.on('click', function () {
                    searchInput.val('');
                    doSearch('');
                });
                table.before(search);
            }

            function doSort(element, index) {
                sortableHeaders.removeClass('up');
                sortableHeaders.removeClass('down');
                switch (sortOrders[index]) {
                    case 0:
                        sortOrders[index] = 1;
                        $(element).addClass('up');
                        break;
                    case 1:
                        sortOrders[index] = -1;
                        $(element).addClass('down');
                        break;
                    case -1:
                        sortOrders[index] = 0;
                        break;
                }

                currentSortOrder = sortOrders[index];
                currentSortIndex = index;
                var sortType = 'text';
                if ($(element).hasClass('numeric')) {
                    sortType = 'numeric';
                } else if ($(element).hasClass('date')) {
                    sortType = 'date';
                }

                if (currentSortOrder === 0) {
                    sortType = 'original';
                }

                // Filter the map first
                var mapFiltered = map.filter(function (mapElement) {
                    return (typeof mapElement.el.attr('data-filtered-off') === 'undefined');
                });
                switch (sortType) {
                    case 'text':
                            mapFiltered.sort(textSort);
                        break;
                    case 'numeric':
                            mapFiltered.sort(numericSort);
                        break;
                    case 'date':
                            mapFiltered.sort(dateSort);
                        break;
                    case 'original':
                            mapFiltered.sort(originalSort);
                        break;
                }

                for (var i = 0; i < mapFiltered.length; i = i + 1) {
                    tbody.append(pagerItemsOriginal[mapFiltered[i].index]);
                }

                gotoPage(1);
            }

            function doSearch(text) {
                // Restore!
                if (text.length === 0) {
                    searchEmpty.addClass('active');
                    searchClear.removeClass('active');
                } else {
                    searchEmpty.removeClass('active');
                    searchClear.addClass('active');
                }

                // split only once!
                var text_parts = text.split(' ');

                for (var s = 0; s < map.length; s = s + 1) {
                    var textValue = map[s].textValue;
                    var push = true;
                    text_parts.forEach(function (part) {
                        if (textValue.indexOf(part) === -1) {
                            push = false;
                        }
                    });

                    // Use data set
                    if (push) {
                        map[s].el.removeAttr('data-filtered-off');
                    } else {
                        map[s].el.attr('data-filtered-off', true);
                    }
                }

                buildPager();
                gotoPage(1);
            }

            function originalSort(element_a, element_b) {
                return (element_a.index < element_b.index) ? -1 : 1;
            }

            function numericSort(element_a, element_b) {
                return (currentSortOrder === 1) ?
                    (element_a.textValues[currentSortIndex] * 1 - element_b.textValues[currentSortIndex] * 1) :
                    (element_b.textValues[currentSortIndex] * 1 - element_a.textValues[currentSortIndex] * 1);
            }

            function dateSort(element_a, element_b) {
                return (currentSortOrder === 1) ?
                    (element_a.dateValues[currentSortIndex] * 1 - element_b.dateValues[currentSortIndex] * 1) :
                    (element_b.dateValues[currentSortIndex] * 1 - element_a.dateValues[currentSortIndex] * 1);
            }

            function textSort(element_a, element_b) {
                var value_a = element_a.textValues[currentSortIndex];
                var value_b = element_b.textValues[currentSortIndex];

                if (value_a < value_b) {
                    return -1 * currentSortOrder;
                }
                if (value_a > value_b) {
                    return currentSortOrder;
                }
                return 0;
            }

            function gotoPage(page) {
                if (!pagerInitialized) {
                    return;
                }

                noResult.hide();
                showPager();
                if (visibleItemsLength() === 0) {
                    table.find(settings.row_selector).hide();
                    noResult.show();
                    hidePager();
                }

                var max = Math.ceil((visibleItemsLength()/settings.items_per_page)) * settings.items_per_page;
                if ((page < 1) || (page * settings.items_per_page > max)) {
                    return;
                }

                var start = (page - 1) * settings.items_per_page;
                var end = start + settings.items_per_page;

                if (typeof settings.callback_goto_page === 'function') {
                    settings.callback_goto_page();
                }

                pager.find('.sb-pager-current').text(page);

                currentPage = page;

                var count = 0;
                var visibleIndex = 0;
                table.find(settings.row_selector).hide();
                table.find(settings.row_selector+':not([data-filtered-off])').slice(start, end).show();

                if (visibleItemsLength() <= settings.items_per_page) {
                    hidePager();
                }
            }

            function visibleItemsLength() {
                return table.find(settings.row_selector+':not([data-filtered-off])').length;
            }

            function gotoFirst() {
                gotoPage(1);
            }

            function gotoPrevious() {
                gotoPage(currentPage-1);
            }

            function gotoNext() {
                gotoPage(currentPage+1);
            }

            function gotoLast() {
                gotoPage(Math.ceil(visibleItemsLength() / settings.items_per_page));
            }

            function showPager() {
                if (pagerInitialized) {
                    pager.show();
                }
            }

            function hidePager() {
                if (pagerInitialized) {
                    pager.hide();
                }
            }

            function buildPager() {
                if (!pagerInitialized) {
                    pager = $(settings.pagerTemplate);

                    pager.find('.sb-pager-first').on('click', function () {
                        gotoFirst();
                    });
                    pager.find('.sb-pager-previous').on('click', function () {
                        gotoPrevious();
                    });
                    pager.find('.sb-pager-next').on('click', function () {
                        gotoNext();
                    });
                    pager.find('.sb-pager-last').on('click', function () {
                        gotoLast();
                    });
                    table.after(pager);
                    pagerInitialized = true;
                }

                pager.find('.sb-pager-total').text(Math.ceil(visibleItemsLength() / settings.items_per_page));
            }

            function buildSortableHeaders() {
                if (!sortableInitialized) {
                    sortableHeaders = table.find('thead th');
                    $.each(sortableHeaders, function (index, element) {
                        sortOrders[index] = 0;
                        /** Null == from header class */
                        if (settings.sort_headers === null) {
                            if ($(element).hasClass('sortable')) {
                                $(element).on('click', function () {
                                    doSort(element, index);
                                });
                            }
                        } else if (typeof settings.sort_headers === 'object') {
                            if (settings.sort_headers.indexOf(index) !== -1) {
                                $(element).addClass('sortable');
                                $(element).on('click', function () {
                                    doSort(element, index);
                                });
                            }
                        }
                    });

                    /** Original order */
                    $.each(pagerItemsOriginal, function (index, row) {
                        createMap(index, row);
                    });

                    sortableInitialized = true;
                }
            }

            function createMap(index, row) {
                var _row = $(row);
                _row.attr('data-original-index', index);

                var _rows = [];
                var _dateRows = [];
                _row.find('td').each(function () {
                    var el = $(this);
                    _rows.push(el.text().trim().replace(/(\n|\s)+/g, ' ').toLowerCase());
                    _dateRows.push(el.attr('data-timestamp'));
                });

                var _textValue = _row.text().trim().replace(/(\n|\s)+/g, ' ').toLowerCase();

                map.push({
                    el: _row,
                    index: index,
                    textValue: _textValue,
                    textValues: _rows,
                    dateValues: _dateRows
                });
            }

            initPager();
        });
    };

    $.fn.sbpager.defaults = {
        items_per_page: 15,
        row_selector: 'tr.sb-pager',
        callback_goto_page: null,
        pagerTemplate:
            '<div class="sb-pagination">' +
            '   <div class="sb-pager-first btn default_button color-blue"><i class="fa fa-angle-double-left icon icon-double-angle-left"></i></div>' +
            '   <div class="sb-pager-previous btn default_button color-blue"><i class="fa fa-angle-left  icon icon-angle-left"></i></div>' +
            '   <div class="sb-pager-pages btn default_button"><span class="sb-pager-current"></span>/<span class="sb-pager-total"></span></div>' +
            '   <div class="sb-pager-next btn default_button color-blue"><i class="fa fa-angle-right  icon icon-angle-right"></i></div>' +
            '   <div class="sb-pager-last btn default_button color-blue"><i class="fa fa-angle-double-right  icon icon-double-angle-right"></i></div>' +
            '</div>',
        with_search: false,
        search_placeholder: 'Search',
        searchTemplate:
            '<div class="sb-search">' +
            '   <div class="sb-search-content">' +
            '       <input type="text" class="input-flat" placeholder="" />' +
            '       <i class="fa fa-search empty active"></i>' +
            '       <i class="fa fa-times clear"></i>' +
            '   </div>' +
            '</div>',
        sort_headers: null
    };
}(jQuery));
