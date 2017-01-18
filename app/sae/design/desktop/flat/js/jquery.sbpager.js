/**
 * Minimalist pager
 */
(function($){

    $.fn.sbpager = function(options) {
        var settings = $.extend({}, $.fn.sbpager.defaults, options);

        return this.each(function() {
            var table = $(this),
                pager = null,
                search = null,
                searchInput = null,
                searchEmpty = null,
                searchClear = null,
                pagerItems = [],
                pagerItemsOriginal = [],
                pagerInitialized = false,
                noResult = null,
                currentPage = 1;


            function initPager() {
                pagerItemsOriginal = pagerItems = table.find(settings.row_selector).toArray();
                noResult = table.find("tfoot");

                if(settings.with_search) {
                    initSearch();
                }

                if(visibleItemsLength() <= settings.items_per_page) {
                    return;
                }

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

            function gotoPage(page) {

                noResult.hide();
                pager.show();
                if(visibleItemsLength() == 0) {
                    pagerItems.forEach(function (item) {
                        $(item).hide();
                    });
                    noResult.show();
                    pager.hide();
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
            "</div>"
    };

})(jQuery);