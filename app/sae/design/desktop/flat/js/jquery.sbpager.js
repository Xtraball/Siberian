/**
 * Minimalist pager
 */
(function($){

    $.fn.sbpager = function(options) {
        var settings = $.extend({}, $.fn.sbpager.defaults, options);

        return this.each(function() {
            var table = $(this),
                pager = null,
                pagerItems = new Array(),
                currentPage = 1;

            function initPager() {
                pagerItems = table.find(settings.row_selector);

                if(pagerItems.length < settings.items_per_page-1) {
                    return;
                }

                buildPager();
                gotoPage(1);

                table.data("sbpager", this);
            };

            function gotoPage(page) {

                if((page < 1) || (page * settings.items_per_page > pagerItems.length)) {
                    return;
                }

                var start = (page - 1) * settings.items_per_page;
                var end = start + settings.items_per_page;

                if(typeof settings.callback_goto_page == "function") {
                    settings.callback_goto_page();
                }

                pager.find(".sb-pager-current").text(page);

                currentPage = page;
                pagerItems.hide();
                pagerItems.slice(start, end).show();
            };

            function gotoFirst() {
                gotoPage(1);
            };

            function gotoPrevious() {
                gotoPage(currentPage-1);
            };

            function gotoNext() {
                gotoPage(currentPage+1);
            };

            function gotoLast() {
                gotoPage(Math.floor(pagerItems.length / settings.items_per_page));
            };

            function buildPager() {
                pager = $(settings.pagerTemplate);
                pager.find(".sb-pager-total").text(Math.floor(pagerItems.length / settings.items_per_page));
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
            };

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
            "</div>"
    };

})(jQuery);