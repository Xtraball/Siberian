/**
 * Minimalist jQuery carousel
 *
 * @author Xtraball <dev@xtraball.com>
 * @version 1.0.0
 *
 */
(function ($) {
    $.fn.sbcarousel = function (options) {
        var settings = $.extend({}, $.fn.sbcarousel.defaults, options);

        return this.each(function () {
            var $this = $(this),
                pagerItems = [],
                containerItems = null,
                sliderItems = null,
                itemWidth = 0,
                pageSize = 0,
                sliderSize = 0,
                currentPage = 1,
                visibleItems = 1,
                prevArrow = null,
                nextArrow = null;


            function update() {
                sliderItems.hide();

                containerItems.css({
                    'width': '100%',
                    'max-width': '100%'
                });

                containerItems.css({
                    'width': containerItems.width() + 'px'
                });

                pageSize = containerItems.width();
                itemWidth = pageSize / settings.visible_items;

                visibleItems = settings.visible_items;
                if (itemWidth < settings.min_size) {
                    itemWidth = pageSize / settings.visible_items_small;
                    visibleItems = settings.visible_items_small;
                }

                // Items!
                pagerItems.css({
                    'width': itemWidth + 'px'
                });
                sliderItems.show();

                // Slidebox!
                sliderSize = itemWidth * pagerItems.length;
                sliderItems.css({
                    'width': sliderSize + 'px'
                });
            }

            function gotoPage(page) {
                prevArrow.css('opacity', (page <= 1) ? 0.2 : 1);
                nextArrow.css('opacity', (page * visibleItems >= pagerItems.length) ? 0.2 : 1);

                if ((page < 1) || (page > Math.ceil(pagerItems.length / visibleItems))) {
                    return;
                }

                currentPage = page;
                var distance = (page-1) * pageSize;
                sliderItems.css('transform', 'translate(-' + distance + 'px)');
            }

            function gotoElement(selector) {
                var el = pagerItems.filter(selector);
                var index = $(el).attr('data-index') * 1;
                index = index + 1;
                var page = Math.ceil(index / settings.visible_items);

                gotoPage(page);
            }

            function gotoPrevious() {
                gotoPage(currentPage-1);
            }

            function gotoNext() {
                gotoPage(currentPage+1);
            }

            function buildPager() {
                prevArrow.on('click', function () {
                    gotoPrevious();
                });
                nextArrow.on('click', function () {
                    gotoNext();
                });
            }

            function initPager() {
                containerItems = $this.find(settings.container_selector);
                sliderItems = $this.find(settings.slider_selector);
                pagerItems = $this.find(settings.item_selector);
                prevArrow = $this.find(settings.prev_selector);
                nextArrow = $this.find(settings.next_selector);

                if (settings.visible_items_small === -1) {
                    settings.visible_items_small = Math.ceil(settings.visible_items / 2);
                }

                $this.css({
                    'width': '100%'
                });

                // Main container!
                pagerItems.hide();
                containerItems.css({
                    'width': '100%',
                    'max-width': '100%'
                });

                containerItems.css({
                    'display': 'block',
                    'width': containerItems.width() + 'px',
                    'overflow': 'hidden'
                });

                pageSize = containerItems.width();
                itemWidth = pageSize / settings.visible_items;
                visibleItems = settings.visible_items;
                if (itemWidth < settings.min_size) {
                    itemWidth = pageSize / settings.visible_items_small;
                    visibleItems = settings.visible_items_small;
                }

                // Items!
                pagerItems.css({
                    'width': itemWidth + 'px',
                    'float': 'left'
                });
                pagerItems.show();
                $this.find(settings.item_selector+' img').css({
                    'width': '100%',
                    'padding': '0 2px'
                });

                pagerItems.toArray().forEach(function (el, index) {
                    $(el).attr('data-index', index);
                });

                // Slidebox!
                sliderSize = itemWidth * pagerItems.length;
                sliderItems.css({
                    'width': sliderSize + 'px',
                    'transition-duration': '1s'
                });

                if (pagerItems.length < visibleItems - 1) {
                    prevArrow.css('opacity', 0.2);
                    nextArrow.css('opacity', 0.2);

                    return;
                }

                buildPager();
                gotoPage(1);

                this.gotoPage = gotoPage;
                this.gotoElement = gotoElement;
                this.gotoNext = gotoNext;
                this.gotoPrevious = gotoPrevious;
                this.update = update;

                $this.data('sbcarousel', this);
            }

            initPager();

            $(window).resize(function () {
                setTimeout(function () {
                    update();
                    gotoPage(currentPage);
                }, 200);
            });
        });
    };

    $.fn.sbcarousel.defaults = {
        visible_items: 3,
        visible_items_small: -1,
        min_size: -1,
        prev_selector: '.prev-arrow',
        next_selector: '.next-arrow',
        container_selector: '.sb-carousel-items',
        slider_selector: '.sb-carousel-inline',
        item_selector: '.sb-carousel-item'
    };
})(jQuery);
