/**
 * Minimalist pager
 */
(function($){

    $.fn.sbpager = function(options) {
        var settings = $.extend({}, $.fn.sbpager.defaults, options);

        return this.each(function() {
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
                noResult = null,
                sortOrders = [],
                currentSortOrder = 0,
                currentSortIndex = 0,
                sortableInitialized = false,
                sortableHeaders = null,
                currentPage = 1;


            function initPager() {
                pagerItemsOriginal = pagerItems = table.find(settings.row_selector).toArray();
                noResult = table.find("tfoot");
                tbody = table.find("tbody");

                if(settings.with_search) {
                    initSearch();
                }

                buildSortableHeaders();
                buildPager();
                gotoPage(1);

                table.data("sbpager", this);
            }

            function initSearch() {
                search = $(settings.searchTemplate);
                searchInput = search.find("input");
                searchInput.attr("placeholder", settings.search_placeholder);
                searchInput.on("keyup", function() {
                    doSearch($(this).val().trim().toLowerCase());
                });
                searchEmpty = search.find("i.empty");
                searchClear = search.find("i.clear");
                searchClear.on("click", function() {
                    searchInput.val("");
                    doSearch("");
                });
                table.before(search);
            }

            function doSort(element, index) {
                sortableHeaders.removeClass("up");
                sortableHeaders.removeClass("down");
                switch(sortOrders[index]) {
                    case 0:
                        sortOrders[index] = 1;
                        $(element).addClass("up");
                        break;
                    case 1:
                        sortOrders[index] = -1;
                        $(element).addClass("down");
                        break;
                    case -1:
                        sortOrders[index] = 0;
                        break;
                }

                currentSortOrder = sortOrders[index];
                currentSortIndex = index;

                var tmpRows = tbody.find("tr:not(.edit-form)").get();
                tmpRows.sort(sortElements);

                $.each(tmpRows, function(index, row) {
                    tbody.append(row);
                });

                /** Refresh pager items */
                pagerItems = table.find(settings.row_selector).toArray();

                gotoPage(1);
            }

            function doSearch(text) {
                // Restore
                if(text.length == 0) {
                    searchEmpty.addClass("active");
                    searchClear.removeClass("active");
                } else {
                    searchEmpty.removeClass("active");
                    searchClear.addClass("active");
                }

                pagerItems.forEach(function (item) {
                    var jitem = $(item);
                    var textValue = jitem.text().trim().replace(/(\n|\s)+/g, " ").toLowerCase();
                    var push = true;
                    text.split(" ").forEach(function(part) {
                        if(textValue.indexOf(part) == -1) {
                            push = false;
                        }
                    });

                    // Use data set
                    if(!push) {
                        jitem.data("filtered-off", true);
                    } else {
                        jitem.removeData("filtered-off");
                    }
                });

                buildPager();
                gotoPage(1);
            }

            function sortElements(element_a, element_b) {
                value_a = getElementValue(element_a);
                value_b = getElementValue(element_b);

                /** Reset to default order */
                if(currentSortOrder == 0) {
                    return (value_a < value_b) ? -1 : 1;
                }

                /** Numeric sort */
                if(table.find("thead th:eq("+currentSortIndex+")").hasClass("numeric")) {
                    return (currentSortOrder == 1) ? (value_a - value_b) : (value_b - value_a);
                }

                /** Default alpha */
                if(value_a < value_b) {
                    return -1 * currentSortOrder;
                }
                if(value_a > value_b) {
                    return 1 * currentSortOrder;
                }
                return 0;
            }

            function getElementValue(element) {
                if(currentSortOrder == 0) {
                    return $(element).data("original-index");
                }

                return $(element).find("td:eq("+currentSortIndex+")").text();
            }

            function gotoPage(page) {
                if(!pagerInitialized) {
                    return;
                }

                noResult.hide();
                showPager();
                if(visibleItemsLength() == 0) {
                    pagerItems.forEach(function (item) {
                        $(item).hide();
                    });
                    noResult.show();
                    hidePager();
                }

                var max = Math.ceil((visibleItemsLength()/settings.items_per_page)) * settings.items_per_page;
                if((page < 1) || (page * settings.items_per_page > max)) {
                    return;
                }

                var start = (page - 1) * settings.items_per_page;
                var end = start + settings.items_per_page;

                if(typeof settings.callback_goto_page == "function") {
                    settings.callback_goto_page();
                }

                pager.find(".sb-pager-current").text(page);

                currentPage = page;

                var count = 0;
                var visibleIndex = 0;
                pagerItems.forEach(function(item) {
                    $(item).hide();
                    if((typeof $(item).data("filtered-off") == "undefined") && (count < settings.items_per_page)) {
                        if(visibleIndex >= start && visibleIndex < end) {
                            $(item).show();
                            count++;
                        }

                        visibleIndex++;
                    }
                });

                if(visibleItemsLength() <= settings.items_per_page) {
                    hidePager();
                }
            }

            function visibleItemsLength() {
                var count = 0;
                pagerItems.forEach(function(item) {
                    if(typeof $(item).data("filtered-off") == "undefined") {
                        count++;
                    }
                });

                return count;
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
                if(pagerInitialized) {
                    pager.show();
                }
            }

            function hidePager() {
                if(pagerInitialized) {
                    pager.hide();
                }
            }

            function buildPager() {
                if(!pagerInitialized) {
                    pager = $(settings.pagerTemplate);

                    pager.find(".sb-pager-first").on("click", function() {
                        gotoFirst();
                    });
                    pager.find(".sb-pager-previous").on("click", function() {
                        gotoPrevious();
                    });
                    pager.find(".sb-pager-next").on("click", function() {
                        gotoNext();
                    });
                    pager.find(".sb-pager-last").on("click", function() {
                        gotoLast();
                    });
                    table.after(pager);
                    pagerInitialized = true;
                }

                pager.find(".sb-pager-total").text(Math.ceil(visibleItemsLength() / settings.items_per_page));
            }

            function buildSortableHeaders() {
                if(!sortableInitialized) {
                    sortableHeaders = table.find("thead th");
                    $.each(sortableHeaders, function(index, element) {
                        sortOrders[index] = 0;
                        /** Null == from header class */
                        if(settings.sort_headers == null) {
                            if($(element).hasClass("sortable")) {
                                $(element).on("click", function() {
                                    doSort(element, index);
                                });
                            }
                        } else if(typeof settings.sort_headers == "object") {
                            if(settings.sort_headers.indexOf(index) != -1) {
                                $(element).addClass("sortable");
                                $(element).on("click", function() {
                                    doSort(element, index);
                                });
                            }
                        }
                    });

                    /** Original order */
                    var tmpRows = tbody.find("tr:not(.edit-form)").get();
                    $.each(tmpRows, function(index, row) {
                        $(row).data("original-index", index);
                    });

                    sortableInitialized = true;
                }
            }

            initPager();
        });
    };

    $.fn.sbpager.defaults = {
        items_per_page: 15,
        row_selector: "tr.sb-pager",
        callback_goto_page: null,
        pagerTemplate:
            "<div class=\"sb-pagination\">" +
            "   <div class=\"sb-pager-first btn default_button color-blue\"><i class=\"fa fa-angle-double-left icon icon-double-angle-left\"></i></div>" +
            "   <div class=\"sb-pager-previous btn default_button color-blue\"><i class=\"fa fa-angle-left  icon icon-angle-left\"></i></div>" +
            "   <div class=\"sb-pager-pages btn default_button\"><span class=\"sb-pager-current\"></span>/<span class=\"sb-pager-total\"></span></div>" +
            "   <div class=\"sb-pager-next btn default_button color-blue\"><i class=\"fa fa-angle-right  icon icon-angle-right\"></i></div>" +
            "   <div class=\"sb-pager-last btn default_button color-blue\"><i class=\"fa fa-angle-double-right  icon icon-double-angle-right\"></i></div>" +
            "</div>",
        with_search: false,
        search_placeholder: "Search",
        searchTemplate:
            "<div class=\"sb-search\">" +
            "   <div class=\"sb-search-content\">" +
            "       <input type=\"text\" class=\"input-flat\" placeholder=\"\" />" +
            "       <i class=\"fa fa-search empty active\"></i>" +
            "       <i class=\"fa fa-times clear\"></i>" +
            "   </div>" +
            "</div>",
        sort_headers: null
    };

})(jQuery);